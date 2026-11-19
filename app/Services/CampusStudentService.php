<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class CampusStudentService
{
    public function findByNim(string $nim): ?array
    {
        $baseUrl = rtrim((string) config('services.kampus.base_url'), '/');
        $token = (string) config('services.kampus.token');

        if ($baseUrl === '' || $token === '') {
            throw new \RuntimeException('Konfigurasi database kampus belum lengkap.');
        }

        $response = Http::acceptJson()
            ->withToken($token)
            ->timeout(15)
            ->retry(2, 250, null, false)
            ->get($baseUrl.'/mahasiswa/nim/'.urlencode($nim));

        if ($response->notFound()) {
            return null;
        }

        $response->throw();

        $data = $response->json('data');
        $student = is_array($data) && array_is_list($data)
            ? ($data[0] ?? null)
            : $data;

        if (! is_array($student)
            || (string) ($student['nim'] ?? '') !== $nim
            || trim((string) ($student['nama_mahasiswa'] ?? '')) === '') {
            return null;
        }

        return $student;
    }

    public function resolveEmail(array $student): string
    {
        $email = strtolower(trim((string) ($student['email'] ?? '')));

        return filter_var($email, FILTER_VALIDATE_EMAIL)
            ? $email
            : $student['nim'].'@student.unsika.ac.id';
    }

    public function resolveProgramStudy(array $student): ?string
    {
        $candidates = [
            data_get($student, 'prodi'),
            data_get($student, 'nama_prodi'),
            data_get($student, 'program_studi'),
            data_get($student, 'nama_program_studi'),
            data_get($student, 'prodi.nama'),
            data_get($student, 'prodi.nama_prodi'),
            data_get($student, 'program_studi.nama'),
            data_get($student, 'program_studi.nama_program_studi'),
        ];

        foreach ($candidates as $candidate) {
            if (! is_scalar($candidate)) {
                continue;
            }

            $normalized = strtolower(trim((string) $candidate));

            if (str_contains($normalized, 'sistem informasi')) {
                return 'Sistem Informasi';
            }

            if (str_contains($normalized, 'informatika')) {
                return 'Informatika';
            }
        }

        return null;
    }

    public function campusStudentId(array $student): ?string
    {
        foreach (['id', 'mahasiswa_id', 'student_id', 'id_mahasiswa'] as $key) {
            $value = trim((string) ($student[$key] ?? ''));

            if ($value !== '') {
                return $value;
            }
        }

        return null;
    }
}
