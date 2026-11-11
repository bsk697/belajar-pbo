<?php
// Eksperimen kunci (Bagian G) + uji skenario 01-07 dan 12 (jalankan: php tes_mahasiswa.php)
require_once __DIR__ . '/DataMahasiswa.php';

$daftar = [
    new MahasiswaReguler('23001', 'Andi', 'Informatika', 4, 5000000),
    new MahasiswaBeasiswa('23002', 'Siti', 'Informatika', 4, 5000000, 60),
];
foreach ($daftar as $m) {
    echo $m->getNama() . ' / ' . $m->getJenis()
       . ' / Rp' . number_format($m->hitungBiaya(), 0, ',', '.') . PHP_EOL;
}

echo PHP_EOL . "== Uji validasi ==" . PHP_EOL;
$kasus = [
    '03 NIM kosong'        => fn() => new MahasiswaReguler('', 'A', 'IF', 1, 1000),
    '04 Semester 0'        => fn() => new MahasiswaReguler('1', 'A', 'IF', 0, 1000),
    '04 Semester 15'       => fn() => new MahasiswaReguler('1', 'A', 'IF', 15, 1000),
    '05 UKT -100'          => fn() => new MahasiswaReguler('1', 'A', 'IF', 1, -100),
    '06 Potongan 120%'     => fn() => new MahasiswaBeasiswa('1', 'A', 'IF', 1, 1000, 120),
];
foreach ($kasus as $nama => $fn) {
    try { $fn(); echo "$nama: DITERIMA (tidak sesuai)" . PHP_EOL; }
    catch (InvalidArgumentException $e) { echo "$nama: ditolak - " . $e->getMessage() . PHP_EOL; }
}

$repo = new DataMahasiswa($daftar);
echo '07 NIM duplikat: ' . ($repo->tambah($daftar[0]) ? 'DITERIMA (tidak sesuai)' : 'ditolak') . PHP_EOL;
echo '09 Edit NIM berbeda: ' . ($repo->ubah('23001', $daftar[1]) ? 'DITERIMA (tidak sesuai)' : 'ditolak') . PHP_EOL;