<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostComment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'post_comments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'post_id',
        'parent_id',
        'content',
    ];

    /**
     * Disable the 'updated_at' timestamp since it doesn't exist in the table.
     */
    const UPDATED_AT = null;

    // protected function casts(): array
    // {
    //     return [
    //         'is_edited' => 'boolean',
    //     ];
    // }

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
     * Comment belongs to a specific Post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Self-Referencing: A comment can belong to a parent comment (Reply).
     */
    public function parent()
    {
        return $this->belongsTo(PostComment::class, 'parent_id');
    }

    /**
     * Self-Referencing: A comment can have many child comments (Replies).
     */
    public function replies()
    {
        return $this->hasMany(PostComment::class, 'parent_id');
    }
}