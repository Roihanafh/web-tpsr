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
                        <a class="panduan-modal-nav-link" id="modal-tab-troubleshoot" data-toggle="pill" href="#modal-content-troubleshoot" role="tab" aria-controls="modal-content-troubleshoot" aria-selected="false">
                            <i class="fas fa-tools"></i> Troubleshooting
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
                                Tersedia dua cara: input langsung di sistem atau import via file Excel.
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Pilih Kelas &amp; Pertemuan</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Gunakan dropdown <strong>Pilih Kelas</strong> dan <strong>Pilih Pertemuan</strong> (1–16)
                                            untuk menentukan data yang akan dinilai. Tabel penilaian akan muncul otomatis setelah keduanya dipilih.
                                        </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Isi &amp; Simpan Penilaian</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Isi nilai <strong>L0–L4</strong> (skala 1–4) untuk setiap siswa pada tabel yang muncul,
                                            lalu klik <strong>Simpan Penilaian</strong>.
                                            Untuk mengosongkan semua nilai pertemuan yang aktif, gunakan tombol <strong>Kosongkan Penilaian</strong>.
                                        </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">3</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Import via Excel (Alternatif Cepat)</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height: 1.7;">
                                            Klik <strong>Import Excel</strong> → <strong>Download template</strong> untuk mengunduh file <strong>TPSR_template.xlsx</strong>.<br>
                                            Isi template pada tab <strong>Input_TPSR</strong>:<br>
                                            &nbsp;&nbsp;• Sel <strong>B2</strong> = nama kelas (harus sama persis dengan nama di sistem, atau akan dibuat otomatis jika belum ada).<br>
                                            &nbsp;&nbsp;• Kolom <strong>A</strong> mulai baris 5 = nama siswa.<br>
                                            &nbsp;&nbsp;• Kolom <strong>B–F</strong> = nilai L0–L4 Pertemuan 1, kolom <strong>G–K</strong> = Pertemuan 2, dst. hingga Pertemuan 16 (kolom CC).<br>
                                            &nbsp;&nbsp;• Nilai yang valid: <strong>1, 2, 3, atau 4</strong>. Sel kosong berarti tidak dinilai.<br>
                                            Setelah file diisi, unggah kembali via tombol <strong>Import Excel</strong>.
                                            Sistem akan langsung menampilkan tabel kelas tersebut pada pertemuan terakhir yang memiliki data.
                                        </p>
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
                                Menu Laporan Analisis terdiri dari dua submenu: <strong>Laporan Individu</strong> dan <strong>Laporan Kelas</strong>.
                                Hasil laporan dapat diunduh dalam format PDF maupun Excel.
                            </p>
                            <div class="panduan-step-list">
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">1</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Pilih Data &amp; Lihat Detail</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Gunakan filter atau cari nama kelas/siswa yang diinginkan,
                                            lalu klik tombol <strong>Detail</strong> pada kolom Grafik untuk menampilkan grafik perkembangan dan tabel ranking.
                                        </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">2</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Download PDF</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Setelah detail muncul, klik tombol <strong>Download PDF</strong> untuk mengunduh laporan analisis dalam format PDF.
                                            PDF mencakup grafik perkembangan, tabel ranking, dan catatan/rekomendasi siswa (jika ada).
                                        </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">3</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Export Excel (Laporan Kelas)</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Khusus di halaman <strong>Laporan Kelas</strong>, tersedia tombol <strong>Export Excel</strong> di samping tombol Download PDF.
                                            File yang diunduh berformat <strong>TPSR_&lt;NamaKelas&gt;.xlsx</strong> dengan tiga tab:
                                            <strong>Input_TPSR</strong> (data nilai L0–L4 per pertemuan),
                                            <strong>Resume_Karakter</strong> (mapping ke 10 dimensi karakter),
                                            dan <strong>Dashboard_Karakter</strong> (rata-rata karakter seluruh pertemuan).
                                        </p>
                                    </div>
                                </div>
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-number" style="width:28px; height:28px; font-size: 0.85rem;">4</div>
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">Catatan &amp; Rekomendasi Siswa</div>
                                        <p class="panduan-step-text" style="font-size:0.8rem;">
                                            Pada <strong>Laporan Individu</strong>, klik tombol <strong>Catatan</strong> pada tabel untuk mengisi catatan perkembangan dan rekomendasi per siswa.
                                            Catatan hanya dapat diisi jika siswa bersangkutan sudah memiliki data penilaian lengkap untuk seluruh 16 pertemuan,
                                            dan akan otomatis tercetak pada laporan PDF yang diunduh.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between;">
                                <button onclick="showModalTab('modal-tab-penilaian')" class="btn-panduan-prev py-1.5 px-3" style="font-size:0.8rem;">
                                    <i class="fas fa-arrow-left"></i> Sebelumnya
                                </button>
                                <button onclick="showModalTab('modal-tab-troubleshoot')" class="btn-panduan-next py-1.5 px-3" style="font-size:0.8rem;">
                                    Selanjutnya: Troubleshooting <i class="fas fa-arrow-right"></i>
                                </button>
                            </div>
                        </div>

                        {{-- Tab 5: Troubleshooting --}}
                        <div class="tab-pane fade" id="modal-content-troubleshoot" role="tabpanel" aria-labelledby="modal-tab-troubleshoot">
                            <h5 class="font-weight-bold text-dark mb-3">Troubleshooting</h5>
                            <p class="text-secondary mb-3" style="font-size: 0.9rem;">
                                Kumpulan masalah umum yang mungkin ditemui beserta solusinya.
                            </p>
                            <div class="panduan-step-list">

                                {{-- 1 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Lupa Sandi / Tidak Bisa Login
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Klik <strong>Lupa Sandi</strong> di halaman login, lalu masukkan alamat email yang didaftarkan saat registrasi.<br>
                                            <strong>Pastikan:</strong> email yang dimasukkan valid dan sama persis dengan email akun Anda.<br>
                                            Link reset akan dikirim ke inbox — cek juga folder <em>Spam/Junk</em> jika tidak muncul dalam beberapa menit.<br>
                                            Jika email terdaftar tidak dikenali, hubungi Admin sekolah untuk mereset akun Anda.
                                        </p>
                                    </div>
                                </div>

                                {{-- 2 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Import Excel Gagal / "Tidak Ada Data Siswa yang Berhasil Diimport"
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Periksa hal berikut pada file Excel Anda:<br>
                                            • Pastikan file yang diunggah berformat <strong>.xlsx</strong> dan ukurannya tidak melebihi <strong>5 MB</strong>.<br>
                                            • Pastikan Anda mengisi data pada tab <strong>Input_TPSR</strong>, bukan tab lain.<br>
                                            • Sel <strong>B2</strong> wajib diisi nama kelas (contoh: <code>5-A</code>).<br>
                                            • Nama siswa dimulai dari baris 5 kolom A — jangan ada baris kosong di antara data siswa.<br>
                                            • Nilai L0–L4 hanya boleh berisi angka <strong>1, 2, 3, atau 4</strong>. Nilai selain itu (termasuk 5) akan diabaikan.<br>
                                            • Jangan mengganti nama tab atau struktur kolom template.
                                        </p>
                                    </div>
                                </div>

                                {{-- 3 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Peringatan "Kelas Tidak Ditemukan, Otomatis Dibuat" Muncul
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Ini terjadi karena nama kelas di sel <strong>B2</strong> file Excel tidak cocok dengan nama kelas yang sudah ada di sistem.<br>
                                            Sistem akan membuat kelas baru secara otomatis, namun jika ini tidak diinginkan:<br>
                                            • Periksa ejaan dan format nama kelas (contoh: <code>5-A</code> bukan <code>5 A</code> atau <code>5a</code>).<br>
                                            • Nama kelas bersifat <em>case-insensitive</em> tetapi tanda hubung dan spasi tetap diperhatikan.<br>
                                            • Kelas yang terbuat secara tidak sengaja dapat dihapus melalui menu <strong>Manajemen Kelas</strong>.
                                        </p>
                                    </div>
                                </div>

                                {{-- 4 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Tombol "Catatan" Tidak Bisa Diklik / Berwarna Abu-abu
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Tombol Catatan hanya aktif jika siswa sudah memiliki data penilaian untuk <strong>seluruh 16 pertemuan</strong>.<br>
                                            Jika masih abu-abu, pastikan semua pertemuan (1–16) sudah diisi nilainya untuk siswa tersebut melalui menu <strong>Penilaian TPSR</strong>.
                                        </p>
                                    </div>
                                </div>

                                {{-- 5 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Grafik Laporan Kosong / Data Tidak Muncul
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Grafik hanya ditampilkan jika ada data penilaian yang tersimpan.<br>
                                            • Pastikan kelas dan siswa sudah memiliki minimal satu pertemuan yang dinilai.<br>
                                            • Jika baru saja mengimport, coba refresh halaman atau pilih kelas kembali dari dropdown.<br>
                                            • Jika grafik tidak muncul pada PDF, pastikan browser tidak memblokir JavaScript.
                                        </p>
                                    </div>
                                </div>

                                {{-- 6 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Akun Tidak Memiliki Akses ke Fitur Tertentu
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            Akun <strong>Guru</strong> hanya dapat mengakses data sekolah yang terhubung dengan akunnya.<br>
                                            Jika muncul pesan <em>"Akun belum terhubung dengan sekolah"</em>, hubungi Admin untuk menautkan akun Anda ke sekolah yang sesuai.<br>
                                            Akun <strong>Admin</strong> memiliki akses penuh ke semua data sekolah, kelas, guru, dan siswa.
                                        </p>
                                    </div>
                                </div>

                                {{-- 7 --}}
                                <div class="panduan-step-card py-2 px-3 mb-2">
                                    <div class="panduan-step-content">
                                        <div class="panduan-step-title" style="font-size:0.9rem;">
                                            <i class="fas fa-exclamation-circle text-warning mr-1"></i>
                                            Data Siswa / Kelas Tidak Muncul di Daftar
                                        </div>
                                        <p class="panduan-step-text" style="font-size:0.8rem; line-height:1.7;">
                                            • Pastikan data sudah ditambahkan melalui menu <strong>Manajemen Kelas</strong> atau via Import.<br>
                                            • Coba hapus teks pada kolom pencarian (search) karena filter aktif dapat menyembunyikan data.<br>
                                            • Reload halaman jika data baru tidak langsung terlihat setelah proses import selesai.
                                        </p>
                                    </div>
                                </div>

                            </div>

                            <div class="panduan-navigation-footer mt-3" style="margin-top: 1.5rem; padding-top: 1rem; display: flex; justify-content: space-between;">
                                <button onclick="showModalTab('modal-tab-laporan')" class="btn-panduan-prev py-1.5 px-3" style="font-size:0.8rem;">
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