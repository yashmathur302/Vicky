<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Story extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'stories';

    protected $fillable = [
        'user_id',
        'page_id',
        'type',
        'media_url',
        'content',
        'bg_color',
        'link',
        'location_name',
        'privacy_level',
        'views_count',
        'allow_replies',
        'expires_at',
    ];


    protected function casts(): array
    {
        return [
            'allow_replies' => 'boolean',
            'views_count'   => 'integer',
            'expires_at'    => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who created the story.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The page that posted the story (if applicable).
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes (Helper functions for querying)
    |--------------------------------------------------------------------------
    */

    /**
     * Scope a query to only include active (unexpired) stories.
     * Aap controller me `Story::active()->get()` use kar sakte hain.
     */
    // public function scopeActive($query)
    // {
    //     return $query->where('expires_at', '>', now());
    // }
}