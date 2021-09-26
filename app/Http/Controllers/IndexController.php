<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Profile;
use App\Models\Document;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{
    public function index()
    {
        if (!Auth::guard('profile')->check()) {
            return view('index');
        }
        if (Auth::guard('profile')->user()->role==='admin') {
            $documents = Document::all();
        }
        else {
            $documents = Document::all()->where('visibility', 'user');
        }
        foreach($documents as $document)
        {
            $document->username = $document->user->username;
            $document->email = $document->user->email;
            $document->filename = basename($document->filepath); // last element of array
        }
        return view('index', ['documents' => $documents]);
    }

    public function logout(Request $request)
    {
        Auth::guard('profile')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}