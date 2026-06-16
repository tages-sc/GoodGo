<?php

namespace App\Livewire\Admin;

use App\Models\InvitationCode;
use App\Models\User;
use App\Enums\UserType;
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
    public string $ente_id = '';

    // Filters
    public string $search = '';
    public string $filterEnte = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'required|string|size:6|regex:/^\d{6}$/|unique:invitation_codes,code' . ($this->editingId ? ',' . $this->editingId : ''),
            'expires_at' => 'required|date|after_or_equal:today',
            'is_active' => 'boolean',
            'max_uses' => 'required|integer|min:0',
            'ente_id' => 'required|exists:users,id',
        ];
    }

    protected $messages = [
        'code.size' => 'Il codice deve essere di 6 cifre.',
        'code.regex' => 'Il codice deve contenere solo numeri.',
        'code.unique' => 'Questo codice è già in uso.',
        'ente_id.required' => 'Seleziona un ente.',
        'ente_id.exists' => 'L\'ente selezionato non esiste.',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->code = InvitationCode::generateUniqueCode();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $invitationCode = InvitationCode::findOrFail($id);

        $this->editingId = $id;
        $this->name = $invitationCode->name;
        $this->code = $invitationCode->code;
        $this->expires_at = $invitationCode->expires_at->format('Y-m-d');
        $this->is_active = $invitationCode->is_active;
        $this->max_uses = $invitationCode->max_uses;
        $this->ente_id = (string) $invitationCode->ente_id;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'ente_id' => $this->ente_id,
            'name' => $this->name,
            'code' => $this->code,
            'expires_at' => $this->expires_at,
            'is_active' => $this->is_active,
            'max_uses' => $this->max_uses,
        ];

        if ($this->editingId) {
            InvitationCode::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Codice invito aggiornato con successo.');
        } else {
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
        $invitationCode = InvitationCode::findOrFail($id);
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
            InvitationCode::findOrFail($this->deletingId)->delete();
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
        $this->ente_id = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = InvitationCode::with('ente')
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('code', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterEnte) {
            $query->where('ente_id', $this->filterEnte);
        }

        $enti = User::where('type', UserType::ENTE)->orderBy('name')->get(['id', 'name']);

        return view('livewire.admin.invitation-codes', [
            'invitationCodes' => $query->paginate(10),
            'enti' => $enti,
        ]);
    }
}
