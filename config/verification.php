<?php
return [
    // Testing ke liye Minutes use kar rahe hain
    // Production me inke naam badalkar 'reminder_after_days' kar dena aur values 4, 7 set kar dena
    'reminder_after_minutes' => env('VERIFY_REMINDER_AFTER_MINS', 1),
    'ban_after_minutes'      => env('VERIFY_BAN_AFTER_MINS', 2),
    
    // Ye secret password hoga external pinger ke liye
    'ping_token'             => env('REMINDER_PING_TOKEN', 'my-super-secret-token-123'),
];