<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Http\Request;

class AdminController extends Controller
{

public function login(Request $request)
{
    $request->validate(['password' => 'required|string']);
    
    $correctPassword = '$2y$12$heaGMbOJHZ/q1.PHw.PJGutYdflCSFNceayep6jcV2yw8g.3lnO3G'; 
    
    if (Hash::check($request->password, $correctPassword)) {
        $request->session()->put('admin_authenticated', true);
        $request->session()->regenerate();
        return redirect()->route('admin.menu');
    }
    
    return back()->withErrors(['password' => 'Invalid password']);
}
}
