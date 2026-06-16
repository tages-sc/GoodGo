<?php

namespace App\Livewire\Admin\Tracks;

use App\Enums\TrackStatus;
use App\Enums\TransportMode;
use App\Enums\UserType;
use App\Models\Competition;
use App\Models\Track;
use App\Models\User;
use App\Services\TrackUploadService;
use App\Services\TrackValidationManager;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;

    // Modal states
    public bool $showUploadModal = false;
    public bool $showDeleteModal = false;
    public bool $showValidateModal = false;
    public ?int $deletingId = null;
    public ?int $validatingId = null;

    // Upload form
    public $trackFile = null;
    public ?int $uploadUserId = null;
    public ?int $uploadCompetitionId = null;

    // Validation form
    public string $validationAction = 'valid';
    public string $rejectionReason = '';

    // Filters
    public string $search = '';
    public string $filterStatus = '';
    public string $filterUser = '';
    public string $filterCompetition = '';
    public string $filterTransportMode = '';

    protected function rules(): array
    {
        return [
            'trackFile' => 'required|file|mimes:zip,txt,csv|max:10240',
            'uploadUserId' => 'required|exists:users,id',
            'uploadCompetitionId' => 'nullable|exists:competitions,id',
        ];
    }

    public function openUploadModal(): void
    {
        $this->resetUploadForm();
        $this->showUploadModal = true;
    }

    public function processUpload(): void
    {
        $this->validate();

        try {
            $user = User::findOrFail($this->uploadUserId);
            $uploadService = app(TrackUploadService::class);

            $extension = $this->trackFile->getClientOriginalExtension();

            if ($extension === 'zip') {
                $track = $uploadService->processUpload(
                    $this->trackFile,
                    $user,
                    $this->uploadCompetitionId
                );
            } else {
                $track = $uploadService->processTextFile(
                    $this->trackFile,
                    $user,
                    $this->uploadCompetitionId
                );
            }

            session()->flash('message', "Traccia caricata con successo! ID: {$track->id}, Distanza: {$track->total_distance_km} km");
            $this->closeUploadModal();
        } catch (\Exception $e) {
            $this->addError('trackFile', $e->getMessage());
        }
    }

    public function closeUploadModal(): void
    {
        $this->showUploadModal = false;
        $this->resetUploadForm();
    }

    private function resetUploadForm(): void
    {
        $this->trackFile = null;
        $this->uploadUserId = null;
        $this->uploadCompetitionId = null;
        $this->resetValidation();
    }

    public function confirmDelete(int $id): void
    {
        $this->deletingId = $id;
        $this->showDeleteModal = true;
    }

    public function delete(): void
    {
        if ($this->deletingId) {
            $track = Track::findOrFail($this->deletingId);
            $uploadService = app(TrackUploadService::class);
            $uploadService->deleteTrack($track);
            session()->flash('message', 'Traccia eliminata con successo.');
        }
        $this->showDeleteModal = false;
        $this->deletingId = null;
    }

    public function openValidateModal(int $id): void
    {
        $this->validatingId = $id;
        $this->validationAction = 'valid';
        $this->rejectionReason = '';
        $this->showValidateModal = true;
    }

    public function validate_track(TrackValidationManager $validationManager): void
    {
        if (!$this->validatingId) {
            return;
        }

        $track = Track::findOrFail($this->validatingId);

        if ($this->validationAction === 'valid') {
            $creditsAdded = $validationManager->validate($track, auth()->id());
            session()->flash('message', 'Traccia validata con successo.' . ($creditsAdded > 0 ? " Assegnati {$creditsAdded} crediti." : ''));
        } else {
            if (empty($this->rejectionReason)) {
                $this->addError('rejectionReason', 'La motivazione è obbligatoria per invalidare una traccia.');
                return;
            }

            $creditsRemoved = $validationManager->invalidate($track, $this->rejectionReason, auth()->id());
            session()->flash('message', 'Traccia invalidata.' . ($creditsRemoved > 0 ? " Rimossi {$creditsRemoved} crediti." : ''));
        }

        $this->showValidateModal = false;
        $this->validatingId = null;
    }

    public function quickValidate(int $id, TrackValidationManager $validationManager): void
    {
        $track = Track::findOrFail($id);
        $creditsAdded = $validationManager->validate($track, auth()->id());
        session()->flash('message', 'Traccia validata con successo.' . ($creditsAdded > 0 ? " Assegnati {$creditsAdded} crediti." : ''));
    }

    public function setProcessing(int $id): void
    {
        $track = Track::findOrFail($id);
        $track->markAsProcessing();
        session()->flash('message', 'Traccia impostata come in elaborazione.');
    }

    public function getStatuses(): array
    {
        $statuses = [];
        foreach (TrackStatus::cases() as $status) {
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

    public function render()
    {
        $query = Track::query()
            ->with(['user', 'competition', 'validator', 'segments'])
            ->orderBy('created_at', 'desc');

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('session_id', 'like', '%' . $this->search . '%')
                    ->orWhereHas('user', function ($uq) {
                        $uq->where('name', 'like', '%' . $this->search . '%')
                            ->orWhere('email', 'like', '%' . $this->search . '%');
                    });
            });
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterUser) {
            $query->where('user_id', $this->filterUser);
        }

        if ($this->filterCompetition) {
            $query->where('competition_id', $this->filterCompetition);
        }

        if ($this->filterTransportMode) {
            $query->where('primary_transport_mode', $this->filterTransportMode);
        }

        return view('livewire.admin.tracks.index', [
            'tracks' => $query->paginate(15),
            'statuses' => $this->getStatuses(),
            'transportModes' => $this->getTransportModes(),
            'users' => User::where('type', UserType::USER)->orderBy('name')->get(),
            'competitions' => Competition::orderBy('name')->get(),
        ]);
    }
}
