<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Bans extends Model
{
    protected $fillable=['fk_name', 'hardban'];
}
