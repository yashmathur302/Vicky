<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Currently logged-in user ka data fetch kar rahe hain
        $user = Auth::user();
        
        return view('frontend.home', compact('user'));
    }
}