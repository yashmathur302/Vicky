<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupInvite extends Model
{
    use HasFactory;

    protected $table = 'group_invites';
    
    protected $fillable = [
        'group_id',
        'inviter_id',
        'invitee_id',
        'status',
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
     * Group invite belongs to a specific Group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * The user who sent the invite.
     */
    public function inviter()
    {
        return $this->belongsTo(User::class, 'inviter_id');
    }

    /**
     * The user who received the invite (invitee).
     */
    public function invitee()
    {
        return $this->belongsTo(User::class, 'invitee_id');
    }
}