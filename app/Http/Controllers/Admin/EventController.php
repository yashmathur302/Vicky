<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Support\SmartLog;
use App\Models\Category;

class EventController extends Controller
{
    //Time ke hisaab se automatically DB me status update hoga
    private function syncEventStatuses()
    {
        $now = Carbon::now();

        // 1. Scheduled -> Ongoing (Agar start time aagaya hai aur end time bacha hai)
        Event::where('status', 'scheduled')
            ->where('start_at', '<=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('end_at')->orWhere('end_at', '>', $now);
            })
            ->update(['status' => 'ongoing']);

        // 2. Scheduled/Ongoing -> Completed (Agar end time nikal gaya hai)
        Event::whereIn('status', ['scheduled', 'ongoing'])
            ->whereNotNull('end_at')
            ->where('end_at', '<=', $now)
            ->update(['status' => 'completed']);
    }

    // Events List Page Show Karega (With AJAX Filtering)
    public function getList(Request $request)
    {
        $this->syncEventStatuses();

        $query = Event::with(['creator', 'category'])->orderBy('created_at', 'desc');

        // Filters
        if ($request->filled('search_title')) {
            $query->where('title', 'like', $request->search_title . '%');
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        $events = $query->paginate(15);
        $categories = Category::where('status', 'active')->get();

        return view('admin.events.list', compact('events', 'categories'));
    }

    // Update Page (Form) Get Karega
    public function getUpdate(Request $request, $id)
    {

        $this->syncEventStatuses();
        
        $event = Event::findOrFail($id);
        $categories = Category::where('status', 'active')->get(); // Dropdown
        $users = User::where('id', $event->user_id)->get();  // fetching only the creator of event

        //fetching all users
        // $users = User::whereNull('deleted_at')->orderBy('name', 'asc')->get();

        
        return view('admin.events.update', compact('event', 'categories', 'users'));
    }

    // Event Update Database Save Karega
    public function postUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'user_id'       => 'required|exists:users,id',
            'title'         => 'required|string|max:255',
            'category_id'   => 'nullable|exists:categories,id',
            'privacy_level' => 'required|in:public,private,friends',
            'location_name' => 'nullable|string|max:255',
            'start_date'    => 'required|date_format:d/m/Y',
            'start_time'    => 'required|date_format:H:i',
            'end_date'      => 'nullable|date_format:d/m/Y',
            'end_time'      => 'nullable|required_with:end_date|date_format:H:i',
            'description'   => 'nullable|string',
            'cover_image'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'status'        => 'required|in:scheduled,ongoing,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            DB::beginTransaction();

            $event = Event::findOrFail($id);
            $now = Carbon::now();

            $startAtString = $request->start_date . ' ' . $request->start_time;
            $startAt = Carbon::createFromFormat('d/m/Y H:i', $startAtString);

            // ==========================================
            // START TIME VALIDATION LOGIC ADDED HERE
            // ==========================================
            // Check if user is changing a past event's time to another past time
            if ($startAt->isPast() && $startAt->format('Y-m-d H:i') != $event->start_at->format('Y-m-d H:i')) {
                return response()->json(['message' => 'New event start time cannot be set in the past.'], 422);
            }

            $endAt = null;
            if ($request->filled('end_date') && $request->filled('end_time')) {
                $endAtString = $request->end_date . ' ' . $request->end_time;
                $endAt = Carbon::createFromFormat('d/m/Y H:i', $endAtString);

                if ($endAt->lessThanOrEqualTo($startAt)) {
                    return response()->json(['message' => 'Event End Time must be strictly after the Start Time.'], 422);
                }
            }

            // Auto-Correct Status based on times
            $assignedStatus = $request->status;
            if ($assignedStatus !== 'cancelled') {
                if ($endAt !== null && $endAt->isPast()) {
                    $assignedStatus = 'completed'; // Agar end time past me hai, automatically completed
                } elseif ($startAt->isPast() && ($endAt === null || $endAt->isFuture())) {
                    $assignedStatus = 'ongoing'; // Agar start ho chuka hai aur end nahi hua, automatically ongoing
                } elseif ($startAt->isFuture()) {
                    $assignedStatus = 'scheduled'; // Agar start time future me hai, automatically scheduled
                }
            }

            // Update Database
            $event->user_id       = $request->user_id;
            $event->title         = $request->title;
            $event->category_id   = $request->category_id;
            $event->privacy_level = $request->privacy_level;
            $event->status        = $assignedStatus; // Assign auto-corrected status
            $event->location_name = $request->location_name;
            $event->description   = $request->description;
            $event->start_at      = $startAt->format('Y-m-d H:i:s');
            $event->end_at        = $endAt ? $endAt->format('Y-m-d H:i:s') : null;

            // Image Handle (Same as before)
            // if ($request->remove_cover_image == 1) {
            //     $oldFile = public_path("uploads/events/cover/event_cover_" . $event->id . ".webp");
            //     if (file_exists($oldFile)) {
            //         @unlink($oldFile);
            //     }
            // }

            // if ($request->hasFile('cover_image')) {
            //     $coverImgName = 'event_cover_' . $event->id . '.webp';
            //     uploadFile('cover_image', 1200, 400, 'events/cover', null, null, $coverImgName);
            // }

            // Image Handle (Same logic as User Update)
            if ($request->remove_cover_image == 1) {
                $oldFile = public_path("uploads/events/cover/event_cover_" . $event->id . ".webp");
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }
            }

            if ($request->hasFile('cover_image')) {
                $coverImgName = 'event_cover_' . $event->id . '.webp';
                
                // Edge Case Fix: Hard delete before overriding
                $oldFile = public_path("uploads/events/cover/" . $coverImgName);
                if (file_exists($oldFile)) {
                    @unlink($oldFile);
                }

                uploadFile('cover_image', 1200, 400, 'events/cover', null, null, $coverImgName);
            }

            $event->cover_image = null; 

            $event->save();
            DB::commit();

            session()->flash('success', 'Event updated successfully.');
            return response()->json([
                'message' => 'Event updated successfully.',
                'redirect' => route('admin.events.list') 
            ]);

        } catch (\Throwable $th) {
            DB::rollBack();
            SmartLog::throttledError('Event Update Error: ' . $th->getMessage(), [], 60);
            return response()->json(['message' => 'Failed to process your request. Please try again later.'], 500);
        }
    }

    public function destroy(Request $request, $id = null)
    {
        DB::beginTransaction();

        try {
            $event = Event::findOrFail($id);
            // if (!empty($event->cover_image) && $event->cover_image !== 'default.jpg') {
            //     $profilePath = public_path("uploads/events/cover/" . $event->cover_image);
            //     if (file_exists($profilePath)) {
            //         unlink($profilePath);
            //     }
            // }
            $event->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Event deleted successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();
            // Log::error('Event Deletion Failed: ' . $e->getMessage());
            SmartLog::throttledError('Event Deletion Failed: ' . $th->getMessage(), [], 60);
            return redirect()->back()->with('error', 'Failed to delete event. Please try again.');
        }
    }
}