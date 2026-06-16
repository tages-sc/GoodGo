<?php

namespace App\Livewire\Ente;

use App\Enums\UserType;
use App\Models\EnteProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Organizers extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // User form fields
    public string $name = '';
    public string $email = '';
    public string $password = '';

    // EnteProfile form fields
    public string $tipologia = '';
    public string $location = '';
    public string $descrizione = '';
    public $logo = null;
    public $banner = null;
    public bool $iscrizione_moderata = false;
    public string $website = '';
    public string $instagram_url = '';
    public string $linkedin_url = '';
    public string $twitter_url = '';
    public string $facebook_url = '';
    public string $colore = '#4CAF50';

    // Filters
    public string $search = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingId ? ',' . $this->editingId : ''),
            'tipologia' => 'nullable|in:comune,azienda',
            'location' => 'nullable|string|max:255',
            'descrizione' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'iscrizione_moderata' => 'boolean',
            'website' => 'nullable|url|max:255',
            'instagram_url' => 'nullable|url|max:255',
            'linkedin_url' => 'nullable|url|max:255',
            'twitter_url' => 'nullable|url|max:255',
            'facebook_url' => 'nullable|url|max:255',
            'colore' => 'nullable|string|max:7|regex:/^#[0-9A-Fa-f]{6}$/',
        ];

        if (!$this->editingId) {
            $rules['password'] = 'required|string|min:8';
        } else {
            $rules['password'] = 'nullable|string|min:8';
        }

        return $rules;
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $organizer = User::with('enteProfile')->findOrFail($id);

        // Verifica che l'organizzatore appartenga a questo ente
        if ($organizer->parent_ente_id !== auth()->id()) {
            session()->flash('error', 'Non hai i permessi per modificare questo organizzatore.');
            return;
        }

        $this->editingId = $id;
        $this->name = $organizer->name;
        $this->email = $organizer->email;

        if ($organizer->enteProfile) {
            $this->tipologia = $organizer->enteProfile->tipologia ?? '';
            $this->location = $organizer->enteProfile->location ?? '';
            $this->descrizione = $organizer->enteProfile->descrizione ?? '';
            $this->iscrizione_moderata = $organizer->enteProfile->iscrizione_moderata;
            $this->website = $organizer->enteProfile->website ?? '';
            $this->instagram_url = $organizer->enteProfile->instagram_url ?? '';
            $this->linkedin_url = $organizer->enteProfile->linkedin_url ?? '';
            $this->twitter_url = $organizer->enteProfile->twitter_url ?? '';
            $this->facebook_url = $organizer->enteProfile->facebook_url ?? '';
            $this->colore = $organizer->enteProfile->colore ?? '#4CAF50';
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'type' => UserType::ORGANIZER,
            'parent_ente_id' => auth()->id(),
            'email_verified_at' => now(),
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $organizer = User::findOrFail($this->editingId);

            // Verifica che l'organizzatore appartenga a questo ente
            if ($organizer->parent_ente_id !== auth()->id()) {
                session()->flash('error', 'Non hai i permessi per modificare questo organizzatore.');
                return;
            }

            $organizer->update($userData);
        } else {
            $organizer = User::create($userData);
        }

        // Gestisci profilo ente/organizer
        $profileData = [
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
            $profileData['logo'] = $this->logo->store('organizers/logos', 'public');
        }

        if ($this->banner) {
            $profileData['banner'] = $this->banner->store('organizers/banners', 'public');
        }

        EnteProfile::updateOrCreate(
            ['user_id' => $organizer->id],
            $profileData
        );

        session()->flash('message', $this->editingId ? 'Organizzatore aggiornato con successo.' : 'Organizzatore creato con successo.');
        $this->closeModal();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $organizer = User::findOrFail($this->deletingId);

            // Verifica che l'organizzatore appartenga a questo ente
            if ($organizer->parent_ente_id !== auth()->id()) {
                session()->flash('error', 'Non hai i permessi per eliminare questo organizzatore.');
                $this->showDeleteModal = false;
                $this->deletingId = null;
                return;
            }

            $organizer->delete();
            session()->flash('message', 'Organizzatore eliminato con successo.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->password = '';
        $this->tipologia = '';
        $this->location = '';
        $this->descrizione = '';
        $this->logo = null;
        $this->banner = null;
        $this->iscrizione_moderata = false;
        $this->website = '';
        $this->instagram_url = '';
        $this->linkedin_url = '';
        $this->twitter_url = '';
        $this->facebook_url = '';
        $this->colore = '#4CAF50';
        $this->resetValidation();
    }

    public function render()
    {
        $query = auth()->user()->organizers()
            ->with(['enteProfile'])
            ->withCount(['organizedCompetitions'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.ente.organizers', [
            'organizers' => $query->paginate(10),
        ]);
    }
}
