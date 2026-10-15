<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasFactory;

    protected $table = 'groups';

    protected $fillable = [
        'creator_user_id',
        'name',
        'description',
        'category',
        'cover_image',
        'privacy_level',
        'member_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'member_count' => 'integer',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * The user who created the group.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_user_id');
    }

    /**
     * Group has many members through the group_members table.
     */
    public function members()
    {
        return $this->hasMany(GroupMember::class);
    }

    /**
     * Group can have many posts created inside it.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Group can host many events.
     */
    public function events()
    {
        return $this->hasMany(Event::class);
    }

    /**
     * Group has many invites sent to users.
     */
    public function invites()
    {
        return $this->hasMany(GroupInvite::class);
    }

    /**
     * Group Categories
     */
    // public const CATEGORIES = [
    //     'technology' => 'Technology & IT',
    //     'education' => 'Education & Learning',
    //     'finance_and_trading' => 'Finance & Trading',
    //     'business' => 'Business & Retail',
    //     'gaming' => 'Gaming',
    //     'entertainment' => 'Entertainment',
    //     'sports' => 'Sports',
    //     'health_and_lifestyle' => 'Health & Lifestyle',
    //     'other' => 'Other',
    // ];
}