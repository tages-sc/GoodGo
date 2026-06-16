<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class UpdateUserProfile extends Component
{
    public string $username = '';
    public ?string $birth_date = null;
    public string $phone = '';
    public string $address = '';
    public string $city = '';
    public string $province = '';
    public string $postal_code = '';

    public function mount(): void
    {
        $profile = Auth::user()->profile;

        if ($profile) {
            $this->username = $profile->username ?? '';
            $this->birth_date = $profile->birth_date?->format('Y-m-d');
            $this->phone = $profile->phone ?? '';
            $this->address = $profile->address ?? '';
            $this->city = $profile->city ?? '';
            $this->province = $profile->province ?? '';
            $this->postal_code = $profile->postal_code ?? '';
        }
    }

    protected function rules(): array
    {
        $userId = Auth::id();

        return [
            'username' => "nullable|string|max:50|unique:user_profiles,username,{$userId},user_id",
            'birth_date' => 'nullable|date|before:today',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:100',
            'province' => 'nullable|string|max:5',
            'postal_code' => 'nullable|string|max:10',
        ];
    }

    public function save(): void
    {
        $this->validate();

        Auth::user()->profile()->updateOrCreate(
            ['user_id' => Auth::id()],
            [
                'username' => $this->username ?: null,
                'birth_date' => $this->birth_date ?: null,
                'phone' => $this->phone ?: null,
                'address' => $this->address ?: null,
                'city' => $this->city ?: null,
                'province' => $this->province ?: null,
                'postal_code' => $this->postal_code ?: null,
            ]
        );

        $this->dispatch('saved');
    }

    public function render()
    {
        return view('livewire.profile.update-user-profile');
    }
}
