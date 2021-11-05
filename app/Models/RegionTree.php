<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RegionTree extends Model
{
    use HasFactory;

    protected $table = 'region_tree';

    protected $fillable = [
        'region_id',
        'parent_id',
    ];
}
