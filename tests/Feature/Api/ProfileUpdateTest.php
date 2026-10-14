<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileUpdateTest extends TestCase
{
    use RefreshDatabase;

    private const BOUNDARY = 'goodgoTestBoundary';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.api.secret_key' => 'test-secret']);
        Storage::fake('public');
    }

    /**
     * Costruisce un body multipart grezzo: PHP non lo parsa per PUT,
     * quindi la request arriva con campi e file vuoti come in produzione.
     */
    private function multipartBody(array $fields, array $files = []): string
    {
        $body = '';

        foreach ($fields as $name => $value) {
            $body .= '--' . self::BOUNDARY . "\r\n"
                . "Content-Disposition: form-data; name=\"{$name}\"\r\n\r\n"
                . $value . "\r\n";
        }

        foreach ($files as $name => [$filename, $mimeType, $content]) {
            $body .= '--' . self::BOUNDARY . "\r\n"
                . "Content-Disposition: form-data; name=\"{$name}\"; filename=\"{$filename}\"\r\n"
                . "Content-Type: {$mimeType}\r\n\r\n"
                . $content . "\r\n";
        }

        return $body . '--' . self::BOUNDARY . "--\r\n";
    }

    private function putMultipart(string $body)
    {
        return $this->call('PUT', '/api/v1/profile', [], [], [], [
            'CONTENT_TYPE' => 'multipart/form-data; boundary=' . self::BOUNDARY,
            'HTTP_SECRET_KEY' => 'test-secret',
            'HTTP_ACCEPT' => 'application/json',
        ], $body);
    }

    private function pngContent(): string
    {
        $chunk = fn(string $type, string $data) => pack('N', strlen($data)) . $type . $data
            . pack('N', crc32($type . $data));

        return "\x89PNG\r\n\x1a\n"
            . $chunk('IHDR', pack('NNCCCCC', 1, 1, 8, 2, 0, 0, 0))
            . $chunk('IDAT', gzcompress("\x00\xff\x00\x00"))
            . $chunk('IEND', '');
    }

    public function test_put_multipart_updates_profile_fields(): void
    {
        $user = User::factory()->create(['name' => 'Mario', 'surname' => 'Rossi']);
        Sanctum::actingAs($user);

        $this->putMultipart($this->multipartBody([
            'first_name' => 'Luigi',
            'last_name' => 'Verdi',
            'tutorial' => 'true',
            'extra_fields[birth_date]' => '1990-01-15',
            'extra_fields[main_location][lat]' => '43.7228',
            'extra_fields[main_location][lng]' => '10.4017',
            'extra_fields[main_location][address]' => 'Via Roma 1, Pisa',
        ]))->assertOk();

        $profile = $this->getJson('/api/v1/profile', ['secret-key' => 'test-secret'])->assertOk();

        $profile->assertJsonPath('first_name', 'Luigi')
            ->assertJsonPath('last_name', 'Verdi')
            ->assertJsonPath('tutorial', true)
            ->assertJsonPath('extra_fields.birth_date', '1990-01-15')
            ->assertJsonPath('extra_fields.main_location.lat', 43.7228)
            ->assertJsonPath('extra_fields.main_location.lng', 10.4017)
            ->assertJsonPath('extra_fields.main_location.address', 'Via Roma 1, Pisa');
    }

    public function test_put_multipart_uploads_profile_image(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $this->putMultipart($this->multipartBody([], [
            'image' => ['avatar.png', 'image/png', $this->pngContent()],
        ]))->assertOk();

        $path = $user->fresh()->profile_photo_path;
        $this->assertNotNull($path);
        Storage::disk('public')->assertExists($path);
    }

    public function test_profile_image_must_be_an_image(): void
    {
        $user = User::factory()->create(['surname' => 'Rossi']);
        Sanctum::actingAs($user);

        $this->putMultipart($this->multipartBody(['last_name' => 'Verdi'], [
            'image' => ['avatar.php', 'image/png', '<?php echo 1;'],
        ]))->assertStatus(422)->assertJsonPath('error', 100);

        $this->assertNull($user->fresh()->profile_photo_path);
        $this->assertSame('Rossi', $user->fresh()->surname);
    }

    public function test_put_json_still_updates_profile(): void
    {
        $user = User::factory()->create(['surname' => 'Rossi']);
        Sanctum::actingAs($user);

        $this->putJson('/api/v1/profile', ['last_name' => 'Bianchi'], ['secret-key' => 'test-secret'])
            ->assertOk();

        $this->assertSame('Bianchi', $user->fresh()->surname);
    }
}
