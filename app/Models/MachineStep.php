<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MachineStep extends Model
{
    protected $fillable = ['machine_id', 'step_name', 'sequence'];

    public function machine()
    {
        return $this->belongsTo(Machine::class);
    }
}
