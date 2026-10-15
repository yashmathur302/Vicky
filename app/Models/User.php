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
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_banned'         => 'boolean',
            'last_login'        => 'datetime',
            'email'             => 'encrypted',
            'session'           => 'encrypted',
            'browser'           => 'encrypted',
            'ip_address'        => 'encrypted',
            'operating_system'  => 'encrypted',

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

    // public function getAccountStatusAttribute()
    // {
    //     // Edge Case 1: Admin hamesha active rahega
    //     if ($this->user_role === 'admin') {
    //         return 'active';
    //     }

    //     $timeLimit = Carbon::now()->subMinutes(2); // Production - subDays(30)

    //     // Edge Case 2: Agar naya account hai (kabhi login nahi kiya)
    //     if (is_null($this->last_login)) {
    //         // Toh created_at date se check karenge
    //         if ($this->created_at && $this->created_at->greaterThanOrEqualTo($timeLimit)) {
    //             return 'active';
    //         }
    //         return 'inactive';
    //     }

    //     // Main Logic: last_login se time check karenge
    //     if ($this->last_login->greaterThanOrEqualTo($timeLimit)) {
    //         return 'active';
    //     }

    //     return 'inactive';
    // }
}