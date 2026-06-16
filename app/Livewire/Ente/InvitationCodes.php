<?php

namespace App\Livewire\Ente;

use App\Models\InvitationCode;
use Livewire\Component;
use Livewire\WithPagination;

class InvitationCodes extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $name = '';
    public string $code = '';
    public string $expires_at = '';
    public bool $is_active = true;
    public int $max_uses = 0;

    // Filters
    public string $search = '';

    protected function rules(): array
    {
        $rules = [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^\d{6}$/|unique:invitation_codes,code' . ($this->editingId ? ',' . $this->editingId : ''),
            'expires_at' => 'required|date|after_or_equal:today',
            'is_active' => 'boolean',
            'max_uses' => 'required|integer|min:0',
        ];

        return $rules;
    }

    protected $messages = [
        'code.size' => 'Il codice deve essere di 6 cifre.',
        'code.regex' => 'Il codice deve contenere solo numeri.',
        'code.unique' => 'Questo codice è già in uso.',
        'expires_at.after_or_equal' => 'La data di scadenza deve essere oggi o nel futuro.',
        'max_uses.min' => 'Il limite utilizzi deve essere 0 (illimitato) o maggiore.',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->code = InvitationCode::generateUniqueCode();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $invitationCode = $this->findOwnCode($id);
        if (!$invitationCode) {
            return;
        }

        $this->editingId = $id;
        $this->name = $invitationCode->name;
        $this->code = $invitationCode->code;
        $this->expires_at = $invitationCode->expires_at->format('Y-m-d');
        $this->is_active = $invitationCode->is_active;
        $this->max_uses = $invitationCode->max_uses;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
            'max_uses' => $this->max_uses,
        ];

        if ($this->editingId) {
            $invitationCode = $this->findOwnCode($this->editingId);
            if (!$invitationCode) {
                return;
            }
            $invitationCode->update($data);
            session()->flash('message', 'Codice invito aggiornato con successo.');
        } else {
            $data['ente_id'] = auth()->id();
            InvitationCode::create($data);
            session()->flash('message', 'Codice invito creato con successo.');
        }

        $this->closeModal();
    }

    public function generateCode(): void
    {
        $this->code = InvitationCode::generateUniqueCode();
    }

    public function toggleActive(int $id): void
    {
        $invitationCode = $this->findOwnCode($id);
        if (!$invitationCode) {
            return;
        }

        $invitationCode->update(['is_active' => !$invitationCode->is_active]);
        session()->flash('message', $invitationCode->is_active ? 'Codice attivato.' : 'Codice disattivato.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $invitationCode = $this->findOwnCode($this->deletingId);
            if (!$invitationCode) {
                $this->showDeleteModal = false;
                $this->deletingId = null;
                return;
            }

            $invitationCode->delete();
            session()->flash('message', 'Codice invito eliminato con successo.');
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
        $this->code = '';
        $this->expires_at = '';
        $this->is_active = true;
        $this->max_uses = 0;
        $this->resetValidation();
    }

    private function findOwnCode(int $id): ?InvitationCode
    {
        $invitationCode = InvitationCode::find($id);

        if (!$invitationCode || $invitationCode->ente_id !== auth()->id()) {
            session()->flash('error', 'Non hai i permessi per gestire questo codice.');
            return null;
        }

        return $invitationCode;
    }

    public function render()
    {
        $query = InvitationCode::where('ente_id', auth()->id())
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        return view('livewire.ente.invitation-codes', [
            'invitationCodes' => $query->paginate(10),
        ]);
    }
}
