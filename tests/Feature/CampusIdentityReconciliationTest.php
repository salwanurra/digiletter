<?php

namespace Tests\Feature;

use App\Models\CampusIdentityVerification;
use App\Models\User;
use App\Services\CampusIdentityReconciliationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CampusIdentityReconciliationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_saves_program_study_from_campus_api(): void
    {
        Notification::fake();
        config([
            'services.kampus.base_url' => 'https://kampus.example.test',
            'services.kampus.token' => 'test-token',
        ]);
        Http::fake([
            'https://kampus.example.test/mahasiswa/nim/2210631170008' => Http::response([
                'data' => [
                    'nim' => '2210631170008',
                    'nama_mahasiswa' => 'RINA MAHARANI',
                    'email' => '2210631170008@student.unsika.ac.id',
                    'nama_program_studi' => 'Teknik Informatika',
                ],
            ]),
        ]);

        $this->post(route('post_regist'), [
            'npm' => '2210631170008',
            'password' => 'password',
            'prodi' => 'Sistem Informasi',
        ])->assertRedirect(route('verification.notice'));

        $user = User::where('npm', '2210631170008')->firstOrFail();

        $this->assertSame('Informatika', $user->mahasiswa->prodi);
        $this->assertSame('Rina Maharani', $user->name);
    }

    public function test_matching_npm_is_verified_automatically_and_official_name_is_applied(): void
    {
        $user = User::factory()->create([
            'name' => 'Budi Sntoso',
            'npm' => 2210631170001,
            'email' => '2210631170001@student.unsika.ac.id',
        ]);

        $verification = app(CampusIdentityReconciliationService::class)->submit($user, [
            'nim' => '2210631170001',
            'nama_mahasiswa' => 'BUDI SANTOSO',
            'email' => '2210631170001@student.unsika.ac.id',
        ]);

        $this->assertSame(CampusIdentityVerification::STATUS_VERIFIED, $verification->status);
        $this->assertSame('Budi Santoso', $user->fresh()->name);
        $this->assertSame('2210631170001', (string) $user->fresh()->mahasiswa->npm);
    }

    public function test_different_email_is_replaced_with_campus_email_and_needs_reverification(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 2210631170002,
            'email' => 'email-lama@example.test',
        ]);

        $verification = app(CampusIdentityReconciliationService::class)->submit($user, [
            'nim' => '2210631170002',
            'nama_mahasiswa' => 'SITI AMINAH',
            'email' => '2210631170002@student.unsika.ac.id',
        ]);

        $user->refresh();
        $this->assertSame(CampusIdentityVerification::STATUS_VERIFIED, $verification->status);
        $this->assertSame('2210631170002@student.unsika.ac.id', $user->email);
        $this->assertSame('email-lama@example.test', $verification->old_email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_different_npm_is_replaced_with_campus_npm(): void
    {
        Notification::fake();
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 9999999999999,
            'email' => 'legacy@example.test',
        ]);

        app(CampusIdentityReconciliationService::class)->submit($user, [
            'nim' => '2210631170002',
            'nama_mahasiswa' => 'SITI AMINAH',
            'email' => '2210631170002@student.unsika.ac.id',
        ]);

        $user->refresh();
        $this->assertSame('2210631170002', (string) $user->npm);
        $this->assertSame('2210631170002@student.unsika.ac.id', $user->email);
        $this->assertSame('2210631170002', (string) $user->mahasiswa->npm);
    }

    public function test_identity_already_claimed_by_another_account_is_rejected(): void
    {
        User::factory()->create(['npm' => 2210631170002, 'email' => '2210631170002@student.unsika.ac.id']);
        $user = User::factory()->create(['npm' => 9999999999999, 'email' => 'legacy@example.test']);

        $this->expectException(\DomainException::class);

        app(CampusIdentityReconciliationService::class)->submit($user, [
            'nim' => '2210631170002',
            'nama_mahasiswa' => 'SITI AMINAH',
            'email' => '2210631170002@student.unsika.ac.id',
        ]);
    }

    public function test_admin_approval_applies_official_npm_and_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 9999999999999,
            'email' => 'legacy@example.test',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $verification = CampusIdentityVerification::create([
            'user_id' => $user->id,
            'npm' => '2210631170002',
            'status' => CampusIdentityVerification::STATUS_PENDING,
            'old_name' => $user->name,
            'old_npm' => (string) $user->npm,
            'old_email' => $user->email,
            'official_name' => 'Siti Aminah',
            'official_email' => '2210631170002@student.unsika.ac.id',
            'campus_payload' => ['nim' => '2210631170002'],
        ]);

        app(CampusIdentityReconciliationService::class)->approve($verification, $admin, 'OK');

        $user->refresh();
        $this->assertSame('2210631170002', (string) $user->npm);
        $this->assertSame('2210631170002@student.unsika.ac.id', $user->email);
        $this->assertSame(CampusIdentityVerification::STATUS_VERIFIED, $verification->fresh()->status);
    }

    public function test_user_can_submit_corrected_npm_and_email_follows_api(): void
    {
        Notification::fake();
        config([
            'services.kampus.base_url' => 'https://kampus.example.test',
            'services.kampus.token' => 'test-token',
        ]);
        Http::fake([
            'https://kampus.example.test/mahasiswa/nim/2210631170005' => Http::response([
                'data' => [
                    'nim' => '2210631170005',
                    'nama_mahasiswa' => 'DEWI LESTARI',
                    'email' => '2210631170005@student.unsika.ac.id',
                ],
            ]),
        ]);
        $user = User::factory()->create(['npm' => 1111111111111, 'email' => 'lama@example.test']);

        $this->actingAs($user)
            ->post(route('campus-identity.submit'), ['npm' => '2210631170005', 'password' => 'password'])
            ->assertRedirect(route('verification.notice'));

        $user->refresh();
        $this->assertSame('2210631170005', (string) $user->npm);
        $this->assertSame('2210631170005@student.unsika.ac.id', $user->email);
        $this->assertSame('Dewi Lestari', $user->name);
    }

    public function test_email_from_the_form_is_ignored_and_api_email_is_used(): void
    {
        config([
            'services.kampus.base_url' => 'https://kampus.example.test',
            'services.kampus.token' => 'test-token',
        ]);
        Http::fake([
            'https://kampus.example.test/mahasiswa/nim/2210631170001' => Http::response([
                'data' => [
                    'nim' => '2210631170001',
                    'nama_mahasiswa' => 'BUDI SANTOSO',
                    'email' => '2210631170001@student.unsika.ac.id',
                ],
            ]),
        ]);
        $user = User::factory()->create([
            'name' => 'Budi Sntoso',
            'npm' => 2210631170001,
            'email' => '2210631170001@student.unsika.ac.id',
        ]);

        $this->actingAs($user)
            ->post(route('campus-identity.submit'), [
                'npm' => '2210631170001',
                'email' => 'penyerang@example.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('home'));

        $this->assertSame('2210631170001', (string) $user->fresh()->npm);
        $this->assertSame('2210631170001@student.unsika.ac.id', $user->fresh()->email);
    }

    public function test_lookup_returns_campus_email_for_preview(): void
    {
        config([
            'services.kampus.base_url' => 'https://kampus.example.test',
            'services.kampus.token' => 'test-token',
        ]);
        Http::fake([
            'https://kampus.example.test/mahasiswa/nim/2210631170005' => Http::response([
                'data' => [
                    'nim' => '2210631170005',
                    'nama_mahasiswa' => 'DEWI LESTARI',
                    'email' => 'dewi@student.unsika.ac.id',
                ],
            ]),
        ]);
        $user = User::factory()->create(['npm' => 1111111111111, 'email' => 'lama@example.test']);

        $this->actingAs($user)
            ->getJson(route('campus-identity.lookup', ['npm' => '2210631170005']))
            ->assertOk()
            ->assertJson(['found' => true, 'email' => 'dewi@student.unsika.ac.id', 'name' => 'Dewi Lestari']);
    }
}
