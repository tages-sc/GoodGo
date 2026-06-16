<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Occupation;
use App\Models\PolicyVersion;
use Illuminate\Http\JsonResponse;

class ConfigurationController extends ApiController
{
    /**
     * GET api/v1/configuration
     *
     * Ritorna i dati di configurazione:
     * - Lista dei lavori consentiti
     * - URL privacy policy
     * - URL termini e condizioni
     */
    public function __invoke(): JsonResponse
    {
        $occupations = Occupation::active()->ordered()->get(['id', 'name']);

        $privacyUrl = '';
        $termsUrl = '';

        $latestPrivacy = PolicyVersion::getLatestPrivacyPolicy();
        $latestTerms = PolicyVersion::getLatestTerms();

        if ($latestPrivacy) {
            $privacyUrl = route('policy.show');
        }

        if ($latestTerms) {
            $termsUrl = route('terms.show');
        }

        return $this->success([
            'occupations_list' => $occupations->map(fn($o) => [
                'id' => $o->id,
                'name' => $o->name,
            ])->values(),
            'privacy_url' => $privacyUrl,
            'terms_url' => $termsUrl,
        ]);
    }
}
