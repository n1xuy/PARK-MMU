<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Admin;
use App\Models\SystemLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Add this property to define default redirect path
    protected $redirectTo = '/';
    
    public function registration(Request $request)
    {
        $validated = $request->validate([
            'fullname' => ['required', 'min:3', 'max:30'],
            'username' => ['required', 'min:4', 'max:20', Rule::unique('users','username')],
            'email' => ['required','email', Rule::unique('users','email')],
            'password' => ['required','min:8', 'max:20'],
            'confirm-password' => ['required', 'same:password'],
        ], [
            //error message
            'fullname.required' => 'Please enter your full name',
            'username.unique' => 'This username is already taken',
            'email.unique' => 'email is already been use',
            'confirm-password.same' => 'The password do not match',
        ]);

        $user = User::create([
            'fullname' => $validated['fullname'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
        ]);
        
        Auth::login($user);
        return $this->handlePostLoginRedirect(); // Modified this line
    }
    
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        
        // First try regular user login
        if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ])) {
            $request->session()->regenerate();
            return $this->handlePostLoginRedirect();
        }
        
        if (Auth::guard('admin')->attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ])) {
            $request->session()->regenerate();
            
            $admin = Auth::guard('admin')->user();
            
            SystemLog::create([
                'admin_id' => $admin->id,
                'description' => "Admin '{$admin->username}' logged in via user login",
                'action' => 'Admin Login',
                'model' => 'Login',
            ]);
            
            return redirect()->route('admin.menu')
                ->with('success', 'Welcome to Admin Dashboard!');
        }
        
        $userExists = User::where('username', $credentials['username'])->exists();
        $adminExists = Admin::where('username', $credentials['username'])->exists();
        
        return back()->withErrors([
            $userExists || $adminExists ? 'password' : 'username' => 
                $userExists || $adminExists 
                    ? 'The password is incorrect' 
                    : 'The username does not exist',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('student.login');
    }

    protected function handlePostLoginRedirect()
    {
        // Check for redirect URL from parking report
        if ($requestRedirect = request()->input('redirect')) {
            return redirect()->to($requestRedirect);
        }

        // Check for Laravel's intended URL
        if (session()->has('url.intended')) {
            return redirect()->to(session()->pull('url.intended'));
        }

        // Default redirect
        return redirect()->route('home');
    }
}