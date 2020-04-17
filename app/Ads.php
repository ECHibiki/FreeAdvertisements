<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class Ads extends Model
{
    protected $fillable=['fk_name', 'uri', 'url', 'ip', 'size', 'clicks'];
}
