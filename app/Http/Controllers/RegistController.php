<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Mahasiswa;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegistController extends Controller
{
    public function index()
    {
        return view('auth/register');
    }

    public function create()
    {
        $validator = $this->validate(request(), [
        'name' => 'required|min:2',
        'npm' => 'required|unique:users', 
        'email' => [
            'required','email','unique:users',
            function ($attribute, $value, $fail) {
                // Pisahkan bagian sebelum '@'
                $localPart = explode('@', $value)[0];
                
                // Periksa apakah bagian depan hanya mengandung karakter alfanumerik dan tanda hubung (_-)
                if (!preg_match('/^[a-zA-Z0-9._-]+$/', $localPart)) {
                    $fail('"Bagian depan email hanya boleh mengandung huruf, angka, titik, garis bawah, atau tanda hubung."');
                }
            }
        ],
        'password' => 'required|min:6'
        ], [
            'name.min' => 'Nama harus memiliki minimal 2 karakter.',
            'password.min' => 'Password harus memiliki minimal 6 karakter/angka.',
            'npm.unique' => 'NPM telah terdaftar.',
            'email.unique' => 'Email telah terdaftar.'
        ]);

        $allowedDomain = 'student.unsika.ac.id';
        $email = request('email');
        $npm = request('npm');
        $npmRegex = '/^([0-9]{13})@student\.unsika\.ac\.id$/i';
        
        // Ambil awalan npm dari email (contoh: 2010631170075@student.unsika.ac.id -> 2010631170075)
        // preg_match($npmRegex, $email, $matches);
        // $extractedNpm = $matches[1] ?? null;
        
        // Periksa apakah npm yang diambil sesuai dengan npm yang diinput
        // if ($extractedNpm !== $npm) {
        //     return redirect()->back()->withInput()->with('error', 'Bagian depan email harus sesuai dengan NPM.');
        // }

        // Jika email tidak memiliki domain student.unsika.ac.id.
        if (substr(strrchr($email, "@"), 1) !== $allowedDomain) {
            return redirect()->back()->withInput()->with('error', 'Alamat email harus memiliki domain ' . $allowedDomain);
        }

        // Periksa apakah terdapat karakter HTML khusus dalam input
        $containsHtmlSpecialChar = $this->containsHtmlSpecialChar(request()->all());
        if ($containsHtmlSpecialChar) {
            return redirect()->back()->withInput()->with('error', 'Input tidak valid, harap isi dengan teks biasa.');
        }

        // Periksa apakah npm terdaftar di tabel Mahasiswa
        $mahasiswa = Mahasiswa::where('npm', $npm)->first();
        if (!$mahasiswa) {
            return redirect()->back()->withInput()->with('error', 'NPM yang dimasukkan bukan mahasiswa Fasilkom.');
        }

        // Temukan atau buat pengguna baru
        $user = User::firstOrCreate(
            ['npm' => $npm],
            [
                'name' => Str::title(request('name')),
                'email' => $email,
                // 'password' => bcrypt(request('password')),
                'password' => Hash::make(request('password')),
                'remember_token' => Str::random(60),
            ]
        );

        // Hubungkan pengguna dengan mahasiswa
        Mahasiswa::updateOrCreate(
            ['npm' => $npm],
            ['user_id' => $user->id, 'foto' => 'user_profile.png']
        );

        return redirect()->route('login')->with('success', 'Anda Telah Berhasil Mendaftar!');
    }

    private function containsHtmlSpecialChar($data)
    {
        foreach ($data as $value) {
            if ($value != strip_tags($value)) {
                return true;
            }
        }
        return false;
    }
}
