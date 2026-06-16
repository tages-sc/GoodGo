<?php

namespace App\Livewire\Admin\Badges;

use App\Enums\BadgeCategory;
use App\Models\Badge;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    use WithFileUploads;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public bool $showWinnersModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;
    public ?int $viewingBadgeId = null;

    // Form fields
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $category = 'registrazione';
    public int $stars = 0;
    public ?string $threshold_type = null;
    public ?string $threshold_value = null;
    public bool $is_active = true;
    public int $sort_order = 0;
    public $icon;

    // Filters
    public string $filterCategory = '';
    public string $filterActive = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:badges,slug,' . $this->editingId,
            'description' => 'required|string',
            'category' => 'required|string',
            'stars' => 'required|integer|min:0|max:3',
            'threshold_type' => 'nullable|string|max:50',
            'threshold_value' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
            'icon' => 'nullable|image|max:1024',
        ];
    }

    public function create(): void
    {
        $this->resetForm();
        $this->sort_order = Badge::max('sort_order') + 1;
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        $badge = Badge::findOrFail($id);

        $this->editingId = $id;
        $this->name = $badge->name;
        $this->slug = $badge->slug;
        $this->description = $badge->description;
        $this->category = $badge->category->value;
        $this->stars = $badge->stars;
        $this->threshold_type = $badge->threshold_type;
        $this->threshold_value = $badge->threshold_value;
        $this->is_active = $badge->is_active;
        $this->sort_order = $badge->sort_order;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        // Shift automatico: se la posizione è già occupata da un altro badge, sposta in avanti
        $conflictQuery = Badge::where('sort_order', '>=', $this->sort_order);
        if ($this->editingId) {
            $conflictQuery->where('id', '!=', $this->editingId);
        }
        if ($conflictQuery->where('sort_order', $this->sort_order)->exists()) {
            Badge::where('sort_order', '>=', $this->sort_order)
                ->when($this->editingId, fn($q) => $q->where('id', '!=', $this->editingId))
                ->increment('sort_order');
        }

        $data = [
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'category' => $this->category,
            'stars' => $this->stars,
            'threshold_type' => $this->threshold_type ?: null,
            'threshold_value' => $this->threshold_value ?: null,
            'is_active' => $this->is_active,
            'sort_order' => $this->sort_order,
        ];

        if ($this->icon) {
            $data['icon'] = $this->icon->store('badges', 'public');
        }

        if ($this->editingId) {
            Badge::findOrFail($this->editingId)->update($data);
            session()->flash('message', 'Badge aggiornato con successo.');
        } else {
            Badge::create($data);
            session()->flash('message', 'Badge creato con successo.');
        }

        $this->closeModal();
    }

    public function updateOrder(array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            Badge::where('id', $id)->update(['sort_order' => $index]);
        }
        session()->flash('message', 'Ordine badge aggiornato con successo.');
    }

    public function toggleActive(int $id): void
    {
        $badge = Badge::findOrFail($id);
        $badge->update(['is_active' => !$badge->is_active]);
        session()->flash('message', 'Stato badge aggiornato.');
    }

    public function viewWinners(int $id): void
    {
        $this->viewingBadgeId = $id;
        $this->showWinnersModal = true;
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $badge = Badge::findOrFail($this->deletingId);
            $deletedOrder = $badge->sort_order;
            $badge->delete();

            // Rinumera le posizioni dopo l'eliminazione
            Badge::where('sort_order', '>', $deletedOrder)->decrement('sort_order');

            session()->flash('message', 'Badge eliminato con successo.');
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
        $this->slug = '';
        $this->description = '';
        $this->category = 'registrazione';
        $this->stars = 0;
        $this->threshold_type = null;
        $this->threshold_value = null;
        $this->is_active = true;
        $this->sort_order = 0;
        $this->icon = null;
        $this->resetValidation();
    }

    public function render()
    {
        $query = Badge::query()->ordered();

        if ($this->filterCategory) {
            $query->where('category', $this->filterCategory);
        }

        if ($this->filterActive !== '') {
            $query->where('is_active', $this->filterActive === '1');
        }

        $winners = null;
        $viewingBadge = null;
        if ($this->viewingBadgeId) {
            $viewingBadge = Badge::find($this->viewingBadgeId);
            $winners = $viewingBadge?->users()->orderBy('user_badges.earned_at', 'desc')->paginate(10, ['*'], 'winnersPage');
        }

        return view('livewire.admin.badges.index', [
            'badges' => $query->withCount('users')->paginate(15),
            'categories' => BadgeCategory::cases(),
            'winners' => $winners,
            'viewingBadge' => $viewingBadge,
        ]);
    }
}
