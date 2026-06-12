<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'nama_sekolah' => ['nullable', 'string', 'max:255'],
            'alamat_sekolah' => ['nullable', 'string'],
        ]);

        $user = DB::transaction(function () use ($request) {
            $namaSekolah = trim((string) ($request->nama_sekolah ?? ''));
            $alamatSekolah = trim((string) ($request->alamat_sekolah ?? ''));
            $sekolahId = null;

            if ($namaSekolah !== '') {
                $sekolah = \App\Models\Sekolah::where('nama', $namaSekolah)->first();
                if (! $sekolah) {
                    $sekolah = \App\Models\Sekolah::create([
                        'nama' => $namaSekolah,
                        'alamat' => $alamatSekolah,
                    ]);
                }
                $sekolahId = $sekolah->id;
            }

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'sekolah_id' => $sekolahId,
            ]);

            // Assign user tersebut sebagai role guru yaitu id 1
            $user->assignRole(1);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
