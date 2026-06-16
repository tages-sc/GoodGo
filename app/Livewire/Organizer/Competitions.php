<?php

namespace App\Livewire\Organizer;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionType;
use App\Enums\ExtensionType;
use App\Enums\RewardMode;
use App\Enums\ScoringType;
use App\Enums\TransportMode;
use App\Models\Competition;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Competitions extends Component
{
    use WithFileUploads;
    use WithPagination;

    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $editingId = null;
    public ?int $deletingId = null;

    // Form fields - Base
    public string $name = '';
    public string $description = '';
    public string $rules = '';
    public string $prizes = '';
    public $image = null;
    public $banner = null;
    public string $start_date = '';
    public string $end_date = '';
    public string $registration_start = '';
    public string $registration_end = '';
    public string $status = 'draft';

    // Form fields - Settings
    public bool $is_public = true;
    public ?int $max_participants = null;
    public bool $moderated_subscription = false;

    // Form fields - Track settings
    public ?int $min_track_distance = null;
    public ?int $max_track_distance = null;
    public int $max_daily_tracks = 1;
    public array $allowed_transport_modes = [];

    // Form fields - Credits
    public ?string $credits_per_km = null;
    public string $credits_multiplier = '1.00';
    public ?string $credits_to_euro = null;
    public array $credits_per_mode = [];
    public ?string $max_earning_per_person = null;

    // Form fields - New configuration
    public string $reward_mode = 'credits_based';
    public ?string $extension_type = null;
    public ?string $competition_type = null;
    public string $scoring_type = 'distance';
    public string $leaderboard_type = 'km';
    public bool $request_more_data = false;
    public array $max_distance_per_mode = [];
    public array $max_daily_distance_per_mode = [];
    public array $age_ranges = [];
    public ?string $questionnaire_url = null;
    public $extra_document = null;

    // Filters
    public string $search = '';
    public string $filterStatus = '';

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'prizes' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:2048',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'registration_start' => 'nullable|date',
            'registration_end' => 'nullable|date|after_or_equal:registration_start',
            'status' => 'required|in:' . implode(',', CompetitionStatus::values()),
            'is_public' => 'boolean',
            'max_participants' => 'nullable|integer|min:1',
            'moderated_subscription' => 'boolean',
            'min_track_distance' => 'nullable|integer|min:0',
            'max_track_distance' => 'nullable|integer|min:0|gte:min_track_distance',
            'max_daily_tracks' => 'required|integer|min:1|max:10',
            'allowed_transport_modes' => 'nullable|array',
            'credits_per_km' => 'nullable|numeric|min:0',
            'credits_multiplier' => 'required|numeric|min:0.01|max:10',
            'credits_to_euro' => 'nullable|numeric|min:0',
            'credits_per_mode' => 'nullable|array',
            'max_earning_per_person' => 'nullable|numeric|min:0',
            'reward_mode' => 'required|in:' . implode(',', RewardMode::values()),
            'extension_type' => 'nullable|in:' . implode(',', ExtensionType::values()),
            'competition_type' => 'nullable|in:' . implode(',', CompetitionType::values()),
            'scoring_type' => 'required|in:' . implode(',', ScoringType::values()),
            'leaderboard_type' => 'required|in:km,co2,gekoin',
            'request_more_data' => 'boolean',
            'max_distance_per_mode' => 'nullable|array',
            'max_daily_distance_per_mode' => 'nullable|array',
            'age_ranges' => 'nullable|array',
            'age_ranges.*' => 'in:<19,19-30,30-65,65+',
            'questionnaire_url' => 'nullable|url|max:500',
            'extra_document' => 'nullable|file|max:10240|mimes:pdf,doc,docx',
        ];
    }

    public function create(): void
    {
        // Verifica che l'organizzatore non abbia già una gara in corso
        if (Auth::user()->hasActiveCompetition()) {
            session()->flash('error', 'Hai già una gara in corso. Non puoi gestire più di una gara contemporaneamente.');
            return;
        }

        // Verifica che l'organizzatore abbia un ente padre
        if (!Auth::user()->parent_ente_id) {
            session()->flash('error', 'Non sei associato a nessun ente. Contatta l\'amministratore.');
            return;
        }

        $this->resetForm();
        $this->showModal = true;
    }

    public function edit(int $id): void
    {
        // L'organizzatore può modificare solo le proprie gare
        $competition = Competition::where('organizer_id', Auth::id())->findOrFail($id);

        $this->editingId = $id;
        $this->name = $competition->name;
        $this->description = $competition->description ?? '';
        $this->rules = $competition->rules ?? '';
        $this->prizes = $competition->prizes ?? '';
        $this->start_date = $competition->start_date->format('Y-m-d');
        $this->end_date = $competition->end_date->format('Y-m-d');
        $this->registration_start = $competition->registration_start?->format('Y-m-d\TH:i') ?? '';
        $this->registration_end = $competition->registration_end?->format('Y-m-d\TH:i') ?? '';
        $this->status = $competition->status->value;
        $this->is_public = $competition->is_public;
        $this->max_participants = $competition->max_participants;
        $this->moderated_subscription = $competition->moderated_subscription;
        $this->min_track_distance = $competition->min_track_distance;
        $this->max_track_distance = $competition->max_track_distance;
        $this->max_daily_tracks = $competition->max_daily_tracks;
        $this->allowed_transport_modes = $competition->allowed_transport_modes ?? [];
        $this->credits_per_km = $competition->credits_per_km;
        $this->credits_multiplier = (string) $competition->credits_multiplier;
        $this->credits_to_euro = $competition->credits_to_euro;
        $this->credits_per_mode = $competition->credits_per_mode ?? [];
        $this->max_earning_per_person = $competition->max_earning_per_person;
        $this->reward_mode = $competition->reward_mode?->value ?? 'credits_based';
        $this->extension_type = $competition->extension_type?->value;
        $this->competition_type = $competition->competition_type?->value;
        $this->scoring_type = $competition->scoring_type?->value ?? 'distance';
        $this->leaderboard_type = $competition->leaderboard_type ?? 'km';
        $this->request_more_data = $competition->request_more_data ?? false;
        $this->max_distance_per_mode = $competition->max_distance_per_mode ?? [];
        $this->max_daily_distance_per_mode = $competition->max_daily_distance_per_mode ?? [];
        $this->age_ranges = $competition->age_range ?? [];
        $this->questionnaire_url = $competition->questionnaire_url;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        $user = Auth::user();

        // Se sta creando una nuova gara, verifica che non ne abbia già una in corso
        if (!$this->editingId && $user->hasActiveCompetition()) {
            session()->flash('error', 'Hai già una gara in corso. Non puoi gestire più di una gara contemporaneamente.');
            $this->closeModal();
            return;
        }

        $data = [
            'name' => $this->name,
            'description' => $this->description ?: null,
            'rules' => $this->rules ?: null,
            'prizes' => $this->prizes ?: null,
            'ente_id' => $user->parent_ente_id, // Assegnata automaticamente all'ente padre
            'organizer_id' => $user->id, // Assegnata automaticamente a se stesso
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'registration_start' => $this->registration_start ?: null,
            'registration_end' => $this->registration_end ?: null,
            'status' => $this->status,
            'is_public' => $this->is_public,
            'max_participants' => $this->max_participants,
            'moderated_subscription' => $this->moderated_subscription,
            'min_track_distance' => $this->min_track_distance,
            'max_track_distance' => $this->max_track_distance,
            'max_daily_tracks' => $this->max_daily_tracks,
            'allowed_transport_modes' => !empty($this->allowed_transport_modes) ? $this->allowed_transport_modes : null,
            'credits_per_km' => $this->credits_per_km,
            'credits_multiplier' => $this->credits_multiplier,
            'credits_to_euro' => $this->credits_to_euro ?: null,
            'credits_per_mode' => !empty($this->credits_per_mode) ? $this->filterEmptyValues($this->credits_per_mode) : null,
            'max_earning_per_person' => $this->max_earning_per_person ?: null,
            'reward_mode' => $this->reward_mode,
            'extension_type' => $this->extension_type ?: null,
            'competition_type' => $this->competition_type ?: null,
            'scoring_type' => $this->scoring_type,
            'leaderboard_type' => $this->leaderboard_type,
            'request_more_data' => $this->request_more_data,
            'max_distance_per_mode' => !empty($this->max_distance_per_mode) ? $this->filterEmptyValues($this->max_distance_per_mode) : null,
            'max_daily_distance_per_mode' => !empty($this->max_daily_distance_per_mode) ? $this->filterEmptyValues($this->max_daily_distance_per_mode) : null,
            'age_range' => !empty($this->age_ranges) ? $this->age_ranges : null,
            'questionnaire_url' => $this->questionnaire_url ?: null,
        ];

        if ($this->image) {
            $data['image'] = $this->image->store('competitions', 'public');
        }

        if ($this->banner) {
            $data['banner'] = $this->banner->store('competitions/banners', 'public');
        }

        if ($this->extra_document) {
            $data['extra_document'] = $this->extra_document->store('competitions/documents', 'public');
        }

        if ($this->editingId) {
            $competition = Competition::where('organizer_id', Auth::id())->findOrFail($this->editingId);
            $competition->update($data);
            session()->flash('message', 'Gara aggiornata con successo.');
        } else {
            Competition::create($data);
            session()->flash('message', 'Gara creata con successo.');
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
            $competition = Competition::where('organizer_id', Auth::id())->findOrFail($this->deletingId);
            $competition->delete();
            session()->flash('message', 'Gara eliminata con successo.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function updateStatus(int $id, string $status): void
    {
        $competition = Competition::where('organizer_id', Auth::id())->findOrFail($id);
        $competition->update(['status' => $status]);
        session()->flash('message', 'Stato gara aggiornato.');
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
        $this->description = '';
        $this->rules = '';
        $this->prizes = '';
        $this->image = null;
        $this->banner = null;
        $this->start_date = '';
        $this->end_date = '';
        $this->registration_start = '';
        $this->registration_end = '';
        $this->status = 'draft';
        $this->is_public = true;
        $this->max_participants = null;
        $this->moderated_subscription = false;
        $this->min_track_distance = null;
        $this->max_track_distance = null;
        $this->max_daily_tracks = 1;
        $this->allowed_transport_modes = [];
        $this->credits_per_km = null;
        $this->credits_multiplier = '1.00';
        $this->credits_to_euro = null;
        $this->credits_per_mode = [];
        $this->max_earning_per_person = null;
        $this->reward_mode = 'credits_based';
        $this->extension_type = null;
        $this->competition_type = null;
        $this->scoring_type = 'distance';
        $this->leaderboard_type = 'km';
        $this->request_more_data = false;
        $this->max_distance_per_mode = [];
        $this->max_daily_distance_per_mode = [];
        $this->age_ranges = [];
        $this->questionnaire_url = null;
        $this->extra_document = null;
        $this->resetValidation();
    }

    private function filterEmptyValues(array $data): ?array
    {
        $filtered = array_filter($data, fn($value) => $value !== null && $value !== '');
        return !empty($filtered) ? $filtered : null;
    }

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (CompetitionStatus::cases() as $status) {
            $statuses[$status->value] = $status->label();
        }
        return $statuses;
    }

    public function getTransportModes(): array
    {
        $modes = [];
        foreach (TransportMode::cases() as $mode) {
            $modes[$mode->value] = $mode->label();
        }
        return $modes;
    }

    public function getRewardModes(): array
    {
        $modes = [];
        foreach (RewardMode::cases() as $mode) {
            $modes[$mode->value] = $mode->label();
        }
        return $modes;
    }

    public function getExtensionTypes(): array
    {
        $types = [];
        foreach (ExtensionType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        return $types;
    }

    public function getCompetitionTypes(): array
    {
        $types = [];
        foreach (CompetitionType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        return $types;
    }

    public function getScoringTypes(): array
    {
        $types = [];
        foreach (ScoringType::cases() as $type) {
            $types[$type->value] = $type->label();
        }
        return $types;
    }

    public function render()
    {
        $user = Auth::user();

        // L'organizzatore vede solo le proprie gare
        $query = Competition::query()
            ->where('organizer_id', $user->id)
            ->with(['ente'])
            ->withCount(['users', 'approvedUsers', 'pendingUsers'])
            ->orderBy('start_date', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                    ->orWhere('description', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        $hasActiveCompetition = $user->hasActiveCompetition();
        $activeCompetition = $hasActiveCompetition ? $user->getActiveCompetition() : null;

        return view('livewire.organizer.competitions', [
            'competitions' => $query->paginate(10),
            'statuses' => $this->getStatuses(),
            'transportModes' => $this->getTransportModes(),
            'rewardModes' => $this->getRewardModes(),
            'extensionTypes' => $this->getExtensionTypes(),
            'competitionTypes' => $this->getCompetitionTypes(),
            'scoringTypes' => $this->getScoringTypes(),
            'hasActiveCompetition' => $hasActiveCompetition,
            'activeCompetition' => $activeCompetition,
            'parentEnte' => $user->parentEnte,
        ]);
    }
}
