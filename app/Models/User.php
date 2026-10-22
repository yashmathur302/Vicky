<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'user_role',
        'session',
        'browser',
        'ip_address',
        'operating_system',
        'last_login',
        'account_status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'verification_reminder_sent_at'     => 'datetime',
            'email_verified_at'                 => 'datetime',
            'password'                          => 'hashed',
            'is_banned'                         => 'boolean',
            'last_login'                        => 'datetime',
            'email'                             => 'encrypted',
            'session'                           => 'encrypted',
            'browser'                           => 'encrypted',
            'ip_address'                        => 'encrypted',
            'operating_system'                  => 'encrypted',

        ];
    }

    // Save hone se pehle hash generate karega
    protected static function booted()
    {
        static::saving(function ($user) {
            // Agar email set hua hai, to uska hash bana kar email_hash me daal do
            if ($user->isDirty('email') && !empty($user->email)) {
                $user->email_hash = blind_hash($user->email);
            }
            // Same for username
            // if ($user->isDirty('username') && !empty($user->username)) {
            //     $user->username_hash = blind_hash($user->username);
            // }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function getAvatarUrlAttribute(): string
    {
        $relative = 'uploads/users/profile/profile_' . $this->getKey() . '.webp';
        $absolute = public_path($relative);

        return is_file($absolute)
            ? asset($relative) . '?v=' . filemtime($absolute)
            : asset('frontend/images/default-avatar.svg');
    }

    /**
     * User has one UserInfo profile.
     */
    public function userInfo()
    {
        return $this->hasOne(UserInfo::class);
    }

    /**
     * User can create many Posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * User can create many Blogs.
     */
    public function blogs()
    {
        return $this->hasMany(Blog::class);
    }

    /**
     * User can create many Groups.
     * Custom foreign key 'creator_user_id' is used.
     */
    public function createdGroups()
    {
        return $this->hasMany(Group::class, 'creator_user_id');
    }

    /**
     * User can create many Pages.
     * Custom foreign key 'creator_id' is used.
     */
    public function createdPages()
    {
        return $this->hasMany(Page::class, 'creator_id');
    }

    /**
     * Followers Relationship (Self-Referencing Many-to-Many).
     * Users that are following THIS user.
     */
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'following_id', 'follower_id')
                    ->withTimestamps();
    }

    /**
     * Following Relationship (Self-Referencing Many-to-Many).
     * Users that THIS user is following.
     */
    public function following()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'following_id')
                    ->withTimestamps();
    }
    
    /**
     * Friends Relationship (Self-Referencing Many-to-Many).
     * Assuming friends are stored with sender_id and receiver_id.
     */
    public function sentFriendRequests()
    {
        return $this->hasMany(Friend::class, 'sender_id');
    }

    public function receivedFriendRequests()
    {
        return $this->hasMany(Friend::class, 'receiver_id');
    }

    /**
     * The pages followed by the user.
     */
    public function followedPages()
    {
        return $this->hasMany(PageFollower::class);
    }

    // Naya Accessor (Attribute)
    public function getAccountStatusAttribute()
    {
       
        if ($this->user_role === 'admin') {
            return 'active';
        }

        $timeLimit = now()->subDays(1);

        // 1. Agar account soft delete ho chuka hai
        if ($this->deleted_at !== null) {
            return 'inactive';
        }

        // 2. Agar pichle 2 minute me login kiya hai
        if ($this->last_login && $this->last_login >= $timeLimit) {
            return 'active';
        }

        // 3. Agar account naya hai (kabhi login nahi kiya) par grace period me hai
        if (is_null($this->last_login) && $this->created_at >= $timeLimit) {
            return 'active';
        }

        // Baaki sab conditions me inactive
        return 'inactive';
    }
}