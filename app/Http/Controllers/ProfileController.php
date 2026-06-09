<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Sekolah;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $sekolahOptions = Sekolah::query()
            ->select('id', 'nama', 'alamat')
            ->orderBy('nama')
            ->get()
            ->map(fn (Sekolah $sekolah): array => [
                'id' => $sekolah->id,
                'nama' => $sekolah->nama,
                'alamat' => $sekolah->alamat,
            ])
            ->values();

        return view('profile.edit', [
            'user' => $request->user()->load('sekolah'),
            'sekolahOptions' => $sekolahOptions,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        DB::transaction(function () use ($validated, $user): void {
            $user->fill([
                'name' => $validated['name'],
                'email' => $validated['email'],
            ]);

            if ($user->isDirty('email')) {
                $user->email_verified_at = null;
            }

            $user->save();

            $namaSekolah = trim((string) ($validated['nama_sekolah'] ?? ''));
            $alamatSekolah = trim((string) ($validated['alamat_sekolah'] ?? ''));
            $sekolahAction = $validated['sekolah_action'] ?? 'move';

            if ($namaSekolah === '') {
                return;
            }

            $sekolahSaatIni = $user->sekolah()->lockForUpdate()->first();
            $sekolahTujuan = Sekolah::query()
                ->where('nama', $namaSekolah)
                ->lockForUpdate()
                ->first();

            if (
                $sekolahTujuan
                && (! $sekolahSaatIni || $sekolahSaatIni->isNot($sekolahTujuan))
                && $sekolahAction !== 'move'
            ) {
                return;
            }

            if (
                $sekolahAction === 'move'
                && $sekolahTujuan
                && (! $sekolahSaatIni || $sekolahSaatIni->isNot($sekolahTujuan))
            ) {
                $user->forceFill(['sekolah_id' => $sekolahTujuan->id])->save();

                return;
            }

            if ($sekolahSaatIni) {
                $sekolahSaatIni->update([
                    'nama' => $namaSekolah,
                    'alamat' => $alamatSekolah,
                ]);

                return;
            }

            $sekolahBaru = Sekolah::query()->create([
                'nama' => $namaSekolah,
                'alamat' => $alamatSekolah,
            ]);

            $user->forceFill(['sekolah_id' => $sekolahBaru->id])->save();
        });

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
