<x-guest-layout>
    <div class="pt-4 bg-gray-100 dark:bg-gray-900">
        <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-0">
            <div>
                <x-authentication-card-logo />
            </div>

            @php
                $termsPolicy = \App\Models\PolicyVersion::getLatestTerms();
            @endphp

            <div class="w-full sm:max-w-2xl mt-6 p-6 bg-white dark:bg-gray-800 shadow-md overflow-hidden sm:rounded-lg">
                @if($termsPolicy)
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-4">{{ $termsPolicy->title }}</h1>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Versione {{ $termsPolicy->version }} - Pubblicata il {{ $termsPolicy->published_at?->format('d/m/Y') }}</p>
                    <div class="prose dark:prose-invert max-w-none">
                        {!! nl2br(e($termsPolicy->content)) !!}
                    </div>
                @else
                    <div class="text-center text-gray-500 dark:text-gray-400">
                        <p>Nessun Terms of Service pubblicato.</p>
                        <p class="text-sm mt-2">Contattare l'amministratore.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-guest-layout>
