<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\PostMedia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Support\SmartLog;

class PostController extends Controller
{
    public function store(Request $request)
    {
        // Validate Payload
        $validator = Validator::make($request->all(), [
            'content'  => 'nullable|string|max:5000|required_without:media',
            'media'    => 'nullable|array|max:10|required_without:content',
            'media.*'  => 'file|mimes:jpeg,png,webp,gif,mp4,webm,pdf|max:51200', // 50MB max for video, others handled below
            // 'media.*'  => 'file|mimes:jpeg,png,webp,gif,mp4,webm,pdf,doc,docx,ppt,pptx,xls,xlsx,txt|max:51200', // 50MB max for video, others handled below
            'privacy'  => 'required|in:public,friends,private',
            'feeling'  => 'nullable|string|max:50',
            'location' => 'nullable|string|max:120',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        try {
            $user = Auth::user();
            
            // 2. Smart Post Type Determination
            $postType = 'text';
            $mediaFiles = $request->file('media') ?? [];
            
            foreach ($mediaFiles as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                if (in_array($ext, ['mp4', 'webm'])) {
                    $postType = 'video';
                    break; // Video takes highest priority in mixed media
                } elseif (in_array($ext, ['pdf'])) {
                // } elseif (in_array($ext, ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'txt'])) {
                    $postType = 'document';
                    break;
                } else {
                    $postType = 'image';
                }
            }

            // 3. Create the Post
            $post = Post::create([
                'user_id'       => $user->id,
                'post_type'     => $postType,
                'caption'       => $request->input('content'),
                'privacy'       => $request->input('privacy'),
                'feeling'       => $request->input('feeling'),
                'location_name' => $request->input('location'),
            ]);

            // 4. Handle Media Uploads
            $formattedMedia = [];
            $formattedDoc = null;

            foreach ($mediaFiles as $file) {
                $ext = strtolower($file->getClientOriginalExtension());
                
                // Extra size validations per file type
                $sizeMB = $file->getSize() / 1048576;
                if (in_array($ext, ['jpeg', 'png', 'webp', 'gif']) && $sizeMB > 5) continue;
                if (in_array($ext, ['pdf']) && $sizeMB > 10) continue;

                // Determine media type for DB
                if (in_array($ext, ['mp4', 'webm'])) $mediaType = 'video';
                elseif (in_array($ext, ['pdf'])) $mediaType = 'document';
                else $mediaType = 'image';

                // Store file
                $path = $file->store('posts/media', 'public');
                $url = Storage::url($path);

                PostMedia::create([
                    'post_id'    => $post->id,
                    'media_url'  => $url,
                    'media_type' => $mediaType,
                ]);

                // Format for frontend response
                if ($mediaType === 'document') {
                    $formattedDoc = [
                        'name' => $file->getClientOriginalName(),
                        'ext'  => $ext,
                        'size' => round($sizeMB, 1) . ' MB'
                    ];
                } else {
                    $formattedMedia[] = ['type' => $mediaType, 'url' => asset($url)];
                }
            }

            // 5. Format Post for Blade Partial (Mapping DB to Dummy Array Structure)
            $formattedPost = [
                'id'             => $post->id,
                'author'         => $user->name,
                'avatar'         => asset('frontend/images/default-avatar.svg'), // Dynamic baad me karenge
                'time'           => 'Just now',
                'audience'       => $post->privacy,
                'text'           => $post->caption,
                'feeling'        => $post->feeling ? 'feeling ' . ucfirst($post->feeling) : null,
                'location'       => $post->location_name,
                'media'          => $formattedMedia,
                'document'       => $formattedDoc,
                'likes'          => 0,
                'liked'          => false,
                'comments_count' => 0,
                'shares'         => 0,
                'comments'       => [],
            ];

            // Render single post HTML
            $html = view('frontend.partials.post-card', ['post' => $formattedPost])->render();

            return response()->json([
                'message' => 'Post created successfully!',
                'html'    => $html
            ]);

        } catch (\Exception $e) {
            SmartLog::throttledError('Post Creation Error: ' . $e->getMessage());
            return response()->json(['message' => 'Something went wrong while posting.'], 500);
        }
    }
}