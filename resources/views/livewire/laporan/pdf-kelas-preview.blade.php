{{-- Template PDF Laporan Kelas --}}
<div id="laporan-kelas-pdf-content" style="
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
    <h2 style="text-align:center;font-size:18px;font-weight:bold;margin:0 0 5px 0;">Laporan Analisis Kelas</h2>
    <h3 style="text-align:center;font-size:14px;font-weight:normal;margin:0 0 20px 0;padding-bottom:8px;border-bottom:2px solid #000;">
        Kelas {{ $kelas->nama ?? '-' }}
    </h3>

    {{-- Info Kelas --}}
    <table style="width:100%;margin-bottom:18px;border-collapse:collapse;font-size:13px;line-height:2;">
        <tr>
            <td style="width:90px;font-weight:bold;vertical-align:top;">Sekolah</td>
            <td style="vertical-align:top;width:10px;">:</td>
            <td id="pdf-kelas-sekolah" style="vertical-align:top;">{{ $sekolahNama }}</td>
        </tr>
        <tr>
            <td style="font-weight:bold;vertical-align:top;">Pengajar</td>
            <td style="vertical-align:top;">:</td>
            <td id="pdf-kelas-pengajar" style="vertical-align:top;">{{ $pengajar }}</td>
        </tr>
    </table>

    {{-- Grafik --}}
    <div style="text-align:center;margin:0 0 4px 0;">
        <img id="laporan-kelas-pdf-chart-img" src="" alt="Grafik Perkembangan Kelas"
            style="width:100%;max-width:460px;height:180px;object-fit:fill;border:1px solid #ccc;display:inline-block;" />
    </div>
    <p style="text-align:center;font-size:12px;margin:2px 0 18px 0;">Grafik Perkembangan Kelas</p>

    {{-- Tabel Ranking Siswa --}}
    <p style="font-size:13px;font-weight:bold;margin:0 0 5px 0;">Tabel Ranking Siswa</p>
    <table id="pdf-kelas-ranking-table" style="width:100%;border-collapse:collapse;font-size:10px;margin-bottom:14px;table-layout:fixed;">
        <thead>
            <tr>
                <th style="border:1px solid #333;text-align:center;padding:5px 3px;background:#f3f4f6;font-weight:bold;width:28px;">No</th>
                <th style="border:1px solid #333;text-align:left;padding:5px 6px;background:#f3f4f6;font-weight:bold;">Nama Siswa</th>
                <th style="border:1px solid #333;text-align:center;padding:5px 3px;background:#f3f4f6;font-weight:bold;width:75px;">Rata-rata</th>
                <th style="border:1px solid #333;text-align:center;padding:5px 3px;background:#f3f4f6;font-weight:bold;width:90px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($siswaList as $index => $siswa)
                <tr>
                    <td style="border:1px solid #333;text-align:center;padding:5px 3px;">{{ $index + 1 }}</td>
                    <td style="border:1px solid #333;text-align:left;padding:5px 6px;">{{ $siswa->nama }}</td>
                    <td style="border:1px solid #333;text-align:center;padding:5px 3px;">
                        {{ $siswa->rata_laporan !== null ? number_format($siswa->rata_laporan, 2, ',', '.') : '-' }}
                    </td>
                    <td style="border:1px solid #333;text-align:center;padding:5px 3px;">{{ $siswa->status_laporan }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Rata-rata Point Kelas --}}
    <div style="font-size:13px;font-weight:bold;margin-bottom:18px;">
        Rata-rata Point Kelas : <span id="pdf-kelas-avg">{{ number_format($rataKelas, 2, ',', '.') }}</span>
    </div>

    {{-- Tabel Catatan & Rekomendasi --}}
    @php
        $siswaDenganCatatan = $siswaList->filter(fn($s) => !empty($s->keterangan) || !empty($s->rekomendasi));
    @endphp
    @if ($siswaDenganCatatan->isNotEmpty())
        <p style="font-size:13px;font-weight:bold;margin:0 0 5px 0;">Catatan & Rekomendasi Siswa</p>
        <table id="pdf-kelas-catatan-table" style="width:100%;border-collapse:collapse;font-size:10px;margin-bottom:14px;">
            <thead>
                <tr>
                    <th style="border:1px solid #333;text-align:center;padding:5px 3px;background:#f3f4f6;font-weight:bold;width:28px;">No</th>
                    <th style="border:1px solid #333;text-align:left;padding:5px 6px;background:#f3f4f6;font-weight:bold;width:110px;">Nama Siswa</th>
                    <th style="border:1px solid #333;text-align:left;padding:5px 6px;background:#f3f4f6;font-weight:bold;">Catatan</th>
                    <th style="border:1px solid #333;text-align:left;padding:5px 6px;background:#f3f4f6;font-weight:bold;">Rekomendasi</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($siswaDenganCatatan->values() as $index => $siswa)
                    <tr>
                        <td style="border:1px solid #333;text-align:center;padding:5px 3px;vertical-align:top;">{{ $index + 1 }}</td>
                        <td style="border:1px solid #333;text-align:left;padding:5px 6px;vertical-align:top;">{{ $siswa->nama }}</td>
                        <td style="border:1px solid #333;text-align:left;padding:5px 6px;vertical-align:top;line-height:1.5;">{{ $siswa->keterangan ?? '-' }}</td>
                        <td style="border:1px solid #333;text-align:left;padding:5px 6px;vertical-align:top;line-height:1.5;">{{ $siswa->rekomendasi ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
