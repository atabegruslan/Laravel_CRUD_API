<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class Region extends Base
{
    use HasFactory;

    protected $fillable = [
        'name', 
    ];

    public function places()
    {
        return $this->belongsToMany(Entry::class);
    }

    public function regionTree()
    {
        return $this->belongsTo(
            RegionTree::class,
            'id',
            'region_id'
        );
    }
}
