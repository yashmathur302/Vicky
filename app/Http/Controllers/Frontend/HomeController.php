<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Follower;
use App\Models\Post;

class HomeController extends Controller
{
    private const PAGE_SIZE = 5;

    public function index()
    {
        $user = Auth::user();

        if (! $user) {
            return redirect()->route('login');
        }

        // "People you may know"
        // (functionality phase): accepted friends aur blocked users bhi hatana.
        $userId = $user->getAuthIdentifier();

        $suggestions = User::query()
            ->where('user_role', 'user')
            ->where('is_banned', false)
            ->whereKeyNot($userId)
            ->whereNotIn('id', Follower::where('follower_id', $userId)->select('following_id'))

            // ->whereKeyNot($user->getKey())
            // ->whereNotIn('id', Follower::where('follower_id', $user->getKey())->select('following_id'))
            ->latest('id')
            ->limit(5)
            ->get(['id', 'name', 'username']);

        return view('frontend.home', compact('user', 'suggestions'));
    }

    // Infinite scroll endpoint: agle posts ka rendered HTML + next cursor (JSON)
    // Infinite scroll endpoint: fetching REAL posts from Database
    public function feed(Request $request): JsonResponse
    {
        $data = $request->validate([
            'cursor' => ['nullable', 'integer', 'min:0'],
        ]);

        $offset = (int) ($data['cursor'] ?? 0);

        $userId = Auth::id();
        
        // TEMP: Jab friends functionality banegi, tab unki ID yahan fetch hogi. 
        // Abhi ke liye blank array de rahe hain.
        $friendIds = []; 

        $postsQuery = Post::with(['user', 'media'])
            ->where(function($query) use ($userId, $friendIds) {
                // 1. Post ya toh public ho
                $query->where('privacy', 'public')
                      // 2. Ya fir post current user ki khud ki ho (Only me & All)
                      ->orWhere('user_id', $userId)
                      // 3. Ya fir privacy 'friends' ho aur post creator user ka friend ho
                      ->orWhere(function($q) use ($friendIds) {
                          $q->where('privacy', 'friends')
                            ->whereIn('user_id', $friendIds);
                      });
            })
            ->latest('id')
            ->skip($offset)
            ->take(self::PAGE_SIZE)
            ->get();

        // DB se posts fetch kar rahe hain (with relations)
        // $postsQuery = Post::with(['user', 'media'])
        //     ->latest('id')
        //     ->skip($offset)
        //     ->take(self::PAGE_SIZE)
        //     ->get();

        $html = '';
        foreach ($postsQuery as $post) {
            // Post ke media items ko frontend format me map karna
            $formattedMedia = [];
            $formattedDoc = null;

            foreach ($post->media as $media) {
                if ($media->media_type === 'document') {
                    $formattedDoc = [
                        'name' => basename($media->media_url),
                        'ext'  => pathinfo($media->media_url, PATHINFO_EXTENSION),
                        'size' => 'Attachment', // Dynamic size hum baad me file system se fetch kar sakte hain
                        'url'  => asset($media->media_url)
                    ];
                } else {
                    $formattedMedia[] = [
                        'type' => $media->media_type,
                        'url'  => asset($media->media_url)
                    ];
                }
            }

            // DB model ko post-card variables me map karna
            $formattedPost = [
                'id'             => $post->id,
                'author'         => $post->user->name,
                'avatar'         => asset('frontend/images/default-avatar.svg'),
                'time'           => $post->created_at->diffForHumans(), // e.g. "2 hours ago"
                'audience'       => $post->privacy ?? 'public',
                'text'           => $post->caption,
                'feeling'        => $post->feeling ? 'feeling ' . ucfirst($post->feeling) : null,
                'location'       => $post->location_name,
                'media'          => $formattedMedia,
                'document'       => $formattedDoc,
                'likes'          => $post->likes_count ?? 0,
                'liked'          => false, // Future me check karenge if current user liked
                'comments_count' => $post->comments_count ?? 0,
                'shares'         => $post->share_count ?? 0,
                'comments'       => [],
            ];

            $html .= view('frontend.partials.post-card', ['post' => $formattedPost])->render();
        }

        $next = $postsQuery->count() === self::PAGE_SIZE ? $offset + self::PAGE_SIZE : null;

        return response()->json([
            'html'        => $html,
            'next_cursor' => $next,
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