<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserType;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    // Filtri
    public string $searchId = '';
    public string $searchEmail = '';
    public string $searchName = '';
    public string $filterType = '';
    public string $filterPlatform = '';
    public string $filterDateFrom = '';
    public string $filterDateTo = '';

    // Modal eliminazione
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?string $deletingName = null;

    protected $queryString = [
        'searchId' => ['except' => ''],
        'searchEmail' => ['except' => ''],
        'searchName' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterPlatform' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function updatingSearchId(): void
    {
        $this->resetPage();
    }

    public function updatingSearchEmail(): void
    {
        $this->resetPage();
    }

    public function updatingSearchName(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterPlatform(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingFilterDateTo(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset([
            'searchId',
            'searchEmail',
            'searchName',
            'filterType',
            'filterPlatform',
            'filterDateFrom',
            'filterDateTo',
        ]);
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $user = User::find($id);
        if ($user) {
            $this->deletingId = $id;
            $this->deletingName = $user->name;
            $this->showDeleteModal = true;
        }
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $user = User::find($this->deletingId);

            if ($user) {
                // Non permettere eliminazione super admin
                if ($user->isSuperAdmin()) {
                    session()->flash('error', 'Non è possibile eliminare un Super Admin.');
                    $this->showDeleteModal = false;
                    $this->deletingId = null;
                    $this->deletingName = null;
                    return;
                }

                // Non permettere eliminazione ente default
                if ($user->isEnte() && $user->enteProfile?->is_default) {
                    session()->flash('error', 'Non è possibile eliminare l\'ente GoodGo default.');
                    $this->showDeleteModal = false;
                    $this->deletingId = null;
                    $this->deletingName = null;
                    return;
                }

                $user->delete();
                session()->flash('message', 'Utente eliminato con successo.');
            }
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingName = null;
    }

    public function render()
    {
        $query = User::query()
            ->with(['profile', 'partnerProfile', 'enteProfile'])
            ->withCount(['competitions', 'tracks'])
            ->orderByDesc('created_at');

        // Filtro per ID
        if ($this->searchId) {
            $query->where('id', $this->searchId);
        }

        // Filtro per Email
        if ($this->searchEmail) {
            $query->where('email', 'like', '%' . $this->searchEmail . '%');
        }

        // Filtro per Nome
        if ($this->searchName) {
            $query->where('name', 'like', '%' . $this->searchName . '%');
        }

        // Filtro per Tipo
        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // Filtro per Piattaforma
        if ($this->filterPlatform) {
            $query->where('platform', $this->filterPlatform);
        }

        // Filtro per Data iscrizione (da)
        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        // Filtro per Data iscrizione (a)
        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        return view('livewire.admin.users.index', [
            'users' => $query->paginate(15),
            'userTypes' => UserType::cases(),
            'platforms' => ['ios', 'android', 'web'],
        ]);
    }
}
