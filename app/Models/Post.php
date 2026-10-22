<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'posts';

    protected $fillable = [
        'user_id',
        'group_id',
        'page_id',
        'event_id',
        'tagged_user_id',
        'post_type',
        'caption',
        'privacy',
        'feeling',
        'link',
        'location_name',
        'likes_count',
        'comments_count',
        'share_count',
    ];

    /**
     * Disable the 'updated_at' timestamp since it doesn't exist in the table.
     */
    const UPDATED_AT = null;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'likes_count' => 'integer',
            'comments_count' => 'integer',
            'share_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Post belongs to a User (Creator/Author).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Post can belong to a Group (if posted inside a group).
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Post can belong to a Page (if posted on a page timeline).
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Post can have a tagged User.
     */
    public function taggedUser()
    {
        return $this->belongsTo(User::class, 'tagged_user_id');
    }

    /**
     * Post has many Media items (images/videos attached).
     */
    public function media()
    {
        return $this->hasMany(PostMedia::class);
    }

    /**
     * Post has many Comments.
     */
    public function comments()
    {
        return $this->hasMany(PostComment::class);
    }

    /**
     * Post has many Likes/Reactions.
     */
    public function likes()
    {
        return $this->hasMany(PostLike::class); 
    }

    /**
     * Post has many Shares.
     */
    public function shares()
    {
        return $this->hasMany(PostShare::class);
    }
    
    /**
     * Post can belong to an Event (if posted inside an event discussion).
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}