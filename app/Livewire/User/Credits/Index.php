<?php

namespace App\Livewire\User\Credits;

use App\Enums\CreditLogType;
use App\Models\CreditLog;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function getTypes(): array
    {
        $types = [];
        foreach (CreditLogType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        return $types;
    }

    public function render()
    {
        $userId = auth()->id();

        $query = CreditLog::query()
            ->forUser($userId)
            ->orderByDesc('created_at');

        if ($this->search) {
            $query->where('description', 'like', '%' . $this->search . '%');
        }

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        // Statistiche
        $user = auth()->user();
        $baseQuery = CreditLog::query()->forUser($userId);

        $stats = [
            'balance' => (float) $user->credits,
            'total_earned' => (float) (clone $baseQuery)->additions()->sum('amount'),
            'total_spent' => abs((float) (clone $baseQuery)->subtractions()->sum('amount')),
        ];

        return view('livewire.user.credits.index', [
            'logs' => $query->paginate(20),
            'types' => $this->getTypes(),
            'stats' => $stats,
        ]);
    }
}
