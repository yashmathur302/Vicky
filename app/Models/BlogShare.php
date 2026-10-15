<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogShare extends Model
{
    use HasFactory;

    protected $fillable = [
        'blog_id',
        'user_id',
        'share_place',
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
     * A share belongs to a specific User who shared it.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * A share belongs to a specific Blog being shared.
     */
    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }
}