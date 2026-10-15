<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    private const PAGE_SIZE = 5;

    public function index()
    {
        $user = Auth::user();

        // "People you may know": DB ke users, khud ko chhodkar, naye pehle.
        //  (functionality phase): follow/friend kiye hue, blocked aur banned users bhi hatana.
        $suggestions = User::query()
            ->whereKeyNot($user->getKey())
            ->latest('id')
            ->limit(5)
            ->get(['id', 'name', 'username']);

        return view('frontend.home', compact('user', 'suggestions'));
    }

    // Infinite scroll endpoint: agle posts ka rendered HTML + next cursor (JSON)
    public function feed(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cursor' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $offset = (int) ($data['cursor'] ?? 0);

        // TEMP: sample data. Functionality phase me yahan Post query aayegi
        // (cursorPaginate + with() + withCount() + privacy checks).
        $all   = $this->samplePosts();
        $slice = array_slice($all, $offset, self::PAGE_SIZE);

        $html = '';
        foreach ($slice as $post) {
            $html .= view('frontend.partials.post-card', ['post' => $post])->render();
        }

        $next = $offset + count($slice);

        return response()->json([
            'html'        => $html,
            'next_cursor' => $next < count($all) ? $next : null,
        ]);
    }

    // TEMP SAMPLE DATA (design phase)
    private function samplePosts(): array
    {
        $avatar      = asset('frontend/images/default-avatar.svg');
        $placeholder = asset('frontend/images/placeholder-media.svg');

        $people = ['Chris', 'Robert', 'Martin', 'Bruce', 'Tom', 'Kat', 'Ryan', 'Alen'];
        $times  = ['Just now', '12 min ago', '1 hour ago', '3 hours ago', '6 hours ago', 'Yesterday', '2 days ago'];

        $templates = [
            [
                'text'     => "Uploaded the revision notes for Organic Chemistry.\nPlease go through the reaction mechanisms before Friday's test. Ask here if anything is unclear, and we will go through it together in the study group tonight. Also check the practice sheet, because the last three questions look very much like the exam pattern.",
                'media'    => 0, 'doc' => ['name' => 'Organic-Chemistry-Notes.pdf', 'ext' => 'pdf', 'size' => '2.4 MB'],
                'feeling'  => null, 'location' => null, 'audience' => 'public',
            ],
            [
                'text'     => 'Lab day at the science block. Our team finally got the titration right!',
                'media'    => 4, 'doc' => null,
                'feeling'  => '🥳 Celebratory', 'location' => 'Science Block, Main Campus', 'audience' => 'public',
            ],
            [
                'text'     => 'Does anyone have a simple way to remember the trigonometric identities?',
                'media'    => 0, 'doc' => null,
                'feeling'  => '🤔 Thoughtful', 'location' => null, 'audience' => 'friends',
            ],
            [
                'text'     => 'Hackathon prep has started. Sharing the problem statements and the schedule.',
                'media'    => 2, 'doc' => ['name' => 'Hackathon-Schedule.xlsx', 'ext' => 'xlsx', 'size' => '180 KB'],
                'feeling'  => '😤 Motivated', 'location' => null, 'audience' => 'public',
            ],
            [
                'text'     => 'Library session photos from this morning.',
                'media'    => 1, 'doc' => null,
                'feeling'  => null, 'location' => 'Central Library', 'audience' => 'public',
            ],
            [
                'text'     => 'Notes from today\'s webinar on career paths in data science.',
                'media'    => 6, 'doc' => null,
                'feeling'  => '😊 Happy', 'location' => null, 'audience' => 'private',
            ],
        ];

        $posts = [];
        for ($i = 0; $i < 24; $i++) {
            $t       = $templates[$i % count($templates)];
            $author  = $people[$i % count($people)];
            $media   = [];
            for ($m = 0; $m < $t['media']; $m++) {
                $media[] = ['type' => 'image', 'url' => $placeholder];
            }

            $comments = [];
            if ($i % 3 !== 0) {
                $comments[] = ['author' => $people[($i + 2) % count($people)], 'avatar' => $avatar, 'text' => 'This is really helpful, thank you!', 'time' => '1 hour ago'];
            }
            if ($i % 4 === 1) {
                $comments[] = ['author' => $people[($i + 5) % count($people)], 'avatar' => $avatar, 'text' => 'Can you share more details about this?', 'time' => '30 min ago'];
            }

            $posts[] = [
                'id'             => $i + 1,
                'author'         => $author,
                'avatar'         => $avatar,
                'time'           => $times[$i % count($times)],
                'audience'       => $t['audience'],
                'text'           => $t['text'],
                'feeling'        => $t['feeling'],
                'location'       => $t['location'],
                'media'          => $media,
                'document'       => $t['doc'],
                'likes'          => ($i * 7) % 90 + 3,
                'liked'          => $i % 5 === 0,
                'comments_count' => count($comments),
                'shares'         => $i % 4,
                'comments'       => $comments,
            ];
        }

        return $posts;
    }
}