<?php

namespace App\Livewire\Ente;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Members extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterStatus = '';

    public function approve(int $userId): void
    {
        Auth::user()->subscribers()->updateExistingPivot($userId, [
            'status' => 'approved',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);
        session()->flash('message', 'Iscrizione approvata.');
    }

    public function reject(int $userId): void
    {
        Auth::user()->subscribers()->updateExistingPivot($userId, [
            'status' => 'rejected',
            'processed_at' => now(),
            'processed_by' => Auth::id(),
        ]);
        session()->flash('message', 'Iscrizione rifiutata.');
    }

    public function remove(int $userId): void
    {
        Auth::user()->subscribers()->detach($userId);
        session()->flash('message', 'Utente rimosso dall\'ente.');
    }

    public function render()
    {
        $query = Auth::user()->subscribers()
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

        $approvedCount = Auth::user()->approvedSubscribers()->count();
        $pendingCount = Auth::user()->pendingSubscribers()->count();

        return view('livewire.ente.members', [
            'members' => $query->paginate(20),
            'approvedCount' => $approvedCount,
            'pendingCount' => $pendingCount,
        ]);
    }
}
