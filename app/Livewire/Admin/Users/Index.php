<?php

namespace App\Livewire\Admin\Users;

use App\Enums\UserType;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    private const PER_PAGE = 15;

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

    // Selezione multipla
    public array $selected = [];
    public bool $showBulkDeleteModal = false;

    protected $queryString = [
        'searchId' => ['except' => ''],
        'searchEmail' => ['except' => ''],
        'searchName' => ['except' => ''],
        'filterType' => ['except' => ''],
        'filterPlatform' => ['except' => ''],
        'filterDateFrom' => ['except' => ''],
        'filterDateTo' => ['except' => ''],
    ];

    public function updating(string $property): void
    {
        // Cambiando i filtri la selezione non sarebbe più visibile: si azzera
        if (str_starts_with($property, 'search') || str_starts_with($property, 'filter')) {
            $this->selected = [];
        }
    }

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
            'selected',
        ]);
        $this->resetPage();
    }

    /**
     * Seleziona/deseleziona tutti gli utenti eliminabili della pagina corrente.
     */
    public function toggleSelectPage(): void
    {
        $pageIds = $this->deletablePageIds();

        if ($pageIds && !array_diff($pageIds, $this->selected)) {
            $this->selected = array_values(array_diff($this->selected, $pageIds));
        } else {
            $this->selected = array_values(array_unique(array_merge($this->selected, $pageIds)));
        }
    }

    public function clearSelection(): void
    {
        $this->selected = [];
    }

    public function confirmBulkDelete(): void
    {
        if ($this->selected) {
            $this->showBulkDeleteModal = true;
        }
    }

    public function bulkDelete(): void
    {
        $deleted = 0;
        $skipped = 0;

        User::with('enteProfile')->whereIn('id', $this->selected)->get()->each(function (User $user) use (&$deleted, &$skipped) {
            if (!$this->canBeDeleted($user)) {
                $skipped++;
                return;
            }

            $user->delete();
            $deleted++;
        });

        $message = "{$deleted} utenti eliminati con successo.";
        if ($skipped) {
            $message .= " {$skipped} esclusi (Super Admin o ente GoodGo default).";
        }
        session()->flash('message', $message);

        $this->selected = [];
        $this->showBulkDeleteModal = false;
        $this->resetPage();
    }

    private function canBeDeleted(User $user): bool
    {
        return !$user->isSuperAdmin() && !($user->isEnte() && $user->enteProfile?->is_default);
    }

    /**
     * @return array<int, int>
     */
    private function deletablePageIds(): array
    {
        return $this->buildQuery()
            ->with('enteProfile')
            ->paginate(self::PER_PAGE)
            ->getCollection()
            ->filter(fn (User $user) => $this->canBeDeleted($user))
            ->pluck('id')
            ->all();
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

    private function buildQuery()
    {
        $query = User::query()->orderByDesc('created_at');

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

        return $query;
    }

    public function render()
    {
        $users = $this->buildQuery()
            ->with(['profile', 'partnerProfile', 'enteProfile'])
            ->withCount(['competitions', 'tracks'])
            ->paginate(self::PER_PAGE);

        $deletableIds = $users->getCollection()
            ->filter(fn (User $user) => $this->canBeDeleted($user))
            ->pluck('id')
            ->all();

        return view('livewire.admin.users.index', [
            'users' => $users,
            'pageSelected' => $deletableIds && !array_diff($deletableIds, array_map('intval', $this->selected)),
            'userTypes' => UserType::cases(),
            'platforms' => ['ios', 'android', 'web'],
        ]);
    }
}
