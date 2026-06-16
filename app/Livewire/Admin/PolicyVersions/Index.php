<?php

namespace App\Livewire\Admin\PolicyVersions;

use App\Enums\PolicyType;
use App\Models\PolicyVersion;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    public string $type = 'privacy';
    public string $version = '';
    public string $title = '';
    public string $content = '';
    public string $status = 'draft';

    public string $filterType = '';
    public string $filterStatus = '';

    protected function rules(): array
    {
        return [
            'type' => 'required|in:privacy,terms',
            'version' => 'required|string|max:20',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'status' => 'required|in:draft,published',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $policy = PolicyVersion::findOrFail($id);

        // Blocca modifica di policy già pubblicate
        if ($policy->isPublished()) {
            session()->flash('error', 'Non è possibile modificare una policy già pubblicata.');
            return;
        }

        $this->editingId = $id;
        $this->type = $policy->type->value;
        $this->version = $policy->version;
        $this->title = $policy->title;
        $this->content = $policy->content;
        $this->status = $policy->status;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Blocca salvataggio se si tenta di modificare una policy già pubblicata
        if ($this->editingId) {
            $existing = PolicyVersion::findOrFail($this->editingId);
            if ($existing->isPublished()) {
                session()->flash('error', 'Non è possibile modificare una policy già pubblicata.');
                return;
            }
        }

        $data = [
            'type' => $this->type,
            'version' => $this->version,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'published_at' => $this->status === 'published' ? now() : null,
        ];

        if ($this->editingId) {
            $existing->update($data);
            session()->flash('message', 'Policy aggiornata con successo.');
        } else {
            PolicyVersion::create($data);
            session()->flash('message', 'Policy creata con successo.');
        }

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
            PolicyVersion::findOrFail($this->deletingId)->delete();
            session()->flash('message', 'Policy eliminata con successo.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function publish(int $id): void
    {
        $policy = PolicyVersion::findOrFail($id);
        $policy->publish(auth()->user());
        session()->flash('message', 'Policy pubblicata con successo.');
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->type = 'privacy';
        $this->version = '';
        $this->title = '';
        $this->content = '';
        $this->status = 'draft';
        $this->resetValidation();
    }

    public function render()
    {
        $query = PolicyVersion::query()->orderBy('created_at', 'desc');

        if ($this->filterType) {
            $query->where('type', $this->filterType);
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        return view('livewire.admin.policy-versions.index', [
            'policies' => $query->paginate(10),
            'types' => PolicyType::cases(),
        ]);
    }
}
