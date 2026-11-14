<?php

declare(strict_types=1);

// Semua class dimuat sebelum session_start agar objek di session bisa dipulihkan.
require_once __DIR__ . '/DataLaundry.php';

const VERSI_DATA = 2;

session_name('laundry_uts_pbo');
session_start();

function aman(mixed $teks): string
{
    return htmlspecialchars((string) $teks, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function rp(int $angka): string
{
    return 'Rp' . number_format($angka, 0, ',', '.');
}

if (!isset($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

// Data session versi lama otomatis diganti dengan data awal yang baru.
if (($_SESSION['versi'] ?? 0) !== VERSI_DATA || !($_SESSION['repo'] ?? null) instanceof DataLaundry) {
    $_SESSION['repo'] = DataLaundry::contoh();
    $_SESSION['versi'] = VERSI_DATA;
}

$repo = $_SESSION['repo'];
$daftarPromo = $repo->semua('promo');
$promoLangganan = $daftarPromo ? reset($daftarPromo) : null;
$targetPoin = $promoLangganan ? $promoLangganan->getMinPoin() : 3;
$pesan = $_SESSION['flash'] ?? '';
unset($_SESSION['flash']);
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (!hash_equals($_SESSION['csrf'], (string) ($_POST['csrf'] ?? ''))) {
            throw new InvalidArgumentException('Token formulir tidak valid. Muat ulang halaman.');
        }

        $aksi = (string) ($_POST['aksi'] ?? '');

        if ($aksi === 'tambah_pelanggan') {
            // Constructor dan setter melakukan validasi; error dari sana ditampilkan ke pengguna.
            $repo->tambah('pelanggan', new Pelanggan(
                $repo->idBerikutnya('pelanggan'),
                (string) ($_POST['nama'] ?? ''),
                (string) ($_POST['no_hp'] ?? ''),
                (string) ($_POST['alamat'] ?? ''),
                (string) ($_POST['email'] ?? ''),
                date('Y-m-d')
            ));
            $notif = 'Pelanggan berhasil ditambahkan.';
        } elseif ($aksi === 'hapus_pelanggan') {
            if (!$repo->hapus('pelanggan', (int) ($_POST['id'] ?? 0))) {
                throw new InvalidArgumentException('Pelanggan tidak ditemukan.');
            }
            $notif = 'Pelanggan dihapus.';
        } elseif ($aksi === 'buat_transaksi') {
            $pelanggan = $repo->cari('pelanggan', (int) ($_POST['pelanggan'] ?? 0));
            $layanan = $repo->cari('layanan', (int) ($_POST['layanan'] ?? 0));
            $metode = $repo->cari('metode', (int) ($_POST['metode'] ?? 0));
            $kasir = $repo->cari('kasir', (int) ($_POST['kasir'] ?? 0));
            $jumlah = filter_var($_POST['jumlah'] ?? '', FILTER_VALIDATE_FLOAT);

            if (!$pelanggan) {
                throw new InvalidArgumentException('Pilih pelanggan terlebih dahulu.');
            }

            if (!$layanan || !$metode || !$kasir) {
                throw new InvalidArgumentException('Layanan, kasir, dan metode pembayaran wajib dipilih.');
            }

            if ($jumlah === false) {
                throw new InvalidArgumentException('Jumlah harus berupa angka.');
            }

            // Paket hemat dipakai otomatis jika ada dan jumlahnya memenuhi syarat.
            $paketDipakai = null;
            foreach ($repo->semua('paket') as $paket) {
                if ($paket->getLayanan()->getId() === $layanan->getId() && $paket->memenuhiSyarat($jumlah)) {
                    $paketDipakai = $paket;
                }
            }

            $transaksi = new Transaksi(
                $repo->idBerikutnya('transaksi'),
                $pelanggan,
                $kasir,
                $metode,
                date('Y-m-d'),
                (string) ($_POST['jenis_paket'] ?? ''),
                (string) ($_POST['pengambilan'] ?? '')
            );
            $transaksi->tambahDetail(new DetailTransaksi(1, $layanan, $jumlah, $paketDipakai));

            // Promo langganan otomatis jika poin pelanggan cukup.
            $diskon = false;
            if ($promoLangganan !== null && $promoLangganan->layakUntuk($pelanggan->getPoin())) {
                $transaksi->terapkanPromo($promoLangganan);
                $diskon = true;
            }

            $repo->tambah('transaksi', $transaksi);
            $notif = 'Transaksi #' . $transaksi->getId() . ' berhasil dibuat.'
                . ($diskon ? ' Diskon langganan diterapkan.' : '');
        } elseif ($aksi === 'ubah_status') {
            $transaksi = $repo->cari('transaksi', (int) ($_POST['id'] ?? 0));

            if (!$transaksi) {
                throw new InvalidArgumentException('Transaksi tidak ditemukan.');
            }

            $transaksi->ubahStatus((string) ($_POST['status'] ?? ''));
            $notif = 'Status diubah menjadi ' . $transaksi->getStatus() . '.';
        } elseif ($aksi === 'reset') {
            $_SESSION['repo'] = DataLaundry::contoh();
            $notif = 'Data dikembalikan ke data awal.';
        } else {
            throw new InvalidArgumentException('Aksi tidak dikenal.');
        }

        $_SESSION['flash'] = $notif;
        header('Location: index.php');
        exit;
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$csrf = aman($_SESSION['csrf']);
$rekap = $repo->rekapPerKasir();
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= aman($repo->getNamaUsaha()) ?> - UTS PBO</title>
<style>
    *{box-sizing:border-box}
    body{font-family:system-ui,Arial,sans-serif;background:#f1f5f9;color:#15324d;margin:0}
    main{max-width:1200px;margin:30px auto;padding:0 16px}
    .panel{background:#fff;border-radius:12px;padding:20px;margin:18px 0;box-shadow:0 4px 14px #16324d14}
    .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px}
    label{font-weight:600;font-size:13px;display:block}
    input,select{display:block;width:100%;padding:9px;margin-top:5px;border:1px solid #c8d5e3;border-radius:7px;font:inherit}
    button{border:0;border-radius:7px;background:#176397;color:#fff;padding:8px 14px;cursor:pointer;font-size:13px}
    .danger{background:#b9414a}
    .muted{color:#60758a;font-size:13px}
    table{border-collapse:collapse;width:100%;font-size:13px}
    th,td{padding:10px 8px;text-align:left;border-bottom:1px solid #e1e8f0;vertical-align:top}
    th{background:#e8f1f8}
    .scroll{overflow-x:auto}
    .notice{padding:12px;border-radius:8px;background:#e3f4e9}
    .err{background:#ffe8e8}
    .total td{font-weight:700;background:#f4f8fc}
    .kanan{text-align:right}
    form.inline{display:inline;margin:0}
</style>
</head>
<body>
<main>
<h1><?= aman($repo->getNamaUsaha()) ?></h1>
<p class="muted">UTS PBO: Inheritance (Orang → Pelanggan/Kasir), enkapsulasi, dan komposisi. Data disimpan di PHP Session, bukan database.</p>

<?php if ($pesan): ?><div class="notice"><?= aman($pesan) ?></div><?php endif; ?>
<?php if ($error): ?><div class="notice err"><?= aman($error) ?></div><?php endif; ?>

<section class="panel">
<h2>Pelanggan (<?= count($repo->semua('pelanggan')) ?>)</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="hidden" name="aksi" value="tambah_pelanggan">
<div class="grid">
<label>Nama<input name="nama" required placeholder="Masukkan nama lengkap"></label>
<label>No. HP<input name="no_hp" required placeholder="Contoh: 081234567890"></label>
<label>Alamat<input name="alamat" required placeholder="Masukkan alamat lengkap"></label>
<label>Email (opsional)<input name="email" type="text" placeholder="nama@email.com"></label>
</div>
<p><button>Tambah Pelanggan</button></p>
</form>
<div class="scroll"><table>
<thead><tr><th>Ringkasan</th><th>Alamat</th><th>Poin Langganan</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($repo->semua('pelanggan') as $p): ?>
<tr>
<td><?= aman($p->ringkasan()) ?></td>
<td><?= aman($p->getAlamat()) ?></td>
<td><?= $p->getPoin() ?> / <?= $targetPoin ?><?= $p->getPoin() >= $targetPoin ? ' <strong>(dapat diskon)</strong>' : '' ?></td>
<td>
<form class="inline" method="post" onsubmit="return confirm('Hapus pelanggan ini?')">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="hidden" name="aksi" value="hapus_pelanggan">
<input type="hidden" name="id" value="<?= $p->getId() ?>">
<button class="danger">Hapus</button>
</form>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$repo->semua('pelanggan')): ?><tr><td colspan="4">Belum ada pelanggan. Tambahkan pelanggan terlebih dahulu.</td></tr><?php endif; ?>
</tbody></table></div>
</section>

<section class="panel">
<h2>Transaksi Baru</h2>
<form method="post">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="hidden" name="aksi" value="buat_transaksi">
<div class="grid">
<label>Pelanggan
<select name="pelanggan" required>
<option value="" disabled selected>-- Pilih pelanggan --</option>
<?php foreach ($repo->semua('pelanggan') as $p): ?>
<option value="<?= $p->getId() ?>"><?= aman($p->getNama()) ?> (<?= $p->getPoin() ?>/<?= $targetPoin ?> poin)</option>
<?php endforeach; ?>
</select></label>
<label>Layanan
<select name="layanan" required>
<?php foreach ($repo->semua('layanan') as $l): ?>
<option value="<?= $l->getId() ?>"><?= aman($l->ringkasan()) ?></option>
<?php endforeach; ?>
</select></label>
<label>Jumlah
<input name="jumlah" type="number" step="0.1" min="0.1" value="1" required></label>
<label>Jenis Paket
<select name="jenis_paket" required>
<option value="Reguler">Reguler (harga normal)</option>
<option value="Ekspres">Ekspres (+<?= Transaksi::PERSEN_EKSPRES ?>%, selesai <?= Transaksi::HARI_EKSPRES ?> hari)</option>
</select></label>
<label>Kasir
<select name="kasir" required>
<?php foreach ($repo->semua('kasir') as $k): ?>
<option value="<?= $k->getId() ?>"><?= aman($k->getNama()) ?> (Shift <?= aman($k->getShift()) ?>)</option>
<?php endforeach; ?>
</select></label>
<label>Metode Pembayaran
<select name="metode" required>
<?php foreach ($repo->semua('metode') as $m): ?>
<option value="<?= $m->getId() ?>"><?= aman($m->getNama()) ?> (admin <?= rp($m->getBiayaAdmin()) ?>)</option>
<?php endforeach; ?>
</select></label>
<label>Pengambilan
<select name="pengambilan" required>
<option value="Datang langsung">Datang langsung (gratis)</option>
<option value="Diantar">Diantar (+<?= rp(Transaksi::BIAYA_ANTAR) ?>)</option>
</select></label>
</div>
<p><button>Buat Transaksi</button></p>
</form>
<p class="muted">Diskon langganan <?= $promoLangganan ? $promoLangganan->getPersen() : 10 ?>% otomatis untuk pelanggan dengan <?= $targetPoin ?> poin (poin terpakai habis). Paket hemat dipakai otomatis jika jumlahnya memenuhi syarat.</p>
</section>

<section class="panel">
<h2>Daftar Transaksi (<?= count($repo->semua('transaksi')) ?>)</h2>
<div class="scroll"><table>
<thead><tr><th>#</th><th>Tanggal</th><th>Pelanggan</th><th>Item</th><th>Jenis Paket</th><th>Pengambilan</th><th>Estimasi Selesai</th><th>Subtotal</th><th>Potongan</th><th>Biaya Antar</th><th>Total Bayar</th><th>Status</th><th>Aksi</th></tr></thead>
<tbody>
<?php foreach ($repo->semua('transaksi') as $t): ?>
<tr>
<td><?= $t->getId() ?></td>
<td><?= aman($t->getTanggal()) ?></td>
<td><?= aman($t->getPelanggan()->getNama()) ?><br><span class="muted">Kasir: <?= aman($t->getKasir()->getNama()) ?></span></td>
<td><?php foreach ($t->getDaftarDetail() as $d): ?><?= aman($d->ringkasan()) ?><br><?php endforeach; ?></td>
<td><?= aman($t->getJenisPaket()) ?></td>
<td><?= aman($t->getPengambilan()) ?></td>
<td><?= aman($t->perkiraanSelesai()->format('Y-m-d')) ?></td>
<td><?= rp($t->hitungSubtotal()) ?><?php if ($t->hitungBiayaEkspres() > 0): ?><br><span class="muted">Ekspres +<?= rp($t->hitungBiayaEkspres()) ?></span><?php endif; ?></td>
<td><?= rp($t->hitungPotongan()) ?><?php if ($t->getPoinDipakai() > 0): ?><br><span class="muted"><?= $t->getPoinDipakai() ?> poin dipakai</span><?php endif; ?></td>
<td><?= rp($t->hitungBiayaAntar()) ?></td>
<td><?= rp($t->hitungTotalBayar()) ?><br><span class="muted"><?= aman($t->getMetode()->getNama()) ?><?= $t->getMetode()->getBiayaAdmin() > 0 ? ' (admin ' . rp($t->getMetode()->getBiayaAdmin()) . ')' : '' ?></span></td>
<td><?= aman($t->getStatus()) ?></td>
<td>
<?php foreach (Transaksi::ALUR_STATUS[$t->getStatus()] as $berikut): ?>
<form class="inline" method="post">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="hidden" name="aksi" value="ubah_status">
<input type="hidden" name="id" value="<?= $t->getId() ?>">
<input type="hidden" name="status" value="<?= aman($berikut) ?>">
<button class="<?= $berikut === 'Batal' ? 'danger' : '' ?>"><?= aman($berikut) ?></button>
</form>
<?php endforeach; ?>
</td>
</tr>
<?php endforeach; ?>
<?php if (!$repo->semua('transaksi')): ?><tr><td colspan="13">Belum ada transaksi.</td></tr><?php endif; ?>
</tbody></table></div>
</section>

<section class="panel">
<h2>Kasir dan Rekap Pendapatan</h2>
<div class="scroll"><table>
<thead><tr><th>Kasir</th><th>Shift</th><th>Jam Kerja</th><th class="kanan">Jml Transaksi</th><th class="kanan">Jml Pelanggan</th><th class="kanan">Total Pendapatan</th></tr></thead>
<tbody>
<?php
$totalTransaksi = 0;
$totalPendapatan = 0;
foreach ($rekap as $baris):
    $totalTransaksi += $baris['jumlahTransaksi'];
    $totalPendapatan += $baris['pendapatan'];
?>
<tr>
<td><?= aman($baris['kasir']->getNama()) ?></td>
<td><?= aman($baris['kasir']->getShift()) ?></td>
<td><?= aman($baris['kasir']->getJamKerja()) ?></td>
<td class="kanan"><?= $baris['jumlahTransaksi'] ?></td>
<td class="kanan"><?= $baris['jumlahPelanggan'] ?></td>
<td class="kanan"><?= rp($baris['pendapatan']) ?></td>
</tr>
<?php endforeach; ?>
<tr class="total">
<td colspan="3">Total semua shift</td>
<td class="kanan"><?= $totalTransaksi ?></td>
<td class="kanan">-</td>
<td class="kanan"><?= rp($totalPendapatan) ?></td>
</tr>
</tbody></table></div>
<p class="muted">Transaksi yang dibatalkan tidak dihitung. Pendapatan tidak termasuk biaya admin pembayaran.</p>
<p class="muted">Data dibuat <?= aman($repo->getDibuatPada()) ?> · diubah terakhir <?= aman($repo->getTerakhirDiubah()) ?> · <?= $repo->getJumlahOperasi() ?> operasi data.</p>
<form method="post" onsubmit="return confirm('Kembalikan semua data ke awal?')">
<input type="hidden" name="csrf" value="<?= $csrf ?>">
<input type="hidden" name="aksi" value="reset">
<button class="danger">Reset Data</button>
</form>
</section>
</main>
</body>
</html>