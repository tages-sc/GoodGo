<?php

namespace App\Livewire\Partner;

use App\Models\PartnerProfile;
use App\Services\GeocodingService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ProfileEdit extends Component
{
    use WithFileUploads;

    // Dati aziendali
    public string $company_name = '';
    public string $vat_number = '';
    public string $fiscal_code = '';
    public string $company_type = '';
    public string $company_address = '';
    public string $company_city = '';
    public string $company_province = '';
    public string $company_postal_code = '';
    public ?string $latitude = null;
    public ?string $longitude = null;

    // Sede legale
    public string $legal_address = '';
    public string $legal_city = '';
    public string $legal_province = '';
    public string $legal_postal_code = '';

    // Sede operativa
    public string $operational_address = '';
    public string $operational_city = '';
    public string $operational_province = '';
    public string $operational_postal_code = '';

    // Contatti aziendali
    public string $company_phone = '';
    public string $company_email = '';
    public string $pec = '';
    public string $website = '';

    // Rappresentante legale
    public string $legal_rep_name = '';
    public string $legal_rep_surname = '';
    public string $legal_rep_fiscal_code = '';
    public string $legal_rep_phone = '';
    public string $legal_rep_email = '';
    public string $legal_rep_birth_place = '';
    public ?string $legal_rep_birth_date = null;
    public string $legal_rep_address = '';
    public string $legal_rep_city = '';
    public string $legal_rep_province = '';
    public string $legal_rep_postal_code = '';

    // Dati bancari
    public string $iban = '';
    public string $bank_name = '';
    public string $swift_bic = '';

    // Operatore 1
    public string $operator_name = '';
    public string $operator_surname = '';
    public string $operator_fiscal_code = '';
    public string $operator_phone = '';
    public string $operator_email = '';

    // Operatore 2
    public string $operator2_name = '';
    public string $operator2_surname = '';
    public string $operator2_fiscal_code = '';

    // Logo e descrizione
    public $logo = null;
    public string $description = '';

    public function mount(): void
    {
        $profile = Auth::user()->partnerProfile;

        if ($profile) {
            $this->company_name = $profile->company_name ?? '';
            $this->vat_number = $profile->vat_number ?? '';
            $this->fiscal_code = $profile->fiscal_code ?? '';
            $this->company_type = $profile->company_type ?? '';
            $this->company_address = $profile->company_address ?? '';
            $this->company_city = $profile->company_city ?? '';
            $this->company_province = $profile->company_province ?? '';
            $this->company_postal_code = $profile->company_postal_code ?? '';
            $this->latitude = $profile->latitude !== null ? (string) $profile->latitude : null;
            $this->longitude = $profile->longitude !== null ? (string) $profile->longitude : null;
            $this->legal_address = $profile->legal_address ?? '';
            $this->legal_city = $profile->legal_city ?? '';
            $this->legal_province = $profile->legal_province ?? '';
            $this->legal_postal_code = $profile->legal_postal_code ?? '';
            $this->operational_address = $profile->operational_address ?? '';
            $this->operational_city = $profile->operational_city ?? '';
            $this->operational_province = $profile->operational_province ?? '';
            $this->operational_postal_code = $profile->operational_postal_code ?? '';
            $this->company_phone = $profile->company_phone ?? '';
            $this->company_email = $profile->company_email ?? '';
            $this->pec = $profile->pec ?? '';
            $this->website = $profile->website ?? '';
            $this->legal_rep_name = $profile->legal_rep_name ?? '';
            $this->legal_rep_surname = $profile->legal_rep_surname ?? '';
            $this->legal_rep_fiscal_code = $profile->legal_rep_fiscal_code ?? '';
            $this->legal_rep_phone = $profile->legal_rep_phone ?? '';
            $this->legal_rep_email = $profile->legal_rep_email ?? '';
            $this->legal_rep_birth_place = $profile->legal_rep_birth_place ?? '';
            $this->legal_rep_birth_date = $profile->legal_rep_birth_date?->format('Y-m-d');
            $this->legal_rep_address = $profile->legal_rep_address ?? '';
            $this->legal_rep_city = $profile->legal_rep_city ?? '';
            $this->legal_rep_province = $profile->legal_rep_province ?? '';
            $this->legal_rep_postal_code = $profile->legal_rep_postal_code ?? '';
            $this->iban = $profile->iban ?? '';
            $this->bank_name = $profile->bank_name ?? '';
            $this->swift_bic = $profile->swift_bic ?? '';
            $this->operator_name = $profile->operator_name ?? '';
            $this->operator_surname = $profile->operator_surname ?? '';
            $this->operator_fiscal_code = $profile->operator_fiscal_code ?? '';
            $this->operator_phone = $profile->operator_phone ?? '';
            $this->operator_email = $profile->operator_email ?? '';
            $this->operator2_name = $profile->operator2_name ?? '';
            $this->operator2_surname = $profile->operator2_surname ?? '';
            $this->operator2_fiscal_code = $profile->operator2_fiscal_code ?? '';
            $this->description = $profile->description ?? '';
        }
    }

    protected function rules(): array
    {
        return [
            'company_name' => 'required|string|max:255',
            'vat_number' => 'nullable|string|max:20',
            'fiscal_code' => 'nullable|string|max:20',
            'company_type' => 'nullable|string|max:100',
            'company_address' => 'nullable|string|max:255',
            'company_city' => 'nullable|string|max:100',
            'company_province' => 'nullable|string|max:5',
            'company_postal_code' => 'nullable|string|max:10',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'legal_address' => 'nullable|string|max:255',
            'legal_city' => 'nullable|string|max:100',
            'legal_province' => 'nullable|string|max:5',
            'legal_postal_code' => 'nullable|string|max:10',
            'operational_address' => 'nullable|string|max:255',
            'operational_city' => 'nullable|string|max:100',
            'operational_province' => 'nullable|string|max:5',
            'operational_postal_code' => 'nullable|string|max:10',
            'company_phone' => 'nullable|string|max:20',
            'company_email' => 'nullable|email|max:255',
            'pec' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
            'legal_rep_name' => 'nullable|string|max:100',
            'legal_rep_surname' => 'nullable|string|max:100',
            'legal_rep_fiscal_code' => 'nullable|string|max:20',
            'legal_rep_phone' => 'nullable|string|max:20',
            'legal_rep_email' => 'nullable|email|max:255',
            'legal_rep_birth_place' => 'nullable|string|max:100',
            'legal_rep_birth_date' => 'nullable|date',
            'legal_rep_address' => 'nullable|string|max:255',
            'legal_rep_city' => 'nullable|string|max:100',
            'legal_rep_province' => 'nullable|string|max:5',
            'legal_rep_postal_code' => 'nullable|string|max:10',
            'iban' => 'nullable|string|max:34',
            'bank_name' => 'nullable|string|max:255',
            'swift_bic' => 'nullable|string|max:11',
            'operator_name' => 'nullable|string|max:100',
            'operator_surname' => 'nullable|string|max:100',
            'operator_fiscal_code' => 'nullable|string|max:20',
            'operator_phone' => 'nullable|string|max:20',
            'operator_email' => 'nullable|email|max:255',
            'operator2_name' => 'nullable|string|max:100',
            'operator2_surname' => 'nullable|string|max:100',
            'operator2_fiscal_code' => 'nullable|string|max:20',
            'logo' => 'nullable|image|max:2048',
            'description' => 'nullable|string|max:2000',
        ];
    }

    public function save(GeocodingService $geocoder): void
    {
        $this->validate();

        $data = [
            'company_name' => $this->company_name,
            'vat_number' => $this->vat_number ?: null,
            'fiscal_code' => $this->fiscal_code ?: null,
            'company_type' => $this->company_type ?: null,
            'company_address' => $this->company_address ?: null,
            'company_city' => $this->company_city ?: null,
            'company_province' => $this->company_province ?: null,
            'company_postal_code' => $this->company_postal_code ?: null,
            'legal_address' => $this->legal_address ?: null,
            'legal_city' => $this->legal_city ?: null,
            'legal_province' => $this->legal_province ?: null,
            'legal_postal_code' => $this->legal_postal_code ?: null,
            'operational_address' => $this->operational_address ?: null,
            'operational_city' => $this->operational_city ?: null,
            'operational_province' => $this->operational_province ?: null,
            'operational_postal_code' => $this->operational_postal_code ?: null,
            'company_phone' => $this->company_phone ?: null,
            'company_email' => $this->company_email ?: null,
            'pec' => $this->pec ?: null,
            'website' => $this->website ?: null,
            'legal_rep_name' => $this->legal_rep_name ?: null,
            'legal_rep_surname' => $this->legal_rep_surname ?: null,
            'legal_rep_fiscal_code' => $this->legal_rep_fiscal_code ?: null,
            'legal_rep_phone' => $this->legal_rep_phone ?: null,
            'legal_rep_email' => $this->legal_rep_email ?: null,
            'legal_rep_birth_place' => $this->legal_rep_birth_place ?: null,
            'legal_rep_birth_date' => $this->legal_rep_birth_date ?: null,
            'legal_rep_address' => $this->legal_rep_address ?: null,
            'legal_rep_city' => $this->legal_rep_city ?: null,
            'legal_rep_province' => $this->legal_rep_province ?: null,
            'legal_rep_postal_code' => $this->legal_rep_postal_code ?: null,
            'iban' => $this->iban ?: null,
            'bank_name' => $this->bank_name ?: null,
            'swift_bic' => $this->swift_bic ?: null,
            'operator_name' => $this->operator_name ?: null,
            'operator_surname' => $this->operator_surname ?: null,
            'operator_fiscal_code' => $this->operator_fiscal_code ?: null,
            'operator_phone' => $this->operator_phone ?: null,
            'operator_email' => $this->operator_email ?: null,
            'operator2_name' => $this->operator2_name ?: null,
            'operator2_surname' => $this->operator2_surname ?: null,
            'operator2_fiscal_code' => $this->operator2_fiscal_code ?: null,
            'description' => $this->description ?: null,
        ];

        if ($this->logo) {
            $data['logo'] = $this->logo->store('partners/logos', 'public');
        }

        // Coordinate: l'override manuale dell'utente ha la precedenza sul geocoding automatico
        $existing = Auth::user()->partnerProfile;

        $manualLatitude = ($this->latitude !== null && $this->latitude !== '') ? (float) $this->latitude : null;
        $manualLongitude = ($this->longitude !== null && $this->longitude !== '') ? (float) $this->longitude : null;

        $existingLatitude = $existing?->latitude !== null ? (float) $existing->latitude : null;
        $existingLongitude = $existing?->longitude !== null ? (float) $existing->longitude : null;

        $userChangedCoordinates =
            $manualLatitude !== $existingLatitude
            || $manualLongitude !== $existingLongitude;

        $addressChanged = !$existing
            || $existing->company_address !== $data['company_address']
            || $existing->company_city !== $data['company_city']
            || $existing->company_province !== $data['company_province']
            || $existing->company_postal_code !== $data['company_postal_code'];

        if ($userChangedCoordinates) {
            // Override manuale: usa quanto inserito dall'utente
            $data['latitude'] = $manualLatitude;
            $data['longitude'] = $manualLongitude;
            $data['geocoded_at'] = ($manualLatitude !== null && $manualLongitude !== null) ? now() : null;
        } elseif ($addressChanged) {
            // Indirizzo cambiato e coordinate non toccate manualmente: re-geocoding
            $coords = $geocoder->forward(
                $data['company_address'],
                $data['company_city'],
                $data['company_province'],
                $data['company_postal_code']
            );

            if ($coords) {
                $data['latitude'] = $coords['latitude'];
                $data['longitude'] = $coords['longitude'];
                $data['geocoded_at'] = now();
            } else {
                $data['latitude'] = null;
                $data['longitude'] = null;
                $data['geocoded_at'] = null;
            }
        }

        Auth::user()->partnerProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        // Allinea il form ai valori salvati (utile in caso di re-geocoding automatico)
        $fresh = Auth::user()->fresh()->partnerProfile;
        $this->latitude = $fresh?->latitude !== null ? (string) $fresh->latitude : null;
        $this->longitude = $fresh?->longitude !== null ? (string) $fresh->longitude : null;

        $this->logo = null;
        session()->flash('message', 'Profilo aziendale aggiornato con successo.');
    }

    public function render()
    {
        $currentLogo = Auth::user()->partnerProfile?->logo;

        return view('livewire.partner.profile-edit', [
            'currentLogo' => $currentLogo,
        ]);
    }
}
