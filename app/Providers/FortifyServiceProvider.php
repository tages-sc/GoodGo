<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\RedirectIfTwoFactorAuthenticatable;
use Laravel\Fortify\Contracts\VerifyEmailResponse;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->instance(VerifyEmailResponse::class, new class implements VerifyEmailResponse
        {
            public function toResponse($request)
            {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->with('status', __('Email verificata, accedi con le tue credenziali.'));
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);
        Fortify::redirectUserForTwoFactorAuthenticationUsing(RedirectIfTwoFactorAuthenticatable::class);

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            return Limit::perMinute(5)->by($throttleKey);
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        $this->customizeVerificationEmail();
    }

    /**
     * Personalizza l'email di verifica: testo in italiano con tono GoodGo.
     *
     * Il link firmato ($url) e la scadenza sono generati automaticamente dalla
     * notifica base; qui personalizziamo solo oggetto e contenuto.
     */
    private function customizeVerificationEmail(): void
    {
        VerifyEmail::toMailUsing(function (object $notifiable, string $url): MailMessage {
            $expireMinutes = (int) config('auth.verification.expire', 60);

            $name = trim((string) ($notifiable->name ?? ''));
            $greeting = $name !== '' ? "Ciao {$name}!" : 'Ciao!';

            return (new MailMessage)
                ->subject('Conferma il tuo indirizzo email · GoodGo')
                ->greeting($greeting)
                ->line('Grazie per esserti registrato su GoodGo, la piattaforma che premia la mobilità sostenibile.')
                ->line('Manca solo un passaggio: conferma il tuo indirizzo email per completare la registrazione e iniziare a guadagnare crediti con i tuoi spostamenti.')
                ->action('Conferma la mia email', $url)
                ->line("Per la tua sicurezza, il link è valido per {$expireMinutes} minuti.")
                ->line('Se questa email è finita nella cartella Spam o Posta indesiderata, segnala GoodGo come mittente attendibile: così riceverai regolarmente anche le prossime comunicazioni.')
                ->line('Se non sei stato tu a registrarti su GoodGo, ignora pure questa email.')
                ->salutation('Il team di GoodGo');
        });
    }
}
