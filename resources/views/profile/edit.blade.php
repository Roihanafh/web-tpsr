@extends('layouts.app')

@section('page-title')
Profil
@endsection

@section('main-content')
<div class="row">
    <div class="col-lg-8">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-user-circle mr-2"></i>Informasi Profil
                </h3>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-lock mr-2"></i>Ubah Password
                </h3>
            </div>
            <div class="card-body">
                @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-id-badge mr-2"></i>Akun
                </h3>
            </div>
            <div class="card-body">
                <strong>{{ $user->name }}</strong>
                <p class="text-muted">{{ $user->email }}</p>

                <hr>

                <strong>
                    <i class="fas fa-school mr-1"></i>Sekolah
                </strong>
                <p class="text-muted mb-1">
                    {{ $user->sekolah?->nama ?? 'Belum terhubung dengan sekolah' }}
                </p>

                <strong>
                    <i class="fas fa-map-marker-alt mr-1"></i>Alamat
                </strong>
                <p class="text-muted mb-0">
                    {{ $user->sekolah?->alamat ?? '-' }}
                </p>
            </div>
        </div>

        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-exclamation-triangle mr-2"></i>Zona Berbahaya
                </h3>
            </div>
            <div class="card-body">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</div>
@endsection
