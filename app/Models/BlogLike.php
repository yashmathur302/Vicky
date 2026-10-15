<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogLike extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'blog_id',
        'user_id',
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
     * A like belongs to a specific User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A like belongs to a specific Blog.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}