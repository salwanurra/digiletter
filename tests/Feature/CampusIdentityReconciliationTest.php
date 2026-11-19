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

    public function test_different_npm_is_rejected_without_creating_a_review_request(): void
    {
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 9999999999999,
            'email' => 'legacy@example.test',
        ]);

        try {
            app(CampusIdentityReconciliationService::class)->submit($user, [
                'nim' => '2210631170002',
                'nama_mahasiswa' => 'SITI AMINAH',
                'email' => '2210631170002@student.unsika.ac.id',
            ]);

            $this->fail('NPM yang berbeda seharusnya ditolak.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('NPM akun tidak sama', $exception->getMessage());
        }

        $this->assertDatabaseMissing('campus_identity_verifications', ['user_id' => $user->id]);
        $this->assertSame('9999999999999', (string) $user->fresh()->npm);
        $this->assertNull($user->fresh()->mahasiswa);
    }

    public function test_different_email_is_rejected_even_when_npm_matches(): void
    {
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 2210631170002,
            'email' => 'email-lama@example.test',
        ]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessage('Email akun tidak sama');

        app(CampusIdentityReconciliationService::class)->submit($user, [
            'nim' => '2210631170002',
            'nama_mahasiswa' => 'SITI AMINAH',
            'email' => '2210631170002@student.unsika.ac.id',
        ]);
    }

    public function test_admin_cannot_approve_a_request_with_different_npm_or_email(): void
    {
        $user = User::factory()->create([
            'name' => 'Siti Aminah',
            'npm' => 9999999999999,
            'email' => 'legacy@example.test',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $service = app(CampusIdentityReconciliationService::class);

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

        try {
            $service->approve($verification, $admin, 'Identitas sudah diperiksa.');

            $this->fail('Admin seharusnya tidak dapat melewati pencocokan identitas ketat.');
        } catch (\DomainException $exception) {
            $this->assertStringContainsString('harus sama', $exception->getMessage());
        }

        $this->assertSame(CampusIdentityVerification::STATUS_PENDING, $verification->fresh()->status);
        $this->assertSame('9999999999999', (string) $user->fresh()->npm);
    }

    public function test_readonly_identity_values_from_the_form_are_ignored_by_the_server(): void
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
                'npm' => '9999999999999',
                'email' => 'penyerang@example.test',
                'password' => 'password',
            ])
            ->assertRedirect(route('home'));

        $this->assertSame('2210631170001', (string) $user->fresh()->npm);
        $this->assertSame('2210631170001@student.unsika.ac.id', $user->fresh()->email);
    }
}
