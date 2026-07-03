<?php

use App\Http\Controllers\Auth\VerifyEmailController;
use App\Livewire\Auth\RegisterPartner;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Partner Registration
Route::get('/register/partner', RegisterPartner::class)
    ->middleware('guest')
    ->name('register.partner');

// Verifica email tramite link firmato (non richiede autenticazione).
// Sovrascrive la rotta di default di Fortify che richiederebbe il middleware
// "auth", impedendo la verifica quando il link viene aperto senza login.
Route::get('/email/verify/{id}/{hash}', VerifyEmailController::class)
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Admin Routes (Super Admin only)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'user.type:super_admin',
])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/policy-versions', function () {
        return view('admin.policy-versions');
    })->name('policy-versions');

    Route::get('/enti', function () {
        return view('admin.enti');
    })->name('enti');

    Route::get('/enti/{ente}/members', function (User $ente) {
        return view('admin.ente-members', ['ente' => $ente]);
    })->name('enti.members');

    Route::get('/competitions', function () {
        return view('admin.competitions');
    })->name('competitions');

    Route::get('/competitions/{competition}', function (\App\Models\Competition $competition) {
        return view('admin.competition-show', ['competition' => $competition]);
    })->name('competitions.show');

    Route::get('/organizers', function () {
        return view('admin.organizers');
    })->name('organizers');

    Route::get('/tracks', function () {
        return view('admin.tracks');
    })->name('tracks.index');

    Route::get('/tracks/{track}', function (\App\Models\Track $track) {
        return view('admin.track-show', ['track' => $track]);
    })->name('tracks.show');

    Route::get('/movements', function () {
        return view('admin.movements');
    })->name('movements');

    // Gestione Utenti
    Route::get('/users', function () {
        return view('admin.users');
    })->name('users.index');

    Route::get('/users/{user}', function (User $user) {
        return view('admin.user-show', ['user' => $user]);
    })->name('users.show');

    Route::get('/users/{user}/edit', function (User $user) {
        return view('admin.user-edit', ['user' => $user]);
    })->name('users.edit');

    Route::get('/spese', function () {
        return view('admin.spese');
    })->name('spese');

    Route::get('/badges', function () {
        return view('admin.badges');
    })->name('badges');

    Route::get('/export', function () {
        return view('admin.export');
    })->name('export');

    Route::get('/invitation-codes', function () {
        return view('admin.invitation-codes');
    })->name('invitation-codes');

    Route::get('/occupations', function () {
        return view('admin.occupations');
    })->name('occupations');
});

// Ente Routes (Ente only)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'user.type:ente',
])->prefix('ente')->name('ente.')->group(function () {
    Route::get('/organizers', function () {
        return view('ente.organizers');
    })->name('organizers');

    Route::get('/members', function () {
        return view('ente.members');
    })->name('members');

    Route::get('/competitions', function () {
        return view('ente.competitions');
    })->name('competitions');

    Route::get('/competitions/{competition}/participants', function (\App\Models\Competition $competition) {
        return view('ente.competition-participants', ['competition' => $competition]);
    })->name('competitions.participants');

    Route::get('/competitions/{competition}/tracks', function (\App\Models\Competition $competition) {
        return view('ente.competition-tracks', ['competition' => $competition]);
    })->name('competitions.tracks');

    Route::get('/competitions/{competition}/tracks/{track}', function (\App\Models\Competition $competition, \App\Models\Track $track) {
        return view('ente.competition-track-show', ['competition' => $competition, 'track' => $track]);
    })->name('competitions.tracks.show');

    Route::get('/competitions/{competition}', function (\App\Models\Competition $competition) {
        return view('ente.competition-show', ['competition' => $competition]);
    })->name('competitions.show');

    Route::get('/movements', function () {
        return view('ente.movements');
    })->name('movements');

    Route::get('/spese', function () {
        return view('ente.spese');
    })->name('spese');

    Route::get('/invitation-codes', function () {
        return view('ente.invitation-codes');
    })->name('invitation-codes');
});

// Organizer Routes (Organizer only)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'user.type:organizer',
])->prefix('organizer')->name('organizer.')->group(function () {
    Route::get('/competitions', function () {
        return view('organizer.competitions');
    })->name('competitions');

    Route::get('/competitions/{competition}/participants', function (\App\Models\Competition $competition) {
        return view('organizer.competition-participants', ['competition' => $competition]);
    })->name('competitions.participants');

    Route::get('/competitions/{competition}/tracks', function (\App\Models\Competition $competition) {
        return view('organizer.competition-tracks', ['competition' => $competition]);
    })->name('competitions.tracks');

    Route::get('/competitions/{competition}/tracks/{track}', function (\App\Models\Competition $competition, \App\Models\Track $track) {
        return view('organizer.competition-track-show', ['competition' => $competition, 'track' => $track]);
    })->name('competitions.tracks.show');

    Route::get('/competitions/{competition}', function (\App\Models\Competition $competition) {
        return view('organizer.competition-show', ['competition' => $competition]);
    })->name('competitions.show');

    Route::get('/movements', function () {
        return view('organizer.movements');
    })->name('movements');

    Route::get('/spese', function () {
        return view('organizer.spese');
    })->name('spese');
});

// User Routes (Generic User only)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'user.type:user',
])->prefix('user')->name('user.')->group(function () {
    Route::get('/competitions', function () {
        return view('user.competitions');
    })->name('competitions.index');

    Route::get('/competitions/{competition}', function (\App\Models\Competition $competition) {
        return view('user.competition-show', ['competition' => $competition]);
    })->name('competitions.show');

    Route::get('/tracks', function () {
        return view('user.tracks');
    })->name('tracks.index');

    Route::get('/tracks/{track}', function (\App\Models\Track $track) {
        return view('user.track-show', ['track' => $track]);
    })->name('tracks.show');

    Route::get('/movements', function () {
        return view('user.movements');
    })->name('movements');

    Route::get('/spese', function () {
        return view('user.spese');
    })->name('spese');

    Route::get('/credits', function () {
        return view('user.credits');
    })->name('credits');

    Route::get('/badges', function () {
        return view('user.badges');
    })->name('badges');
});

// Partner Routes (Partner only)
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
    'user.type:partner',
    'partner.profile.complete',
])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/competitions', function () {
        return view('partner.competitions');
    })->name('competitions');

    Route::get('/movements', function () {
        return view('partner.movements');
    })->name('movements');

    Route::get('/spese', function () {
        return view('partner.spese');
    })->name('spese');
});
