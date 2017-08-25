<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;
use App\Models\Entities\CommandsParam;

class Command extends Model
{

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the process that owns the command
     */
    public function process()
    {
        return $this->belongsTo(Process::class);
    }

    /**
     * Get the commands params associated with the command
     */
    public function commandsParams()
    {
        return $this->hasMany(CommandsParam::class);
    }

}
