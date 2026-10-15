<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventAttendee extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'event_attendees';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'event_id',
        'inviter_id',
        'invitee_id',
        'rsvp_status',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Attendee record belongs to a specific Event.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Attendee record belongs to the User who is attending/invited.
     */
    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }

    /**
     * Attendee record can optionally belong to a User who invited them.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }
}