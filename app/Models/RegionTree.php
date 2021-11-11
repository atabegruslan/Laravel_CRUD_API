<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegionTree extends Base
{
    use HasFactory;

    protected $table = 'region_tree';

    protected $fillable = [
        'region_id',
        'parent_id',
    ];
}
