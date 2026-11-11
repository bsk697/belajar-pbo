<?php
require_once __DIR__ . '/PegawaiTetap.php';
require_once __DIR__ . '/PegawaiKontrak.php';

header('Content-Type: text/plain; charset=UTF-8');

$daftar = [
    new PegawaiTetap('P001', 'Budi', 6000000),
    new PegawaiKontrak('P002', 'Dewi', 50000, 120),
];

foreach ($daftar as $p) {
    echo $p->getId() . ' / ' . $p->getNama() . ' / ' . $p->getJenis()
       . ' / Rp' . number_format($p->hitungGaji(), 0, ',', '.') . PHP_EOL;
}

// Uji validasi
try {
    new PegawaiTetap('P003', 'Eko', -1);
} catch (InvalidArgumentException $e) {
    echo 'Ditolak: ' . $e->getMessage() . PHP_EOL;
}