<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoginHistory extends Model
{
    use HasFactory;

    protected $table = 'login_histories';

    protected $fillable = [
        'user_id', 
        'ip_address', 
        'browser', 
        'operating_system',
    ];

    protected function casts(): array
    {
        // Data hamesha encrypted form me save hoga
        return [
            'ip_address'       => 'encrypted',
            'browser'          => 'encrypted',
            'operating_system' => 'encrypted',
        ];
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}