<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Verifica dell'indirizzo email tramite link firmato.
 *
 * A differenza della rotta standard di Fortify, questa NON richiede che
 * l'utente sia autenticato: l'utente viene identificato dall'id contenuto
 * nel link firmato (HMAC + hash dell'email), così il partner può verificare
 * l'email anche aprendo il messaggio da un altro dispositivo o senza aver
 * effettuato il login. Al termine viene sempre riportato al login con un
 * messaggio di conferma, coerentemente con il flusso previsto dal progetto.
 */
class VerifyEmailController extends Controller
{
    public function __invoke(Request $request, string $id, string $hash): RedirectResponse
    {
        $user = User::findOrFail($id);

        // L'hash nel link deve corrispondere all'email corrente dell'utente.
        // La validità del link (firma e scadenza) è già garantita dal
        // middleware "signed".
        if (! hash_equals($hash, sha1($user->getEmailForVerification()))) {
            abort(403, __('Il link di verifica non è valido.'));
        }

        $alreadyVerified = $user->hasVerifiedEmail();

        if (! $alreadyVerified) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        // Se il click avviene da una sessione autenticata, la chiudiamo:
        // la verifica riporta sempre alla pagina di login.
        if (Auth::check()) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        $message = $alreadyVerified
            ? __('Questa email risulta già verificata, accedi con le tue credenziali.')
            : __('Email verificata, accedi con le tue credenziali.');

        return redirect()->route('login')->with('status', $message);
    }
}
