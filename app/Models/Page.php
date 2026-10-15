<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pages';

    protected $fillable = [
        'creator_id',
        // 'followers_user_id',
        'name',
        'username',
        'user_role',
        'contact_email',
        'contact_phone',
        'category',
        'bio',
        'profile_picture',
        'cover_picture',
        'website_url',
        'is_verified',
        'followers_count',
        'following_count',
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
            'is_verified' => 'boolean',
            'followers_count' => 'integer',
            'following_count' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     * Isse Laravel URL routing me ID ki jagah 'username' ka use karega.
     */
    public function getRouteKeyName()
    {
        return 'username';
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who created the page (Creator).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /**
     * Page has many posts published on its timeline.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Page can host many events.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Page can receive reports.
     */
    public function reports()
    {
        return $this->hasMany(Report::class, 'reported_page_id');
    }

    /**
     * The followers of this page.
     */
    public function followers()
    {
        return $this->hasMany(PageFollower::class);
    }
}