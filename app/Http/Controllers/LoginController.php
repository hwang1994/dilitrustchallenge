<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;

use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{

    public function login(Request $request)
    {
        if (Auth::guard('profile')->check()) {
            return redirect()->route('home')->withErrors('Already logged in!');
        }
        $allParams  = $request->all();
        $password = $allParams['password'];
        $userName = $allParams['username'];
        if (filter_var($userName, FILTER_VALIDATE_EMAIL)) {
            $user = User::all()->where('email', $userName)->pop();
        }
        else {
            $user = User::all()->where('username', $userName)->pop();
        }      
        if ($user !== null && Hash::check($password, $user->password)) {
            Auth::guard('profile')->login($user, true);
            return redirect()->route('home');
        }
        else {
            return redirect()->route('home')->withErrors('Email/Username not found or incorrect password!');
        }
    }

    public function signup(Request $request)
    {
        if (Auth::guard('profile')->check()) {
            return redirect()->route('home')->withErrors('Please log out before signing up another account!');
        }
        $request->validate([
            'username'  => ['required', 'min:7', 'max:15', 'alpha_num'],
            'email' => ['required', 'email', 'regex:/(.*)@dilitrust\.com$/i'], //only dilitrust ppl allowed
            'password'  => ['required', 'confirmed', 'max:15', Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised()], //following laravel documentation here for secure password
            'role'=> ['required', 'in:user,admin']
        ]);

        $allParams  = $request->all();
        $user = DB::table('users')->where('email', $allParams['email'])->orWhere('username',$allParams['username'])->first(); //won't allow one to register the same email and username again
        
        if ($user === null) {
            $user = new User();
            $user->username = $allParams['username'];
            $user->email = $allParams['email'];
            $user->password = Hash::make($allParams['password']);
            $user->role = $allParams['role'];
            $user->save();
            Auth::guard('profile')->login($user, true);
            return redirect()->route('home');
        }
        else {
            return redirect()->route('home')->withErrors('Email or username already registered');
        }
    }

}