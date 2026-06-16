<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartnerProfile extends Model
{
    protected $fillable = [
        'user_id',
        // Dati aziendali
        'company_name',
        'vat_number',
        'fiscal_code',
        'company_type',
        'company_address',
        'company_city',
        'company_province',
        'company_postal_code',
        'latitude',
        'longitude',
        'geocoded_at',
        'nfc_code',
        // Sede legale
        'legal_address',
        'legal_city',
        'legal_province',
        'legal_postal_code',
        // Sede operativa
        'operational_address',
        'operational_city',
        'operational_province',
        'operational_postal_code',
        // Contatti aziendali
        'company_phone',
        'company_email',
        'pec',
        'website',
        // Rappresentante legale
        'legal_rep_name',
        'legal_rep_surname',
        'legal_rep_fiscal_code',
        'legal_rep_phone',
        'legal_rep_email',
        'legal_rep_birth_place',
        'legal_rep_birth_date',
        'legal_rep_address',
        'legal_rep_city',
        'legal_rep_province',
        'legal_rep_postal_code',
        // Dati bancari
        'iban',
        'bank_name',
        'swift_bic',
        // Operatore 1
        'operator_name',
        'operator_surname',
        'operator_fiscal_code',
        'operator_phone',
        'operator_email',
        // Operatore 2
        'operator2_name',
        'operator2_surname',
        'operator2_fiscal_code',
        // Logo e descrizione
        'logo',
        'description',
        // Verifica
        'is_verified',
        'verified_at',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'verified_at' => 'datetime',
            'legal_rep_birth_date' => 'date',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'geocoded_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
