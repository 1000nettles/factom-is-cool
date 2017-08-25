<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class Process extends Model
{

    /**
     * Specify our table here due to plurality issues
     *
     * @var string
     */
    protected $table = 'processes';

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

}
