<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BlogComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'blog_id',
        'parent_id',
        'comment',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Comment belongs to a User (Author of the comment).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Comment belongs to a specific Blog.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    /**
     * Self-Referencing: A comment can belong to a parent comment (Reply).
     */
    public function parent()
    {
        return $this->belongsTo(BlogComment::class, 'parent_id');
    }

    /**
     * Self-Referencing: A comment can have many child comments (Replies).
     */
    public function replies()
    {
        return $this->hasMany(BlogComment::class, 'parent_id');
    }
}