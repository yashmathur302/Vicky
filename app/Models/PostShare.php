<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostShare extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_shares';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'shared_to_user_id',
        'shared_to_page_id',
        'shared_to_group_id',
        'shared_to_event_id',
        'caption',
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
     * The original post that is being shared.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * The user who shared the post.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * The user to whose timeline the post was shared (if applicable).
     */
    public function sharedToUser()
    {
        return $this->belongsTo(User::class, 'shared_to_user_id');
    }

    /**
     * The page where the post was shared (if applicable).
     */
    public function sharedToPage()
    {
        return $this->belongsTo(Page::class, 'shared_to_page_id');
    }

    /**
     * The group where the post was shared (if applicable).
     */
    public function sharedToGroup()
    {
        return $this->belongsTo(Group::class, 'shared_to_group_id');
    }

    /**
     * The event where the post was shared (if applicable).
     */
    public function sharedToEvent()
    {
        return $this->belongsTo(Event::class, 'shared_to_event_id');
    }
}