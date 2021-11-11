<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Comment extends Base
{
    use HasFactory;
    
    protected $fillable = [
        'entry_id', 
        'contents', 
        'commentor_id', 
    ];

    public function entry()
    {
        return $this->belongsTo(Entry::class);
    }

    public function commentor()
    {
        return $this->belongsTo(User::class);
    }
}
