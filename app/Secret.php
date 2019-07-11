<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Secret extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hash', 'secret', 'expire_after_views', 'expire_after'
    ];
}
