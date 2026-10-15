<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInfo extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     * (Optional, but good practice since table is plural and model is singular)
     */
    protected $table = 'user_infos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'profile_picture',
        'cover_image',
        'bio',
        'interests',
        'education',
        'work',
        'social_links',
        'website',
        'facebook_link',
        'instagram_link',
        'linkedin_link',
        'twitter_link',
        'is_private',
        'gender',
        'dob',
        'location',
        'friend_count',
        'follower_count',
        'following_count',
        // 'account_status',
        'last_active_at',
        'is_verified',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Casting JSON columns to arrays automatically
            'interests' => 'array',
            'education' => 'array',
            'work'      => 'array',
            
            // Casting booleans
            'is_private'        => 'boolean',
            'is_verified'       => 'boolean',
            'last_active_at'    => 'datetime',
            
            // Casting dates
            'dob'       => 'encrypted',
            'location'  => 'encrypted',
            // 'website'   => 'encrypted',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * UserInfo belongs to exactly one User.
     * Inverse of the One-to-One relationship.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getProfilePicUrlAttribute()
    {
        // Check if file exists based on our predictable naming convention
        $path = 'uploads/users/profile/profile_' . $this->user_id . '.webp';
        
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return asset('uploads/users/profile/default.jpg'); // Fallback
    }

    /**
     * Dynamically generates the Cover Image URL based on User ID
     */
    public function getCoverPicUrlAttribute()
    {
        $path = 'uploads/users/cover/cover_' . $this->user_id . '.webp';
        
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return null; // No default cover needed usually
    }


    // public function getProfilePicUrlAttribute()
    // {
    //     // Wildcard search: folder me dhoondho koi aisi file jiske end me '_{user_id}.webp' ho
    //     $files = glob(public_path('uploads/users/profile/*_' . $this->user_id . '.webp'));
        
    //     if (!empty($files)) {
    //         // Agar file mil gayi, toh uska naam (basename) nikal kar URL bana do
    //         $filename = basename($files[0]);
    //         return asset('uploads/users/profile/' . $filename);
    //     }
    //     return asset('uploads/users/profile/default.jpg'); // Fallback
    // }

    // public function getCoverPicUrlAttribute()
    // {
    //     $files = glob(public_path('uploads/users/cover/*_' . $this->user_id . '.webp'));
        
    //     if (!empty($files)) {
    //         $filename = basename($files[0]);
    //         return asset('uploads/users/cover/' . $filename);
    //     }
    //     return null; // Fallback
    // }
}