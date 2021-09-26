<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends User
{
    protected $table = 'users'; // utilize users table
    
    public function documents()
    {
        return $this->hasMany(Document::class, 'user_id');
    }
}