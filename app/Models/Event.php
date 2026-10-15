<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    // use HasFactory, SoftDeletes;
    use HasFactory;

    protected $table = 'events';
    
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category_id',
        'start_at',
        'end_at',
        'location_name',
        'cover_image',
        'privacy_level',
        'status',
        // 'going_count',
        // 'interested_count',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'going_count' => 'integer',
            'interested_count' => 'integer',
            'location_name' => 'encrypted',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    //Event belongs to a User (Creator).
    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Event has many Attendees (RSVPs).
    public function attendees()
    {
        return $this->hasMany(EventAttendee::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Dynamically generates the Cover Image URL based on Event ID
     */
    public function getCoverPicUrlAttribute()
    {
        // Fixed naming convention (DB me name save kiye bina kaam karega)
        $path = 'uploads/events/cover/event_cover_' . $this->id . '.webp';
        
        if (file_exists(public_path($path))) {
            return asset($path);
        }
        return null; // Fallback - default null
    }
}