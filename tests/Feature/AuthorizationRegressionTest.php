<?php

namespace Tests\Feature;

use App\Models\CampusIdentityVerification;
use App\Models\Mahasiswa;
use App\Models\SuratBebasPustaka;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationRegressionTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_access_another_users_account_routes(): void
    {
        $user = $this->verifiedStudent('2201000000001');
        $otherUser = $this->verifiedStudent('2201000000002');

        $routes = [
            'user.profile',
            'user.settingAccount',
            'user.settingSecurity',
            'user.userGuide',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route, ['id' => $otherUser->id]))
                ->assertForbidden();
        }

        $this->actingAs($user)
            ->post(route('user.lengkapiprofile', ['id' => $otherUser->id]))
            ->assertForbidden();

        $this->actingAs($user)
            ->post(route('change.password', ['id' => $otherUser->id]))
            ->assertForbidden();
    }

    public function test_user_cannot_open_a_letter_form_for_another_user(): void
    {
        $user = $this->verifiedStudent('2201000000003');
        $otherUser = $this->verifiedStudent('2201000000004');

        $routes = [
            'suratizinpenelitian.create',
            'suratketeranganaktif.create',
            'suratketeranganaktifortupns.create',
            'suratbebaspustaka.create',
            'suratpengajuancuti.create',
            'suratpengundurandiri.create',
            'suratobservasi.create',
            'suratpindahkuliah.create',
            'suratpengantarmagang.create',
            'suratrekomendasisept.create',
        ];

        foreach ($routes as $route) {
            $this->actingAs($user)
                ->get(route($route, ['id' => $otherUser->id]))
                ->assertForbidden();
        }
    }

    public function test_admin_cannot_change_another_admins_profile_via_route_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $otherAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.profile', ['id' => $otherAdmin->id]))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('change.password.admin', ['id' => $otherAdmin->id]))
            ->assertForbidden();
    }

    public function test_delete_student_route_has_the_expected_uri(): void
    {
        $this->assertStringEndsWith('/admin/data-mahasiswa/123', route('delete.mahasiswa', ['id' => 123]));
    }

    public function test_user_cannot_download_another_students_letter(): void
    {
        $user = $this->verifiedStudent('2201000000005');

        SuratBebasPustaka::query()->create([
            'nama_mhs' => 'Mahasiswa Lain',
            'npm' => '2201000000006',
            'prodi' => 'Informatika',
            'file_path' => 'surat-mahasiswa-lain.pdf',
            'status' => 'disetujui',
        ]);

        $this->actingAs($user)
            ->get(route('download-surat', [
                'folders' => 'surat-bebas-pustaka',
                'file_path' => 'surat-mahasiswa-lain.pdf',
            ]))
            ->assertForbidden();
    }

    public function test_profile_update_preserves_readonly_official_fields(): void
    {
        $user = $this->verifiedStudent('2201000000007');

        $this->actingAs($user)
            ->post(route('user.lengkapiprofile', ['id' => $user->id]), [
                'semester' => 'VII (Tujuh)',
                'domisili' => 'Bandar Lampung',
                'jenis_kelamin' => 'Laki-laki',
                'no_hp' => '081234567890',
                'prodi' => 'Sistem Informasi',
                'status' => 'Drop Out',
            ])
            ->assertRedirect(route('user.profile', ['id' => $user->id]));

        $this->assertDatabaseHas('mahasiswa', [
            'user_id' => $user->id,
            'npm' => $user->npm,
            'prodi' => 'Informatika',
            'status' => 'Aktif',
        ]);
    }

    private function verifiedStudent(string $npm): User
    {
        $user = User::factory()->create([
            'npm' => $npm,
            'role' => 'user',
        ]);

        Mahasiswa::create([
            'user_id' => $user->id,
            'npm' => $npm,
            'prodi' => 'Informatika',
            'status' => 'Aktif',
        ]);

        CampusIdentityVerification::create([
            'user_id' => $user->id,
            'npm' => $npm,
            'status' => CampusIdentityVerification::STATUS_VERIFIED,
            'official_name' => $user->name,
            'official_email' => $user->email,
            'campus_payload' => [],
            'verified_at' => now(),
        ]);

        return $user;
    }
}
