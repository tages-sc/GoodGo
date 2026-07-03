<div>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- Header con info utente --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                        <div class="flex items-center">
                            <img class="h-16 w-16 rounded-full object-cover" src="{{ $user->profile_photo_url }}" alt="{{ $user->name }}">
                            <div class="ml-4">
                                <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                    {{ $user->name }}
                                </h2>
                                <p class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $user->email }}
                                </p>
                                <div class="flex items-center mt-2 space-x-2">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full {{ $user->type->badgeClasses() }}">
                                        {{ $user->type->label() }}
                                    </span>
                                    @if($user->email_verified_at)
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                                            Email verificata
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                                            Email non verificata
                                        </span>
                                    @endif
                                    @if($user->platform)
                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200">
                                            {{ ucfirst($user->platform) }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="mt-4 md:mt-0 flex space-x-2">
                            <button wire:click="exportUserData" class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Esporta Dati
                            </button>
                            <a href="{{ route('admin.users.edit', $user) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-indigo-700">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                Modifica
                            </a>
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-200 dark:bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest hover:bg-gray-300 dark:hover:bg-gray-500">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Torna alla lista
                            </a>
                        </div>
                    </div>

                    {{-- Stats rapide --}}
                    @if($user->type === \App\Enums\UserType::USER)
                        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                                    {{ number_format($user->credits ?? 0, 2, ',', '.') }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Crediti</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-green-600 dark:text-green-400">
                                    {{ $user->competitions()->count() }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Gare</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-blue-600 dark:text-blue-400">
                                    {{ $user->tracks()->count() }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Tracce</div>
                            </div>
                            <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                                <div class="text-2xl font-bold text-purple-600 dark:text-purple-400">
                                    {{ $user->enti()->count() }}
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 uppercase">Enti</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="border-b border-gray-200 dark:border-gray-700">
                    <nav class="flex -mb-px overflow-x-auto" aria-label="Tabs">
                        <button wire:click="setTab('info')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'info' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                            Informazioni
                        </button>
                        @if($user->type === \App\Enums\UserType::USER)
                            <button wire:click="setTab('competitions')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'competitions' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Gare
                            </button>
                            <button wire:click="setTab('enti')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'enti' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Enti
                            </button>
                            <button wire:click="setTab('tracks')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'tracks' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Tracce
                            </button>
                            <button wire:click="setTab('movements')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'movements' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Movimenti
                            </button>
                            <button wire:click="setTab('spese')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'spese' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Spese
                            </button>
                            <button wire:click="setTab('credits')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'credits' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Log Crediti
                            </button>
                            <button wire:click="setTab('badges')" class="whitespace-nowrap py-4 px-6 border-b-2 font-medium text-sm {{ $activeTab === 'badges' ? 'border-indigo-500 text-indigo-600 dark:text-indigo-400' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 dark:text-gray-400 dark:hover:text-gray-300' }}">
                                Badge
                            </button>
                        @endif
                    </nav>
                </div>

                <div class="p-6">
                    {{-- Tab Info --}}
                    @if($activeTab === 'info')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            {{-- Dati Base --}}
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Dati Utente</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">ID</dt>
                                        <dd class="text-sm font-mono text-gray-900 dark:text-gray-100">{{ $user->id }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Nome</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->name }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->email }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Tipo</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->type->label() }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Piattaforma</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->platform ? ucfirst($user->platform) : '-' }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Registrato il</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ local_dt($user->created_at, 'd/m/Y H:i') }}</dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Email verificata</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ local_dt($user->email_verified_at, 'd/m/Y H:i') ?: 'No' }}</dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Privacy/Terms --}}
                            <div class="space-y-4">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Privacy & Termini</h4>
                                <dl class="space-y-2">
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Privacy accettata</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                                            @if($user->privacy_accepted_at)
                                                {{ local_dt($user->privacy_accepted_at, 'd/m/Y H:i') }}
                                                @if($user->privacy_version_id)
                                                    <span class="text-xs text-gray-500">(v{{ $user->privacy_version_id }})</span>
                                                @endif
                                            @else
                                                <span class="text-red-500">No</span>
                                            @endif
                                        </dd>
                                    </div>
                                    <div class="flex justify-between">
                                        <dt class="text-sm text-gray-500 dark:text-gray-400">Termini accettati</dt>
                                        <dd class="text-sm text-gray-900 dark:text-gray-100">
                                            @if($user->terms_accepted_at)
                                                {{ local_dt($user->terms_accepted_at, 'd/m/Y H:i') }}
                                                @if($user->terms_version_id)
                                                    <span class="text-xs text-gray-500">(v{{ $user->terms_version_id }})</span>
                                                @endif
                                            @else
                                                <span class="text-red-500">No</span>
                                            @endif
                                        </dd>
                                    </div>
                                </dl>
                            </div>

                            {{-- Profilo Utente (se esiste) --}}
                            @if($user->profile)
                                <div class="space-y-4 md:col-span-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Profilo</h4>
                                    <dl class="grid grid-cols-2 gap-4">
                                        @if($user->profile->username)
                                            <div>
                                                <dt class="text-sm text-gray-500 dark:text-gray-400">Username</dt>
                                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->profile->username }}</dd>
                                            </div>
                                        @endif
                                        @if($user->profile->birth_date)
                                            <div>
                                                <dt class="text-sm text-gray-500 dark:text-gray-400">Data di nascita</dt>
                                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->profile->birth_date->format('d/m/Y') }}</dd>
                                            </div>
                                        @endif
                                        @if($user->profile->address)
                                            <div class="col-span-2">
                                                <dt class="text-sm text-gray-500 dark:text-gray-400">Indirizzo</dt>
                                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->profile->address }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            @endif

                            {{-- Ente Profile (per ente/organizer) --}}
                            @if($user->enteProfile)
                                <div class="space-y-4 md:col-span-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Profilo Ente</h4>
                                    <dl class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Tipologia</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->enteProfile->tipologia ? ucfirst($user->enteProfile->tipologia) : '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Location</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->enteProfile->location ?? '-' }}</dd>
                                        </div>
                                        @if($user->enteProfile->descrizione)
                                            <div class="col-span-2">
                                                <dt class="text-sm text-gray-500 dark:text-gray-400">Descrizione</dt>
                                                <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->enteProfile->descrizione }}</dd>
                                            </div>
                                        @endif
                                    </dl>
                                </div>
                            @endif

                            {{-- Partner Profile (per partner) --}}
                            @if($user->partnerProfile)
                                <div class="space-y-4 md:col-span-2">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Profilo Partner</h4>
                                    <dl class="grid grid-cols-2 gap-4">
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Azienda</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->partnerProfile->company_name ?? '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">P.IVA</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->partnerProfile->vat_number ?? '-' }}</dd>
                                        </div>
                                        <div>
                                            <dt class="text-sm text-gray-500 dark:text-gray-400">Email PEC</dt>
                                            <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $user->partnerProfile->pec_email ?? '-' }}</dd>
                                        </div>
                                    </dl>
                                </div>
                            @endif

                            {{-- Ente Padre (per organizer) --}}
                            @if($user->parentEnte)
                                <div class="space-y-4">
                                    <h4 class="text-lg font-medium text-gray-900 dark:text-gray-100 border-b dark:border-gray-700 pb-2">Ente Padre</h4>
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-lg flex items-center justify-center text-white font-bold" style="background-color: {{ $user->parentEnte->enteProfile?->colore ?? '#4CAF50' }}">
                                            {{ strtoupper(substr($user->parentEnte->name, 0, 2)) }}
                                        </div>
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $user->parentEnte->name }}</div>
                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $user->parentEnte->email }}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endif

                    {{-- Tab Gare --}}
                    @if($activeTab === 'competitions' && isset($competitions))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Gara</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Km</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Rank</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Iscritto il</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($competitions as $competition)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $competition->name }}</div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $competition->ente?->name }}</div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($competition->pivot->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                    @elseif($competition->pivot->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                    @endif">
                                                    {{ ucfirst($competition->pivot->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($competition->pivot->total_credits ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($competition->pivot->total_distance_km ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-center text-sm text-gray-900 dark:text-gray-100">
                                                {{ $competition->pivot->rank ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($competition->pivot->registered_at, 'd/m/Y H:i') ?: '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessuna gara trovata.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $competitions->links() }}</div>
                    @endif

                    {{-- Tab Enti --}}
                    @if($activeTab === 'enti' && isset($enti))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Ente</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Richiesto il</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Processato il</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($enti as $ente)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-8 w-8 rounded-lg flex items-center justify-center text-white text-xs font-bold" style="background-color: {{ $ente->enteProfile?->colore ?? '#4CAF50' }}">
                                                        {{ strtoupper(substr($ente->name, 0, 2)) }}
                                                    </div>
                                                    <div class="ml-3">
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $ente->name }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full
                                                    @if($ente->pivot->status === 'approved') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300
                                                    @elseif($ente->pivot->status === 'pending') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300
                                                    @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300
                                                    @endif">
                                                    {{ ucfirst($ente->pivot->status) }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($ente->pivot->requested_at, 'd/m/Y H:i') ?: '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($ente->pivot->processed_at, 'd/m/Y H:i') ?: '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessun ente trovato.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $enti->links() }}</div>
                    @endif

                    {{-- Tab Tracce --}}
                    @if($activeTab === 'tracks' && isset($tracks))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Gara</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Distanza</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Azioni</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($tracks as $track)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $track->id }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $track->competition?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $track->status->badgeClasses() }}">
                                                    {{ $track->status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($track->total_distance_km ?? 0, 2, ',', '.') }} km
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($track->credits_earned ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($track->created_at, 'd/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-right">
                                                <a href="{{ route('admin.tracks.show', $track) }}" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                                    </svg>
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessuna traccia trovata.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $tracks->links() }}</div>
                    @endif

                    {{-- Tab Movimenti --}}
                    @if($activeTab === 'movements' && isset($movements))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">ID</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Partner</th>
                                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stato</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Euro</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($movements as $movement)
                                        <tr>
                                            <td class="px-4 py-3 text-sm font-mono text-gray-900 dark:text-gray-100">{{ $movement->id }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movement->type->label() }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movement->partner?->name ?? '-' }}</td>
                                            <td class="px-4 py-3 text-center">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $movement->status->badgeClasses() }}">
                                                    {{ $movement->status->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($movement->credits ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($movement->euro_amount ?? 0, 2, ',', '.') }} €
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($movement->created_at, 'd/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessun movimento trovato.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $movements->links() }}</div>
                    @endif

                    {{-- Tab Spese --}}
                    @if($activeTab === 'spese' && isset($spese))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Partner</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Gara</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrizione</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Crediti</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">EUR</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Approvato da</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($spese as $spesa)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $spesa->partner?->partnerProfile?->company_name ?? $spesa->partner?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $spesa->competition?->name ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100 max-w-xs truncate">
                                                {{ $spesa->description }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-bold text-red-600 dark:text-red-400">
                                                {{ $spesa->formatted_credits }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                                {{ $spesa->formatted_euro }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($spesa->processed_at, 'd/m/Y H:i') ?: local_dt($spesa->created_at, 'd/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $spesa->processor?->name ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessuna spesa trovata.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $spese->links() }}</div>
                    @endif

                    {{-- Tab Log Crediti --}}
                    @if($activeTab === 'credits' && isset($creditLogs))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Tipo</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Importo</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Saldo Prima</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Saldo Dopo</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descrizione</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse($creditLogs as $log)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($log->created_at, 'd/m/Y H:i') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                {{ $log->type->label() }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm font-medium {{ $log->amount >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                                {{ $log->amount >= 0 ? '+' : '' }}{{ number_format($log->amount, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-500 dark:text-gray-400">
                                                {{ number_format($log->balance_before ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-right text-sm text-gray-900 dark:text-gray-100">
                                                {{ number_format($log->balance_after ?? 0, 2, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ $log->description ?? '-' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessun log crediti trovato.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $creditLogs->links() }}</div>
                    @endif

                    {{-- Tab Badge --}}
                    @if($activeTab === 'badges' && isset($userBadges))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead class="bg-gray-50 dark:bg-gray-700">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Badge</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Categoria</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stelle</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Data Ottenimento</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                    @forelse ($userBadges as $badge)
                                        <tr>
                                            <td class="px-4 py-3">
                                                <div class="flex items-center">
                                                    @if ($badge->icon)
                                                        <img src="{{ Storage::url($badge->icon) }}" class="w-8 h-8 rounded mr-3" alt="{{ $badge->name }}">
                                                    @else
                                                        <div class="w-8 h-8 rounded bg-indigo-100 dark:bg-indigo-900 flex items-center justify-center mr-3">
                                                            <svg class="w-5 h-5 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                                            </svg>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $badge->display_name }}</div>
                                                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ Str::limit($badge->description, 60) }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $badge->category->badgeClasses() }}">
                                                    {{ $badge->category->label() }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                                @if ($badge->stars > 0)
                                                    <span class="text-yellow-500">{{ str_repeat('★', $badge->stars) }}</span>
                                                @else
                                                    -
                                                @endif
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400">
                                                {{ local_dt($badge->pivot->earned_at, 'd/m/Y H:i') }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                                Nessun badge ottenuto.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-4">{{ $userBadges->links() }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
