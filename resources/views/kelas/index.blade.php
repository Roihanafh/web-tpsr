@extends('layouts.app')

@section('page-title')
Data Kelas
@endsection

@section('main-content')
    <livewire:kelas.kelas-page />
@endsection

@push('scripts')
<script>
    function confirmDeleteAllKelas() {
        Swal.fire({
            title: 'Hapus Seluruh Data Kelas?',
            icon: 'warning',
            html: `
                <div class="text-left text-start">
                    <div class="alert alert-danger mb-3" style="font-size:0.9rem;text-align:left;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Peringatan Sangat Penting:</strong> Tindakan ini akan menghapus <strong>seluruh data kelas</strong> beserta seluruh data <strong>siswa</strong> dan <strong>penilaian</strong> yang terkait di sekolah ini!
                    </div>
                    <p style="text-align:left;">Apakah Anda yakin ingin melanjutkan? Anda akan diminta melakukan verifikasi konfirmasi pada langkah berikutnya.</p>
                </div>
            `,
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, Lanjutkan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Verifikasi Kedua',
                    text: 'Ketik "HAPUS SEMUA DATA" untuk mengonfirmasi tindakan ini:',
                    input: 'text',
                    inputPlaceholder: 'HAPUS SEMUA DATA',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Hapus Permanen!',
                    cancelButtonText: 'Batal',
                    preConfirm: (value) => {
                        if (value !== 'HAPUS SEMUA DATA') {
                            Swal.showValidationMessage('Teks konfirmasi salah. Harap ketik "HAPUS SEMUA DATA"');
                            return false;
                        }
                        return true;
                    }
                }).then((secondResult) => {
                    if (secondResult.isConfirmed) {
                        Livewire.dispatch('delete-all-kelas');
                    }
                });
            }
        });
    }
</script>
@endpush
