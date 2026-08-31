<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ $appName }}</title>
</head>
<body style="margin:0;padding:0;background-color:#f5f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#495057;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="background-color:#f5f5f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="max-width:560px;background-color:#ffffff;border-radius:8px;overflow:hidden;box-shadow:0 2px 4px rgba(0,0,0,0.05);">
                    <tr>
                        <td style="background-color:#696cff;padding:24px 32px;text-align:center;">
                            <h1 style="margin:0;color:#ffffff;font-size:20px;font-weight:600;">Reset Password</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px 0;font-size:15px;line-height:1.5;">Halo,</p>
                            <p style="margin:0 0 24px 0;font-size:15px;line-height:1.5;">
                                Kami menerima permintaan untuk mengatur ulang kata sandi akun <strong>{{ $appName }}</strong> Anda (<a href="mailto:{{ $userEmail }}" style="color:#696cff;text-decoration:none;">{{ $userEmail }}</a>).
                            </p>
                            <p style="margin:0 0 24px 0;font-size:15px;line-height:1.5;">Klik tombol di bawah ini untuk membuat kata sandi baru:</p>

                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;">
                                <tr>
                                    <td align="center" style="border-radius:6px;background-color:#696cff;">
                                        <a href="{{ $resetUrl }}" target="_blank" style="display:inline-block;padding:12px 28px;color:#ffffff;text-decoration:none;font-weight:600;font-size:15px;border-radius:6px;">Reset Password Saya</a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 24px 0;font-size:14px;line-height:1.5;color:#6c757d;">Tautan reset ini akan kedaluwarsa dalam <strong>{{ $expiresInMinutes }} menit</strong> demi keamanan akun Anda.</p>

                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0" border="0" style="margin:24px 0;border:1px solid #e7e7ee;border-radius:6px;background-color:#f8f8fc;">
                                <tr>
                                    <td style="padding:16px;">
                                        <p style="margin:0 0 8px 0;font-size:13px;line-height:1.5;color:#495057;font-weight:600;">Mengapa Anda menerima email ini?</p>
                                        <p style="margin:0;font-size:13px;line-height:1.5;color:#6c757d;">
                                            Email ini dikirim secara otomatis oleh sistem {{ $appName }} karena Anda (atau seseorang yang menggunakan akun Anda) meminta untuk mengatur ulang kata sandi.
                                            <br><br>
                                            Jika Anda <strong>tidak</strong> merasa meminta reset password, silakan abaikan email ini. Akun Anda tetap aman dan tidak ada tindakan lebih lanjut yang diperlukan.
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0;font-size:13px;line-height:1.5;color:#9a9ab0;border-top:1px solid #e7e7ee;padding-top:16px;">
                                Jika tombol di atas tidak dapat diklik, salin dan tempel tautan berikut ke peramban Anda:<br>
                                <a href="{{ $resetUrl }}" style="color:#696cff;word-break:break-all;">{{ $resetUrl }}</a>
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:16px 32px;background-color:#f8f8fc;border-top:1px solid #e7e7ee;text-align:center;">
                            <p style="margin:0 0 4px 0;font-size:12px;color:#9a9ab0;">&copy; {{ $year }} {{ $appName }} &middot; Tata Usaha</p>
                            <p style="margin:0;font-size:11px;color:#9a9ab0;">Email ini adalah pesan otomatis. Mohon tidak membalas langsung ke email ini.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
