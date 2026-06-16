<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeocodingService
{
    /**
     * Forward geocoding via OpenStreetMap Nominatim.
     *
     * Compone l'indirizzo dai pezzi forniti, lo invia a Nominatim e ritorna
     * un array ['latitude' => float, 'longitude' => float] oppure null se
     * la chiamata fallisce o non trova risultati.
     *
     * Le risposte sono cachate per 30 giorni per ridurre il numero di chiamate
     * (Nominatim ha rate limit di 1 req/s e fair use policy).
     */
    public function forward(
        ?string $address,
        ?string $city = null,
        ?string $province = null,
        ?string $postalCode = null,
        string $country = 'Italia'
    ): ?array {
        $query = $this->buildQuery($address, $city, $province, $postalCode, $country);

        if ($query === null) {
            return null;
        }

        $cacheKey = 'geocode:fwd:' . md5($query);

        return Cache::remember($cacheKey, now()->addDays(30), function () use ($query) {
            return $this->callNominatim($query);
        });
    }

    /**
     * Compone una stringa di ricerca dai campi indirizzo.
     * Ritorna null se non c'e materiale sufficiente per una ricerca sensata.
     */
    protected function buildQuery(
        ?string $address,
        ?string $city,
        ?string $province,
        ?string $postalCode,
        string $country
    ): ?string {
        $parts = array_filter([
            trim((string) $address),
            trim((string) $postalCode),
            trim((string) $city),
            trim((string) $province),
            $country,
        ], fn($p) => $p !== '');

        // Servono almeno indirizzo o (citta + qualcosa) per geocodificare
        if (empty(trim((string) $address)) && empty(trim((string) $city))) {
            return null;
        }

        return implode(', ', $parts);
    }

    /**
     * Chiama Nominatim con il query di ricerca.
     */
    protected function callNominatim(string $query): ?array
    {
        try {
            $response = Http::timeout(8)
                ->withHeaders([
                    'User-Agent' => 'GoodGo/1.0 (+https://goodgo.it)',
                    'Accept-Language' => 'it',
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $query,
                    'format' => 'json',
                    'limit' => 1,
                    'addressdetails' => 0,
                ]);

            if (!$response->successful()) {
                Log::warning('Nominatim forward geocoding HTTP error', [
                    'status' => $response->status(),
                    'query' => $query,
                ]);
                return null;
            }

            $data = $response->json();
            if (!is_array($data) || empty($data)) {
                return null;
            }

            $hit = $data[0];
            if (!isset($hit['lat'], $hit['lon'])) {
                return null;
            }

            return [
                'latitude' => round((float) $hit['lat'], 7),
                'longitude' => round((float) $hit['lon'], 7),
            ];
        } catch (\Throwable $e) {
            Log::warning('Forward geocoding failed: ' . $e->getMessage(), [
                'query' => $query,
            ]);
            return null;
        }
    }
}
