<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Support\SmartLog;

class CategoryController extends Controller
{
    // Category List Page Get Karega (With AJAX Filtering)
    public function getList(Request $request)
    {
        $query = Category::orderBy('created_at', 'desc');
        if ($request->filled('search_name')) {
            $query->where('name', 'like', $request->search_name . '%');
        }
        $categories = $query->paginate(15);

        return view('admin.categories.list', compact('categories'));
    }

    // Nayi Category Save Karega (AJAX via Modal)
    public function postCreate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'   => 'required|string|max:255|unique:categories,name',
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $category = new Category();
            $category->name   = $request->name;
            // $category->slug   = Str::slug($request->name);
            $category->status = $request->status;
            $category->save();

            session()->flash('success', 'Category created successfully.');
            return response()->json(['message' => 'Category created successfully.', 'status' => 'success']);
// 
        } catch (\Throwable $th) {
            // Log::error('Category Creation Error: ' . $th->getMessage());
            SmartLog::throttledError('Category Creation Error: ' . $th->getMessage(), [], 60);
            return response()->json(['message' => 'Failed to create category.'], 500);
        }
    }

    // Existing Category Update Karega (AJAX via Modal)
    public function postUpdate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name'   => "required|string|max:255|unique:categories,name,{$id}",
            'status' => 'required|in:active,inactive',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $category = Category::findOrFail($id);
            $category->name   = $request->name;
            // $category->slug   = Str::slug($request->name);
            $category->status = $request->status;
            $category->save();

            session()->flash('success', 'Category updated successfully.');
            return response()->json(['message' => 'Category updated successfully.', 'status' => 'success']);

        } catch (\Throwable $th) {
            // Log::error('Category Update Error: ' . $th->getMessage());
            SmartLog::throttledError('Category Update Error: ' . $th->getMessage(), [], 60);

            return response()->json(['message' => 'Failed to update category.'], 500);
        }
    }

    public function destroy(Request $request, $id = null)
    {
        DB::beginTransaction();

        try {
            // $category = Category::findOrFail($id);
            $category = Category::withCount('events')->findOrFail($id);

            //Prevent deletion if category is in use
            if ($category->events_count > 0) {
                DB::rollBack();
                return redirect()->back()->with('error', "Cannot delete this category. It is linked to {$category->events_count} event(s). Please reassign or delete those events first.");
            }

            $category->delete();
            DB::commit();
            return redirect()->back()->with('success', 'Category deleted successfully.');

        } catch (\throwable $th) {
            DB::rollBack();
            // Log::error('Category Deletion Failed: ' . $e->getMessage());
            SmartLog::throttledError('Category Deletion Error: ' . $th->getMessage(), [], 60);

            return redirect()->back()->with('error', 'Failed to delete Category. Please try again.');
        }
    }
}