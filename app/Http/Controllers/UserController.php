<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    
    public function registration(Request $request){

        $validated = $request->validate([
            'fullname' => ['required', 'min:3', 'max:30'],
            'username' => ['required', 'min:4', 'max:20', Rule::unique('users','username')],
            'email' => ['required','email', Rule::unique('users','email')],
            'password' => ['required','min:8', 'max:20'],
            'confirm-password' => ['required', 'same:password'],
        ],[
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
        return redirect()->route('home');
    }

    

    public function login (Request $request){
        $credentials = $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);
        
         if (Auth::attempt([
            'username' => $credentials['username'],
            'password' => $credentials['password']
        ])) {
            $request->session()->regenerate();
            return redirect()->route('home');
        }

        $userExists = User::where('username', $credentials['username'])->exists();

        return back()->withErrors([
            $userExists ? 'password' : 'username' => $userExists 
                ? 'The password is incorrect' 
                : 'The username does not exist',
        ])->withInput();
    }

    public function logout(Request $request){
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('student.login');

    }
    
}


