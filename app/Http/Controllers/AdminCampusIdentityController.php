<?php

namespace App\Http\Controllers;

use App\Models\CampusIdentityVerification;
use App\Services\CampusIdentityReconciliationService;
use Illuminate\Http\Request;

class AdminCampusIdentityController extends Controller
{
    public function index()
    {
        $verifications = CampusIdentityVerification::with(['user', 'reviewer'])
            ->orderByRaw("CASE WHEN status = 'pending_review' THEN 0 ELSE 1 END")
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.pages.campus-identities', compact('verifications'));
    }

    public function approve(
        Request $request,
        CampusIdentityVerification $verification,
        CampusIdentityReconciliationService $reconciliation
    ) {
        $validated = $request->validate([
            'review_note' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $reconciliation->approve($verification, $request->user(), $validated['review_note'] ?? null);
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Identitas mahasiswa disetujui dan data resmi telah diterapkan.');
    }

    public function reject(
        Request $request,
        CampusIdentityVerification $verification,
        CampusIdentityReconciliationService $reconciliation
    ) {
        $validated = $request->validate([
            'review_note' => ['required', 'string', 'max:1000'],
        ], [
            'review_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            $reconciliation->reject($verification, $request->user(), $validated['review_note']);
        } catch (\DomainException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return back()->with('success', 'Permintaan verifikasi ditolak.');
    }
}
