<?php

namespace App\Livewire\Admin\Enti;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Members extends Component
{
    use WithPagination;

    public User $ente;

    public string $search = '';
    public string $filterStatus = '';

    public function mount(User $ente): void
    {
        $this->ente = $ente;
    }

    public function approve(int $userId): void
    {
        $this->ente->subscribers()->updateExistingPivot($userId, [
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
        session()->flash('message', 'Iscrizione approvata.');
    }

    public function reject(int $userId): void
    {
        $this->ente->subscribers()->updateExistingPivot($userId, [
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => auth()->id(),
        ]);
        session()->flash('message', 'Iscrizione rifiutata.');
    }

    public function remove(int $userId): void
    {
        $this->ente->subscribers()->detach($userId);
        session()->flash('message', 'Utente rimosso dall\'ente.');
    }

    public function render()
    {
        $query = $this->ente->subscribers()
            ->with(['profile'])
            ->orderByPivot('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('email', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->wherePivot('status', $this->filterStatus);
        }

        return view('livewire.admin.enti.members', [
            'members' => $query->paginate(20),
        ]);
    }
}
