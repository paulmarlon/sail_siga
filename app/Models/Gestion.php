<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Gestion extends Model
{
    use SoftDeletes;
    protected $table = 'gestions';
    protected $fillable = ['nombre'];
}
