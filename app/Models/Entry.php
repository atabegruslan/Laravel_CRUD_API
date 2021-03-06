<?php

namespace App\Models;

class Entry extends Base
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

    public function regions()
    { // https://laravel.com/api/5.6/Illuminate/Database/Eloquent/Concerns/HasRelationships.html
        return $this->belongsToMany(
            Region::class,
            'entry_region',
            'place_id',
            'region_id'
        );
    }
}