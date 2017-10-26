<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Visitor extends Model
{

    /**
     * Define which fields are fillable on the entity
     *
     * @var array
     */
    protected $fillable = ['ip'];

}
