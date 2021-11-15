<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class ActivityLog extends Base
{
    use HasFactory;

    public $timestamps = true;
    
    protected $table = 'activity_log';

    protected $fillable = [
        'log_name', 
        'description', 
        'subject_type', 
        'subject_id', 
        'causer_type', 
        'causer_id', 
        'properties', 
    ];

    protected function getActorAttribute()
    {
        return resolve($this->causer_type)->find($this->causer_id);
    }

    protected function getActorTypeAttribute()
    {
        return strtolower( basename($this->causer_type) );
    }

    protected function getObjectAttribute()
    {
        return resolve($this->subject_type)->find($this->subject_id);
    }

    protected function getObjectTypeAttribute()
    {
        return strtolower( basename($this->subject_type) );
    }

    protected function getPropertiesAttribute($value)
    {
        return json_encode(json_decode($value)->attributes, JSON_PRETTY_PRINT);
    }

    protected function getUpdatedAtAttribute($value)
    {
        return resolve('App\Services\DateService')->formatTimestamp($value);
    }
}
