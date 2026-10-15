<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    use HasFactory;

    protected $table = 'reports';

    protected $fillable = [
        'reporter_user_id',
        'reported_user_id',
        'post_id',
        'comment_id',
        'reported_group_id',
        'reported_page_id',
        'reported_event_id',
        'reported_blog_id',
        'reason',
        'status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who submitted the report (Reporter).
     */
    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_user_id');
    }

    /**
     * The user who is being reported (if applicable).
     */
    public function reportedUser()
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }

    /**
     * The post being reported (if applicable).
     */
    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    /**
     * The comment being reported (if applicable).
     */
    public function comment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }

    /**
     * The group being reported (if applicable).
     */
    public function reportedGroup()
    {
        return $this->belongsTo(Group::class, 'reported_group_id');
    }

    /**
     * The page being reported (if applicable).
     */
    public function reportedPage()
    {
        return $this->belongsTo(Page::class, 'reported_page_id');
    }

    /**
     * The event being reported (if applicable).
     */
    public function reportedEvent()
    {
        return $this->belongsTo(Event::class, 'reported_event_id');
    }

    /**
     * The blog being reported (if applicable).
     */
    public function reportedBlog()
    {
        return $this->belongsTo(Blog::class, 'reported_blog_id');
    }
}