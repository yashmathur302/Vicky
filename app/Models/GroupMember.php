<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupMember extends Model
{
    use HasFactory;

    protected $table = 'group_members';

    protected $fillable = [
        'group_id',
        'user_id',
        'role',
        'status',
        'joined_at',
    ];

    /**
     * Customizing timestamp column names since 'created_at' does not exist.
     */
    const CREATED_AT = 'joined_at';

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
     * Group member belongs to a specific Group.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Group member belongs to a specific User.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}