<?php

namespace App\Livewire\Admin;

use App\Models\Occupation;
use Livewire\Component;
use Livewire\WithPagination;

class Occupations extends Component
{
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields
    public string $name = '';
    public bool $is_active = true;
    public int $sort_order = 0;

    // Filters
    public string $search = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255|unique:occupations,name' . ($this->editingId ? ',' . $this->editingId : ''),
            'is_active' => 'boolean',
            'sort_order' => 'required|integer|min:0',
        ];
    }

    protected $messages = [
        'name.unique' => 'Questa occupazione esiste gia.',
    ];

    public function create(): void
    {
        $this->resetForm();
        $this->sort_order = Occupation::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $occupation = Occupation::findOrFail($id);

        $this->editingId = $id;
        $this->name = $occupation->name;
        $this->is_active = $occupation->is_active;
        $this->sort_order = $occupation->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Shift automatico: se la posizione è già occupata, sposta in avanti
        $conflictQuery = Occupation::where('sort_order', $this->sort_order);
        if ($this->editingId) {
            $conflictQuery->where('id', '!=', $this->editingId);
        }
        if ($conflictQuery->exists()) {
            Occupation::where('sort_order', '>=', $this->sort_order)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->increment('sort_order');
        }

        $data = [
            'name' => $this->name,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->editingId) {
            Occupation::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Occupazione aggiornata con successo.');
        } else {
            Occupation::create($data);
            session()->flash('message', 'Occupazione creata con successo.');
        }

        $this->closeModal();
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Occupation::where('id', $id)->update(['sort_order' => $index]);
        }
        session()->flash('message', 'Ordine aggiornato con successo.');
    }

    public function toggleActive(int $id): void
    {
        $occupation = Occupation::findOrFail($id);
        $occupation->update(['is_active' => !$occupation->is_active]);
        session()->flash('message', $occupation->is_active ? 'Occupazione attivata.' : 'Occupazione disattivata.');
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $occupation = Occupation::findOrFail($this->deletingId);
            $deletedOrder = $occupation->sort_order;
            $occupation->delete();

            // Rinumera le posizioni dopo l'eliminazione
            Occupation::where('sort_order', '>', $deletedOrder)->decrement('sort_order');

            session()->flash('message', 'Occupazione eliminata con successo.');
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
        $this->is_active = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Occupation::ordered();

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        return view('livewire.admin.occupations', [
            'occupations' => $query->paginate(20),
        ]);
    }
}
