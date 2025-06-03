<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                Auth::guard('admin')->login($admin);

            $admin = Auth::guard('admin')->user();

                SystemLog::create([
                    'admin_id' => $admin->id,
                    'description' => "Admin '{$admin->username}' logged in",
                    'action' => 'Admin Login    ',
                    'model' => 'Login',
                ]);

                $logs = SystemLog::with('admin')->latest()->get();
                
                return redirect()->route('admin.menu');
            }

            return back()->withErrors(['username' => 'Invalid credentials']);
    }

    public function showChangePasswordForm() {
    return view('changepassword');
    }

    public function updatePassword(Request $request) {

        $request->validate([
        'current_password' => 'required',
        'new_password' => 'required|min:8|confirmed',
        ]);

        $admin = Auth::guard('admin')->user(); 

        if (!$admin) {
            return redirect()->route('admin.login')->with('error', 'Not authenticated.');
        }

        if (!Hash::check($request->current_password, $admin->password)) {
            return back()->with('error', 'Current password is incorrect.');
        }

        $admin->password = Hash::make($request->new_password);
        $admin->save();

        SystemLog::create([
            'admin_id' => auth('admin')->id(),
            'description' => "Admin '{$admin->username}' changed password",
            'action' => 'Admin Change Password',
            'model' => 'Change Password',
        ]);

        return back()->with('success', 'Password updated successfully!');
    }
    
}
