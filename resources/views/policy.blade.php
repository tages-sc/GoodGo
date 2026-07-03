<x-guest-layout>
    <div class="pt-4 bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            @php
                $privacyPolicy = \App\Models\PolicyVersion::getLatestPrivacyPolicy();
            @endphp

            <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                @if($privacyPolicy)
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $privacyPolicy->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Versione {{ $privacyPolicy->version }} - Pubblicata il {{ local_dt($privacyPolicy->published_at, 'd/m/Y') }}</p>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($privacyPolicy->content)) !!}
                    </div>
                @else
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <p>Nessuna Privacy Policy pubblicata.</p>
                        <p class="text-sm mt-2">Contattare l'amministratore.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>
