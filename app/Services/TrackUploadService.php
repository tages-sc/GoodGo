<?php

namespace App\Services;

use App\Enums\TrackStatus;
use App\Jobs\ProcessTrackJob;
use App\Models\Track;
use App\Models\TrackSegment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class TrackUploadService
{
    public function __construct(
        protected TrackParserService $parserService
    ) {}

    /**
     * Processa un file ZIP caricato e crea la traccia
     *
     * @param UploadedFile $file Il file ZIP caricato
     * @param User $user L'utente proprietario della traccia
     * @param int|null $competitionId ID della competizione (opzionale)
     * @param bool $autoProcess Se true, dispatcha il job per la validazione automatica
     * @return Track La traccia creata
     */
    public function processUpload(UploadedFile $file, User $user, ?int $competitionId = null, bool $autoProcess = true): Track
    {
        // Verifica che sia un file ZIP
        if ($file->getClientOriginalExtension() !== 'zip') {
            throw new \InvalidArgumentException('Il file deve essere in formato ZIP');
        }

        // Calcola hash per evitare duplicati
        $fileHash = hash_file('sha256', $file->getRealPath());

        // Verifica duplicati per questo utente
        $existingTrack = Track::where('user_id', $user->id)
            ->where('original_file_hash', $fileHash)
            ->first();

        if ($existingTrack) {
            throw new \InvalidArgumentException('Questa traccia è già stata caricata (file duplicato)');
        }

        // Estrai il contenuto del TXT dal ZIP
        $txtContent = $this->extractTxtFromZip($file);

        if (empty($txtContent)) {
            throw new \InvalidArgumentException('Nessun file TXT trovato nello ZIP');
        }

        // Parsa il contenuto
        $parsedData = $this->parserService->parse($txtContent);

        // Salva il file ZIP originale
        $storedPath = $this->storeOriginalFile($file, $user->id, $parsedData['session_id']);

        // Crea la traccia nel database
        $track = $this->createTrackFromParsedData($parsedData, $user, $competitionId, $storedPath, $fileHash);

        // Dispatcha il job per la validazione automatica
        if ($autoProcess) {
            ProcessTrackJob::dispatch($track);
        }

        return $track;
    }

    /**
     * Processa un file TXT direttamente (senza ZIP)
     *
     * @param bool $autoProcess Se true, dispatcha il job per la validazione automatica
     */
    public function processTextFile(UploadedFile $file, User $user, ?int $competitionId = null, bool $autoProcess = true): Track
    {
        if (!in_array($file->getClientOriginalExtension(), ['txt', 'csv'])) {
            throw new \InvalidArgumentException('Il file deve essere in formato TXT o CSV');
        }

        $fileHash = hash_file('sha256', $file->getRealPath());

        $existingTrack = Track::where('user_id', $user->id)
            ->where('original_file_hash', $fileHash)
            ->first();

        if ($existingTrack) {
            throw new \InvalidArgumentException('Questa traccia è già stata caricata (file duplicato)');
        }

        $txtContent = file_get_contents($file->getRealPath());
        $parsedData = $this->parserService->parse($txtContent);

        $storedPath = $this->storeOriginalFile($file, $user->id, $parsedData['session_id']);

        $track = $this->createTrackFromParsedData($parsedData, $user, $competitionId, $storedPath, $fileHash);

        // Dispatcha il job per la validazione automatica
        if ($autoProcess) {
            ProcessTrackJob::dispatch($track);
        }

        return $track;
    }

    /**
     * Estrae il contenuto del file TXT dallo ZIP
     */
    protected function extractTxtFromZip(UploadedFile $file): string
    {
        $zip = new ZipArchive();

        if ($zip->open($file->getRealPath()) !== true) {
            throw new \InvalidArgumentException('Impossibile aprire il file ZIP');
        }

        $txtContent = null;

        // Cerca un file .txt nel ZIP
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Ignora le cartelle __MACOSX e i file nascosti
            if (str_starts_with($filename, '__MACOSX') || str_starts_with(basename($filename), '.')) {
                continue;
            }

            if (pathinfo($filename, PATHINFO_EXTENSION) === 'txt') {
                $txtContent = $zip->getFromIndex($i);
                break;
            }
        }

        $zip->close();

        return $txtContent ?? '';
    }

    /**
     * Salva il file originale nello storage
     */
    protected function storeOriginalFile(UploadedFile $file, int $userId, string $sessionId): string
    {
        $extension = $file->getClientOriginalExtension();
        $filename = "{$sessionId}.{$extension}";
        $path = "tracks/{$userId}/{$filename}";

        Storage::disk('local')->put($path, file_get_contents($file->getRealPath()));

        return $path;
    }

    /**
     * Crea la traccia e i segmenti dal dato parsato
     */
    protected function createTrackFromParsedData(
        array $parsedData,
        User $user,
        ?int $competitionId,
        string $storedPath,
        string $fileHash
    ): Track {
        return DB::transaction(function () use ($parsedData, $user, $competitionId, $storedPath, $fileHash) {
            $summary = $parsedData['summary'];

            // Crea la traccia
            $track = Track::create([
                'user_id' => $user->id,
                'competition_id' => $competitionId,
                'session_id' => $parsedData['session_id'],
                'status' => TrackStatus::PENDING,
                'started_at' => $summary['started_at'],
                'ended_at' => $summary['ended_at'],
                'duration_seconds' => $summary['duration_seconds'],
                'start_latitude' => $summary['start_latitude'],
                'start_longitude' => $summary['start_longitude'],
                'end_latitude' => $summary['end_latitude'],
                'end_longitude' => $summary['end_longitude'],
                'total_distance_meters' => $summary['total_distance_meters'],
                'valid_distance_meters' => $summary['valid_distance_meters'],
                'is_multimodal' => $summary['is_multimodal'],
                'primary_transport_mode' => $summary['primary_transport_mode'],
                'points_count' => $summary['points_count'],
                'avg_accuracy_meters' => $summary['avg_accuracy_meters'],
                'avg_speed_ms' => $summary['avg_speed_ms'],
                'original_file_path' => $storedPath,
                'original_file_hash' => $fileHash,
            ]);

            // Crea i segmenti
            foreach ($parsedData['segments'] as $segmentData) {
                TrackSegment::create([
                    'track_id' => $track->id,
                    'sequence' => $segmentData['sequence'],
                    'transport_mode' => $segmentData['transport_mode'],
                    'status' => 'pending',
                    'started_at' => $segmentData['started_at'],
                    'ended_at' => $segmentData['ended_at'],
                    'duration_seconds' => $segmentData['duration_seconds'],
                    'start_latitude' => $segmentData['start_latitude'],
                    'start_longitude' => $segmentData['start_longitude'],
                    'end_latitude' => $segmentData['end_latitude'],
                    'end_longitude' => $segmentData['end_longitude'],
                    'distance_meters' => $segmentData['distance_meters'],
                    'points_count' => $segmentData['points_count'],
                    'avg_speed_ms' => $segmentData['avg_speed_ms'],
                    'max_speed_ms' => $segmentData['max_speed_ms'],
                    'max_chunk_speed_kmh' => $segmentData['max_chunk_speed_kmh'] ?? null,
                    'speed_chunks' => $segmentData['speed_chunks'] ?? null,
                    'avg_accuracy_meters' => $segmentData['avg_accuracy_meters'],
                    'generates_credits' => $segmentData['generates_credits'],
                    'polyline' => $segmentData['polyline'],
                ]);
            }

            return $track;
        });
    }

    /**
     * Elimina una traccia e il suo file originale
     */
    public function deleteTrack(Track $track): void
    {
        DB::transaction(function () use ($track) {
            // Elimina il file originale
            if ($track->original_file_path) {
                Storage::disk('local')->delete($track->original_file_path);
            }

            // Elimina la traccia (i segmenti vengono eliminati in cascata)
            $track->delete();
        });
    }
}
