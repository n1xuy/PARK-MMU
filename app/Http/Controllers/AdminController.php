<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{

public function login(Request $request)
{
    $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('username', $request->username)->first();

        if ($admin && Hash::check($request->password, $admin->password)) {

            SystemLog::create([
                'description' => "Admin '{$admin->username}' logged in",
                'action' => 'Admin Login',
            ]);

            $request->session()->put('admin_authenticated', true);
            $request->session()->put('admin_id', $admin->id);
            $request->session()->regenerate();
            return redirect()->route('admin.menu');
        }

        return back()->withErrors(['username' => 'Invalid credentials']);
}
}
