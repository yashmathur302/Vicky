<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory;

    protected $table = 'post_likes';

    protected $fillable = [
        'user_id',
        'post_id',
        'likes_type',
    ];

    /**
     * Disable the 'updated_at' timestamp since it doesn't exist in the table.
     */
    const UPDATED_AT = null;

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Like/Reaction belongs to a specific User who reacted.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Like/Reaction belongs to a specific Post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}