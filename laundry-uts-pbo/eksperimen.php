<?php

declare(strict_types=1);

require_once __DIR__ . '/Pelanggan.php';
require_once __DIR__ . '/Kasir.php';

// Instansiasi: dari kelas menjadi objek.
$daftar = [
    new Pelanggan(1, 'Budi Santoso', '081234567890', 'Jl. Merdeka No. 10, Sukabumi', 'budi@example.com', '2026-11-01'),
    new Kasir(1, 'Andi Pratama', '081311112222', 'Jl. Veteran No. 3, Sukabumi', 'andi@example.com', 'andi', 'Pagi', '2026-10-01', 2500000),
];

echo '<h3>1. Satu method, hasil berbeda (overriding)</h3>';
foreach ($daftar as $orang) {
    echo $orang->ringkasan() . '<br>';
}

echo '<h3>2. Validasi lewat setter</h3>';
try {
    $daftar[0]->setNoHp('abc');
} catch (InvalidArgumentException $e) {
    echo 'setNoHp("abc") ditolak: ' . $e->getMessage() . '<br>';
}

echo '<h3>3. Operasi bisnis</h3>';
$daftar[0]->tambahPoin(20);
echo 'Poin pelanggan setelah tambahPoin(20): ' . $daftar[0]->getPoin() . '<br>';
$daftar[1]->naikkanGaji(10);
echo 'Gaji kasir setelah naik 10%: Rp' . number_format($daftar[1]->getGajiPokok(), 0, ',', '.') . '<br>';

echo '<h3>4. Akses langsung ke atribut private</h3>';
try {
    echo $daftar[0]->poin;
} catch (Error $e) {
    echo $e->getMessage() . '<br>';
}