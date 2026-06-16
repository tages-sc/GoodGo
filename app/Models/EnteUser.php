<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class EnteUser extends Pivot
{
    protected $table = 'ente_user';

    public $incrementing = true;

    protected $casts = [
        'requested_at' => 'datetime',
        'processed_at' => 'datetime',
    ];
}
