<?php

namespace App\Services;

use App\Models\CampusIdentityVerification;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CampusIdentityReconciliationService
{
    public function __construct(private readonly CampusStudentService $campusStudents) {}

    public function submit(User $user, array $student): CampusIdentityVerification
    {
        $npm = (string) $student['nim'];
        $officialName = Str::title(trim((string) $student['nama_mahasiswa']));
        $officialEmail = $this->campusStudents->resolveEmail($student);
        $campusStudentId = $this->campusStudents->campusStudentId($student);

        $npmMatches = (string) $user->npm === $npm;
        $emailMatches = (string) $user->email === $officialEmail;
        $similarity = $this->nameSimilarity((string) $user->name, $officialName);

        $reasons = array_values(array_filter([
            $npmMatches ? 'NPM lama sama dengan data kampus.' : null,
            $emailMatches ? 'Email akun sama dengan data kampus.' : null,
            $similarity >= 85 ? 'Nama lama sangat mirip dengan nama resmi.' : null,
        ]));

        $hasConflict = $this->hasIdentityConflict($user, $npm, $officialEmail, $campusStudentId);

        if (! $npmMatches) {
            throw new \DomainException('NPM akun tidak sama dengan NPM pada data kampus. Hubungi admin untuk memperbaiki data akun.');
        }

        if (! $emailMatches) {
            throw new \DomainException('Email akun tidak sama dengan email pada data kampus. Hubungi admin untuk memperbaiki data akun.');
        }

        if ($hasConflict) {
            throw new \DomainException('NPM atau email kampus sudah terhubung dengan akun lain. Hubungi admin untuk pemeriksaan akun duplikat.');
        }

        return DB::transaction(function () use (
            $user,
            $student,
            $npm,
            $officialName,
            $officialEmail,
            $campusStudentId,
            $similarity,
            $reasons,
        ) {
            $verification = CampusIdentityVerification::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'campus_student_id' => $campusStudentId,
                    'npm' => $npm,
                    'status' => CampusIdentityVerification::STATUS_VERIFIED,
                    'old_name' => $user->name,
                    'old_npm' => $user->npm ? (string) $user->npm : null,
                    'old_email' => $user->email,
                    'official_name' => $officialName,
                    'official_email' => $officialEmail,
                    'name_similarity' => $similarity,
                    'match_reasons' => $reasons,
                    'campus_payload' => $student,
                    'submitted_at' => now(),
                    'verified_at' => now(),
                    'reviewed_at' => null,
                    'reviewed_by' => null,
                    'review_note' => null,
                ]
            );

            $this->applyOfficialIdentity($user, $verification);

            return $verification;
        });
    }

    public function recordNewRegistration(User $user, array $student): CampusIdentityVerification
    {
        $verification = CampusIdentityVerification::create([
            'user_id' => $user->id,
            'campus_student_id' => $this->campusStudents->campusStudentId($student),
            'npm' => (string) $student['nim'],
            'status' => CampusIdentityVerification::STATUS_VERIFIED,
            'old_name' => $user->name,
            'old_npm' => (string) $user->npm,
            'old_email' => $user->email,
            'official_name' => $user->name,
            'official_email' => $this->campusStudents->resolveEmail($student),
            'name_similarity' => 100,
            'match_reasons' => ['Akun dibuat langsung dari database kampus.'],
            'campus_payload' => $student,
            'submitted_at' => now(),
            'verified_at' => now(),
        ]);

        return $verification;
    }

    public function approve(CampusIdentityVerification $verification, User $reviewer, ?string $note): void
    {
        DB::transaction(function () use ($verification, $reviewer, $note) {
            $verification = CampusIdentityVerification::query()->lockForUpdate()->findOrFail($verification->id);
            $user = User::query()->lockForUpdate()->findOrFail($verification->user_id);

            if ($verification->status !== CampusIdentityVerification::STATUS_PENDING) {
                throw new \DomainException('Permintaan ini sudah diproses.');
            }

            if ((string) $user->npm !== (string) $verification->npm
                || (string) $user->email !== (string) $verification->official_email) {
                throw new \DomainException('NPM dan email akun harus sama dengan database kampus. Permintaan ini tidak dapat disetujui.');
            }

            if ($this->hasIdentityConflict($user, $verification->npm, $verification->official_email, $verification->campus_student_id)) {
                throw new \DomainException('Identitas kampus masih terhubung dengan akun lain. Selesaikan akun duplikat terlebih dahulu.');
            }

            $verification->update([
                'status' => CampusIdentityVerification::STATUS_VERIFIED,
                'verified_at' => now(),
                'reviewed_at' => now(),
                'reviewed_by' => $reviewer->id,
                'review_note' => $note,
            ]);

            $this->applyOfficialIdentity($user, $verification);
        });
    }

    public function reject(CampusIdentityVerification $verification, User $reviewer, string $note): void
    {
        if ($verification->status !== CampusIdentityVerification::STATUS_PENDING) {
            throw new \DomainException('Permintaan ini sudah diproses.');
        }

        $verification->update([
            'status' => CampusIdentityVerification::STATUS_REJECTED,
            'verified_at' => null,
            'reviewed_at' => now(),
            'reviewed_by' => $reviewer->id,
            'review_note' => $note,
        ]);
    }

    private function applyOfficialIdentity(User $user, CampusIdentityVerification $verification): void
    {
        $user->update([
            'npm' => $verification->npm,
            'name' => $verification->official_name,
        ]);

        Mahasiswa::updateOrCreate(
            ['user_id' => $user->id],
            ['npm' => $verification->npm, 'foto' => $user->mahasiswa?->foto ?? 'user_profile.png']
        );
    }

    private function hasIdentityConflict(User $user, string $npm, ?string $officialEmail, ?string $campusStudentId): bool
    {
        $userConflict = User::query()
            ->whereKeyNot($user->id)
            ->where(function ($query) use ($npm, $officialEmail) {
                $query->where('npm', $npm);

                if ($officialEmail) {
                    $query->orWhereRaw('LOWER(email) = ?', [strtolower($officialEmail)]);
                }
            })
            ->exists();

        $verificationConflict = CampusIdentityVerification::query()
            ->where('user_id', '!=', $user->id)
            ->where(function ($query) use ($npm, $campusStudentId) {
                $query->where('npm', $npm);

                if ($campusStudentId) {
                    $query->orWhere('campus_student_id', $campusStudentId);
                }
            })
            ->exists();

        return $userConflict || $verificationConflict;
    }

    private function nameSimilarity(string $left, string $right): float
    {
        $left = $this->normalizeName($left);
        $right = $this->normalizeName($right);
        $longest = max(strlen($left), strlen($right));

        if ($longest === 0) {
            return 0;
        }

        return round((1 - levenshtein($left, $right) / $longest) * 100, 2);
    }

    private function normalizeName(string $name): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($name))) ?? '';
    }
}
