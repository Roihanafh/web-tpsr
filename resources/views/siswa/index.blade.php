@extends('layouts.app')

@section('page-title')
Data Siswa
@endsection

@section('main-content')
    <livewire:siswa.siswa-page />
@endsection

@push('scripts')
<script>
    function confirmDeleteAllSiswa(kelasNama) {
        const isFiltered = kelasNama && kelasNama !== '0' && kelasNama !== '';
        const title = isFiltered ? `Hapus Siswa Kelas ${kelasNama}?` : 'Hapus Seluruh Data Siswa?';
        const warningMessage = isFiltered 
            ? `Tindakan ini akan menghapus <strong>seluruh data siswa di Kelas ${kelasNama}</strong> beserta seluruh data <strong>penilaian</strong> yang terkait!`
            : `Tindakan ini akan menghapus <strong>seluruh data siswa</strong> beserta seluruh data <strong>penilaian</strong> yang terkait di sekolah ini!`;

        Swal.fire({
            title: title,
            icon: 'warning',
            html: `
                <div class="text-left text-start">
                    <div class="alert alert-danger mb-3" style="font-size:0.9rem;text-align:left;">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Peringatan Sangat Penting:</strong> ${warningMessage}
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
                        Livewire.dispatch('delete-all-siswa', { kelasNama: kelasNama });
                    }
                });
            }
        });
    }
</script>
@endpush
