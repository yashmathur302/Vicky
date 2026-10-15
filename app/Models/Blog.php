<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'content',
        'video_url',
        'image_url',
        'cover_image',
        'like_counts',
        'comment_counts',
        'share_counts',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            // Ensuring counts are always treated as integers
            'like_counts' => 'integer',
            'comment_counts' => 'integer',
            'share_counts' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     * Isse Laravel URL parameters me 'id' ki jagah 'slug' ko default manega.
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Blog belongs to a User (Author).
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Blog has many Likes.
     */
    public function likes()
    {
        return $this->hasMany(BlogLike::class);
    }

    /**
     * Blog has many Comments.
     */
    public function comments()
    {
        return $this->hasMany(BlogComment::class);
    }

    /**
     * Blog has many Shares.
     */
    public function shares()
    {
        return $this->hasMany(BlogShare::class);
    }
}