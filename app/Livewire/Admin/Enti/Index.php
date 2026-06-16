<?php

namespace App\Livewire\Admin\Enti;

use App\Enums\UserType;
use App\Models\EnteProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
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

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email' . ($this->editingId ? ',' . $this->editingId : ''),
            'tipologia' => 'nullable|string|max:255',
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
        $ente = User::with('enteProfile')->findOrFail($id);

        $this->editingId = $id;
        $this->name = $ente->name;
        $this->email = $ente->email;

        if ($ente->enteProfile) {
            $this->tipologia = $ente->enteProfile->tipologia ?? '';
            $this->location = $ente->enteProfile->location ?? '';
            $this->descrizione = $ente->enteProfile->descrizione ?? '';
            $this->iscrizione_moderata = $ente->enteProfile->iscrizione_moderata;
            $this->website = $ente->enteProfile->website ?? '';
            $this->instagram_url = $ente->enteProfile->instagram_url ?? '';
            $this->linkedin_url = $ente->enteProfile->linkedin_url ?? '';
            $this->twitter_url = $ente->enteProfile->twitter_url ?? '';
            $this->facebook_url = $ente->enteProfile->facebook_url ?? '';
            $this->colore = $ente->enteProfile->colore ?? '#4CAF50';
        }

        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $userData = [
            'name' => $this->name,
            'email' => $this->email,
            'type' => UserType::ENTE,
            'email_verified_at' => now(),
        ];

        if ($this->password) {
            $userData['password'] = Hash::make($this->password);
        }

        if ($this->editingId) {
            $ente = User::findOrFail($this->editingId);
            $ente->update($userData);
        } else {
            $ente = User::create($userData);
        }

        // Gestisci profilo ente
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
            $profileData['logo'] = $this->logo->store('enti/logos', 'public');
        }

        if ($this->banner) {
            $profileData['banner'] = $this->banner->store('enti/banners', 'public');
        }

        EnteProfile::updateOrCreate(
            ['user_id' => $ente->id],
            $profileData
        );

        session()->flash('message', $this->editingId ? 'Ente aggiornato con successo.' : 'Ente creato con successo.');
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
            $ente = User::findOrFail($this->deletingId);

            // Non permettere eliminazione ente default
            if ($ente->enteProfile?->is_default) {
                session()->flash('error', 'Non è possibile eliminare l\'ente GoodGo default.');
                $this->showDeleteModal = false;
                $this->deletingId = null;
                return;
            }

            $ente->delete();
            session()->flash('message', 'Ente eliminato con successo.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
        $this->resetPage();
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
        $query = User::query()
            ->where('type', UserType::ENTE)
            ->with(['enteProfile'])
            ->withCount(['subscribers', 'approvedSubscribers', 'organizedCompetitions'])
            ->orderBy('name');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.enti.index', [
            'enti' => $query->paginate(10),
        ]);
    }
}
