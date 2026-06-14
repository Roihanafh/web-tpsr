<div class="modal fade" id="panduanModal" tabindex="-1" role="dialog" aria-labelledby="panduanModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content panduan-modal-content">
            <div class="modal-header panduan-modal-header">
                <h5 class="modal-title font-weight-bold" id="panduanModalLabel">
                    <i class="fas fa-book-open mr-2"></i> Buku Panduan Website TPSR
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body panduan-modal-body">
                <div class="panduan-modal-layout">
                    {{-- Modal Sidebar --}}
                    <div class="panduan-modal-sidebar">
                        <a class="panduan-modal-nav-link active" id="modal-tab-akses" data-toggle="pill" href="#modal-content-akses" role="tab" aria-controls="modal-content-akses" aria-selected="true">
                            <i class="fas fa-user-shield"></i> Akses & Login
                        </a>
                        <a class="panduan-modal-nav-link" id="modal-tab-kelas" data-toggle="pill" href="#modal-content-kelas" role="tab" aria-controls="modal-content-kelas" aria-selected="false">
                            <i class="fas fa-school"></i> Manajemen Kelas
                        </a>
                        <a class="panduan-modal-nav-link" id="modal-tab-penilaian" data-toggle="pill" href="#modal-content-penilaian" role="tab" aria-controls="modal-content-penilaian" aria-selected="false">
                            <i class="fas fa-clipboard-check"></i> Penilaian TPSR
                        </a>
                        <a class="panduan-modal-nav-link" id="modal-tab-laporan" data-toggle="pill" href="#modal-content-laporan" role="tab" aria-controls="modal-content-laporan" aria-selected="false">
                            <i class="fas fa-chart-line"></i> Laporan Analisis
                        </a>
                    </div>

                    {{-- Modal Main Content --}}
                    <div class="panduan-modal-main tab-content">
                        {{-- Tab 1: Akses & Login --}}
                        
                        <div class="tab-pane fade show active" id="modal-content-akses" role="tabpanel" aria-labelledby="modal-tab-akses">
                            <h5 class="font-weight-bold text-dark mb-3">Hak Akses Pengguna & Login Sistem</h5>
                            <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                                Hak Akses Pengguna meliputi melihat daftar siswa sesuai kelas yang diampu,
                                mengisi, mengubah, menyimpan, dan memperbarui data penilaian TPSR,
                                melihat status penilaian setiap pertemuan, serta mendownload hasil penilaian.
                                Sebelum menggunakan fitur, pengguna harus login terlebih dahulu.
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Buka Halaman Login</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Buka halaman login aplikasi pada browser Anda.</p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Daftar Akun</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Daftar terlebih dahulu jika Anda belum mempunyai akun.</p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">3</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Kredensial & Masuk</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Masukkan username yang terdaftar dan password, lalu klik tombol Login untuk masuk ke halaman dashboard.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: flex-end;">
                                <button onclick="showModalTab('modal-tab-kelas')" class="btn-panduan-next py-1.5 px-3" style="font-size:0.8rem;">
                                    Selanjutnya: Kelas <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Tab 2: Manajemen Kelas --}}
                        <div class="tab-pane fade" id="modal-content-kelas" role="tabpanel" aria-labelledby="modal-tab-kelas">
                            <h5 class="font-weight-bold text-dark mb-3">Manajemen Kelas</h5>
                            <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                                Menu ini digunakan untuk mengelola seluruh data kelas yang terdaftar pada sistem  dengan langkah-langkah berikut:
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Akses Menu</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Setelah login, pada menu sidebar sebelah kiri, klik <strong>Manajemen Kelas</strong>.</p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Pilih Submenu</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Pilih antara <strong>Data Kelas</strong> atau <strong>Data Siswa</strong> untuk menampilkan halamannya.</p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Fitur Utama Halaman:</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height: 1.6;">
                                            • <strong>Filter:</strong> Untuk memilih data yang ingin dilihat.<br>
                                            • <strong>Tambah Data:</strong> Untuk menambahkan data baru.<br>
                                            • <strong>Edit Data:</strong> Untuk mengganti data yang sudah ada.<br>
                                            • <strong>Import:</strong> Menambahkan data via Excel memakai template yang disediakan.<br>
                                            • <strong>Export:</strong> Mendownload data yang sudah ada di dalam sistem.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between;">
                                <button onclick="showModalTab('modal-tab-akses')" class="btn-panduan-prev py-1.5 px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-arrow-left"></i> Sebelumnya
                                </button>
                                <button onclick="showModalTab('modal-tab-penilaian')" class="btn-panduan-next py-1.5 px-3" style="font-size:0.8rem;">
                                    Selanjutnya: Penilaian <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Tab 3: Penilaian TPSR --}}
                        <div class="tab-pane fade" id="modal-content-penilaian" role="tabpanel" aria-labelledby="modal-tab-penilaian">
                            <h5 class="font-weight-bold text-dark mb-3">Penilaian TPSR</h5>
                            <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                                Menu Penilaian digunakan untuk memberikan penilaian kepada siswa dari kelas yang diampu.
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Gunakan Filter Dropdown</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Sebelum melakukan penilaian, Pengguna wajib menentukan data yang akan dinilai melalui filter yang tersedia. </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Simpan Penilaian</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Isi form evaluasi yang tampil, kemudian simpan hasil penilaian Anda dengan menekan tombol <strong>Simpan Penilaian</strong>.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between;">
                                <button onclick="showModalTab('modal-tab-kelas')" class="btn-panduan-prev py-1.5 px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-arrow-left"></i> Sebelumnya
                                </button>
                                <button onclick="showModalTab('modal-tab-laporan')" class="btn-panduan-next py-1.5 px-3" style="font-size:0.8rem;">
                                    Selanjutnya: Laporan <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Tab 4: Laporan Analisis --}}
                        <div class="tab-pane fade" id="modal-content-laporan" role="tabpanel" aria-labelledby="modal-tab-laporan">
                            <h5 class="font-weight-bold text-dark mb-3">Laporan Analisis</h5>
                            <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                                Menu Laporan Analisis digunakan untuk melihat hasil dari penilaian TPSR secara keseluruhan serta mendownload hasilnya dalam bentuk format berkas PDF.
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Tentukan Data Melalui Filter</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Sebelum melihat data laporan analisis, Pengguna wajib menentukan data yang ingin dilihat terlebih dahulu melalui filter yang tersedia. </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Lihat Detail Laporan</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Pengguna bisa memilih dan melihat detail data yang diinginkan dengan mengklik tombol <strong>Detail</strong> yang ada pada kolom Grafik. </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">3</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Download Output PDF</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">Setelah data dipilih, Anda dapat mendownload file hasil laporan analisis tersebut dalam format PDF dengan mengklik tombol <strong>Download</strong>.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between;">
                                <button onclick="showModalTab('modal-tab-penilaian')" class="btn-panduan-prev py-1.5 px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-arrow-left"></i> Sebelumnya
                                </button>
                                <button type="button" class="btn-panduan-next py-1.5 px-3" data-dismiss="modal" style="font-size:0.8rem; background: #22c55e;">
                                    Selesai <i class="fas fa-check-circle"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer panduan-modal-footer">
                <button type="button" class="btn btn-primary bg-success border-0 px-4 font-weight-bold shadow-sm" data-dismiss="modal" style="border-radius: 8px;">
                    <i class="fas fa-check mr-1"></i> Mengerti
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        $('.panduan-modal-nav-link').on('click', function(e) {
            e.preventDefault();
            $('.panduan-modal-nav-link').removeClass('active');
            $('.tab-pane').removeClass('show active');
            $(this).addClass('active');
            let target = $(this).attr('href');
            $(target).addClass('show active');
            $('.panduan-modal-main').scrollTop(0);
        });
    });

    function showModalTab(tabId) {
        $('.panduan-modal-nav-link').removeClass('active');
        $('.tab-pane').removeClass('show active');
        $('#' + tabId).addClass('active');
        let target = $('#' + tabId).attr('href');
        $(target).addClass('show active');
        $('.panduan-modal-main').scrollTop(0);
    }
</script>
@endpush