<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property-read Occupation|null $occupation
 */
class UserProfile extends Model
{
    protected $fillable = [
        'user_id',
        'username',
        'photo',
        'birth_date',
        'phone',
        'address',
        'city',
        'province',
        'postal_code',
        'extra_fields',
        'occupation_id',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'extra_fields' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function occupation(): BelongsTo
    {
        return $this->belongsTo(Occupation::class);
    }
}
