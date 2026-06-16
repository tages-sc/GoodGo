<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class UpdateEnteProfile extends Component
{
    use WithFileUploads;

    public string $tipologia = '';
    public string $location = '';
    public string $descrizione = '';
    public bool $iscrizione_moderata = false;
    public string $website = '';
    public string $instagram_url = '';
    public string $linkedin_url = '';
    public string $twitter_url = '';
    public string $facebook_url = '';
    public string $colore = '#000000';
    public $logo = null;
    public $banner = null;

    public function mount(): void
    {
        $profile = Auth::user()->enteProfile;

        if ($profile) {
            $this->tipologia = $profile->tipologia ?? '';
            $this->location = $profile->location ?? '';
            $this->descrizione = $profile->descrizione ?? '';
            $this->iscrizione_moderata = $profile->iscrizione_moderata ?? false;
            $this->website = $profile->website ?? '';
            $this->instagram_url = $profile->instagram_url ?? '';
            $this->linkedin_url = $profile->linkedin_url ?? '';
            $this->twitter_url = $profile->twitter_url ?? '';
            $this->facebook_url = $profile->facebook_url ?? '';
            $this->colore = $profile->colore ?? '#000000';
        }
    }

    protected function rules(): array
    {
        return [
            'tipologia' => 'nullable|string|in:comune,azienda',
            'location' => 'nullable|string|max:255',
            'descrizione' => 'nullable|string|max:2000',
            'iscrizione_moderata' => 'boolean',
            'website' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'colore' => 'nullable|string|max:7',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'tipologia' => $this->tipologia ?: null,
            'location' => $this->location ?: null,
            'descrizione' => $this->descrizione ?: null,
            'iscrizione_moderata' => $this->iscrizione_moderata,
            'website' => $this->website ?: null,
            'instagram_url' => $this->instagram_url ?: null,
            'linkedin_url' => $this->linkedin_url ?: null,
            'twitter_url' => $this->twitter_url ?: null,
            'facebook_url' => $this->facebook_url ?: null,
            'colore' => $this->colore ?: null,
        ];

        if ($this->logo) {
            $currentProfile = Auth::user()->enteProfile;
            if ($currentProfile?->logo) {
                Storage::disk('public')->delete($currentProfile->logo);
            }
            $data['logo'] = $this->logo->store('enti/logos', 'public');
        }

        if ($this->banner) {
            $currentProfile = Auth::user()->enteProfile;
            if ($currentProfile?->banner) {
                Storage::disk('public')->delete($currentProfile->banner);
            }
            $data['banner'] = $this->banner->store('enti/banners', 'public');
        }

        Auth::user()->enteProfile()->updateOrCreate(
            ['user_id' => Auth::id()],
            $data
        );

        $this->logo = null;
        $this->banner = null;

        $this->dispatch('saved');
    }

    public function render()
    {
        $currentProfile = Auth::user()->enteProfile;

        return view('livewire.profile.update-ente-profile', [
            'currentLogo' => $currentProfile?->logo,
            'currentBanner' => $currentProfile?->banner,
        ]);
    }
}
