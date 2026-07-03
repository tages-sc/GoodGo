<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\URL;
use Laravel\Fortify\Features;
use Tests\TestCase;

class EmailVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_email_verification_screen_can_be_rendered(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->unverified()->create();

        $response = $this->actingAs($user)->get('/email/verify');

        $response->assertStatus(200);
    }

    public function test_email_can_be_verified(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        Event::fake();

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        // Il link è utilizzabile anche senza autenticazione (VerifyEmailController custom).
        $response = $this->get($verificationUrl);

        Event::assertDispatched(Verified::class);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Email verificata! Ora puoi accedere con le tue credenziali.');
    }

    public function test_verifying_from_an_authenticated_session_logs_the_user_out(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->actingAs($user)->get($verificationUrl);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('login', absolute: false));
        $this->assertGuest();
    }

    public function test_already_verified_link_shows_positive_message_and_no_error(): void
    {
        // Simula il caso reale del prefetch (scanner antivirus della posta /
        // anteprima messaggi) o del secondo click: l'email è già verificata,
        // l'utente NON deve vedere un fuorviante "già verificata" ma lo stesso
        // messaggio positivo del primo click.
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        Event::fake();

        $user = User::factory()->create(); // già verificato (default factory)

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verificationUrl);

        // Nessun evento Verified: era già verificato.
        Event::assertNotDispatched(Verified::class);

        $this->assertTrue($user->fresh()->hasVerifiedEmail());
        $response->assertRedirect(route('login', absolute: false));
        $response->assertSessionHas('status', 'Email verificata! Ora puoi accedere con le tue credenziali.');
    }

    public function test_verification_email_is_italian_and_branded(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->make(['name' => 'Mario']);
        $user->id = 999;

        $mail = (new \Illuminate\Auth\Notifications\VerifyEmail())->toMail($user);

        $this->assertSame('Conferma il tuo indirizzo email · GoodGo', $mail->subject);
        $this->assertSame('Ciao Mario!', $mail->greeting);
        $this->assertSame('Conferma la mia email', $mail->actionText);
        $this->assertSame('Il team di GoodGo', $mail->salutation);
    }

    public function test_email_can_not_verified_with_invalid_hash(): void
    {
        if (! Features::enabled(Features::emailVerification())) {
            $this->markTestSkipped('Email verification not enabled.');
        }

        $user = User::factory()->unverified()->create();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes(60),
            ['id' => $user->id, 'hash' => sha1('wrong-email')]
        );

        $response = $this->get($verificationUrl);

        $response->assertForbidden();
        $this->assertFalse($user->fresh()->hasVerifiedEmail());
    }
}
