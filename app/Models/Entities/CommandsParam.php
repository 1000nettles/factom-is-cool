<?php

namespace App\Models\Entities;

use Illuminate\Database\Eloquent\Model;

class CommandsParam extends Model
{

    /**
     * Disable timestamps
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Get the command that owns the commandsParam
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function command()
    {
        return $this->belongsTo(Command::class);
    }

}
