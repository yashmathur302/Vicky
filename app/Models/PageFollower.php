<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PageFollower extends Model
{
    use HasFactory;

    protected $table = 'page_followers';


    protected $fillable = [
        'page_id',
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
     * The page that is being followed.
     */
    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * The user who is following the page.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}