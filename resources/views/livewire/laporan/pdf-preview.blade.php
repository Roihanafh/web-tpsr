{{-- Template PDF Laporan Individu --}}
<div id="laporan-pdf-content" style="
    font-family: Arial, Helvetica, sans-serif;
    color: #000;
    background: #fff;
    width: 170mm;
    padding: 0;
    margin: 0;
    box-sizing: border-box;
    page-break-inside: avoid;
">
    {{-- Judul --}}
    <h2 style="
        text-align: center;
        font-size: 18px;
        font-weight: bold;
        margin: 0 0 20px 0;
        padding-bottom: 8px;
        border-bottom: 2px solid #000;
    ">Laporan Analisis Individu</h2>

    {{-- Info Siswa --}}
    <table style="width:100%; margin-bottom:18px; border-collapse:collapse; font-size:13px; line-height:2;">
        <tr>
            <td style="width:90px; font-weight:bold; vertical-align:top;">Nama</td>
            <td style="vertical-align:top; width:10px;">:</td>
            <td id="pdf-siswa-nama" style="vertical-align:top;">{{ $siswa->nama }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; vertical-align:top;">Kelas</td>
            <td style="vertical-align:top;">:</td>
            <td id="pdf-siswa-kelas" style="vertical-align:top;">{{ $siswa->kelas?->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; vertical-align:top;">Pengajar</td>
            <td style="vertical-align:top;">:</td>
            <td id="pdf-siswa-pengajar" style="vertical-align:top;">{{ $pengajar }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; vertical-align:top;">Sekolah</td>
            <td style="vertical-align:top;">:</td>
            <td id="pdf-siswa-sekolah" style="vertical-align:top;">{{ $sekolahNama }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold; vertical-align:top;">Tahun Ajar</td>
            <td style="vertical-align:top;">:</td>
            <td id="pdf-siswa-tahun-ajar" style="vertical-align:top;">{{ $siswa->kelas?->tahunAjar?->nama ?? '-' }}</td>
        </tr>
    </table>

    {{-- Grafik --}}
    <div style="text-align:center; margin:0 0 4px 0;">
        <img
            id="laporan-pdf-chart-img"
            src=""
            alt="Grafik Penilaian"
            style="width:100%; max-width:460px; height:200px; object-fit:fill; border:1px solid #ccc; display:inline-block;"
        />
    </div>
    <p style="text-align:center; font-size:12px; margin:2px 0 18px 0;">Grafik Penilaian</p>

    {{-- Tabel Jumlah Perolehan --}}
    <p style="font-size:13px; font-weight:bold; margin:0 0 5px 0;">Jumlah Perolehan</p>
    <table style="width:100%; border-collapse:collapse; font-size:12px; margin-bottom:14px; table-layout:fixed;">
        <thead>
            <tr>
                @for ($lvl = 0; $lvl <= 5; $lvl++)
                    <th style="
                        border: 1px solid #333;
                        text-align: center;
                        padding: 6px 4px;
                        background: #f3f4f6;
                        font-weight: bold;
                    ">L{{ $lvl }}</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            <tr>
                @for ($lvl = 0; $lvl <= 5; $lvl++)
                    <td id="pdf-siswa-lvl-{{ $lvl }}" style="
                        border: 1px solid #333;
                        text-align: center;
                        padding: 6px 4px;
                    ">{{ $levelCount[$lvl] ?? 0 }}</td>
                @endfor
            </tr>
        </tbody>
    </table>

    {{-- Rata-rata dan Status --}}
    <table style="width:100%; border-collapse:collapse; font-size:13px; line-height:1.9;">
        <tr>
            <td style="font-weight:bold; width:130px;">Rata-rata Point</td>
            <td style="width:10px;">:</td>
            <td id="pdf-siswa-rata">{{ number_format($rataLaporan, 2) }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;">Status</td>
            <td>:</td>
            <td id="pdf-siswa-status" style="font-weight:bold;">{{ $status }}</td>
        </tr>
    </table>
</div>
