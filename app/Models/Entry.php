<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Entry extends Model
{
    public $timestamps = true;
    
    protected $table = 'entries';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'place', 
        'comments', 
        'user_id', 
        'img_url'
    ];

    protected function getCreatedAtAttribute($value)
    {
        return date('Y-m-d h:i:s', strtotime($value) );
    }

    protected function getUpdatedAtAttribute($value)
    {
        return date('Y-m-d h:i:s', strtotime($value) );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}