<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\ChangeAware;

class Base extends Model
{
	use ChangeAware;
}