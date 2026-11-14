<?php

declare(strict_types=1);

require_once __DIR__ . '/Pelanggan.php';
require_once __DIR__ . '/Kasir.php';
require_once __DIR__ . '/Layanan.php';
require_once __DIR__ . '/Paket.php';
require_once __DIR__ . '/Promo.php';
require_once __DIR__ . '/MetodePembayaran.php';
require_once __DIR__ . '/Transaksi.php';

// Repository: menyimpan semua objek di array (disimpan di PHP Session, bukan database).
class DataLaundry
{
    private const KELAS = [
        'pelanggan' => Pelanggan::class,
        'kasir'     => Kasir::class,
        'layanan'   => Layanan::class,
        'paket'     => Paket::class,
        'promo'     => Promo::class,
        'metode'    => MetodePembayaran::class,
        'transaksi' => Transaksi::class,
    ];

    private array $data = [];
    private string $namaUsaha;
    private string $dibuatPada;
    private string $terakhirDiubah;
    private int $jumlahOperasi = 0;

    public function __construct(string $namaUsaha = 'Laundry Kita')
    {
        $namaUsaha = trim($namaUsaha);

        if ($namaUsaha === '' || mb_strlen($namaUsaha) > 60) {
            throw new InvalidArgumentException('Nama usaha wajib diisi (maks. 60 karakter)');
        }

        foreach (array_keys(self::KELAS) as $jenis) {
            $this->data[$jenis] = [];
        }

        $this->namaUsaha = $namaUsaha;
        $this->dibuatPada = date('Y-m-d H:i:s');
        $this->terakhirDiubah = $this->dibuatPada;
    }

    // Data awal agar aplikasi langsung bisa dicoba. Pelanggan sengaja kosong.
    public static function contoh(): self
    {
        $repo = new self();

        // Empat kasir, satu per shift (nomor HP dan alamat hanya contoh).
        $repo->tambah('kasir', new Kasir(1, 'Azizah', '081300000001', 'Jl. Contoh No. 1, Sukabumi', '', 'azizah', 'Pagi', '2026-10-01', 2500000));
        $repo->tambah('kasir', new Kasir(2, 'Rga', '081300000002', 'Jl. Contoh No. 2, Sukabumi', '', 'rga', 'Siang', '2026-10-01', 2500000));
        $repo->tambah('kasir', new Kasir(3, 'Gian', '081300000003', 'Jl. Contoh No. 3, Sukabumi', '', 'gian', 'Sore', '2026-10-01', 2500000));
        $repo->tambah('kasir', new Kasir(4, 'Fauji', '081300000004', 'Jl. Contoh No. 4, Sukabumi', '', 'fauji', 'Malam', '2026-10-01', 2500000));

        $cuci = new Layanan(1, 'Cuci Kiloan', 'Kg', 7000, 2);
        $repo->tambah('layanan', $cuci);
        $repo->tambah('layanan', new Layanan(2, 'Cuci + Setrika', 'Kg', 9000, 3));
        $repo->tambah('layanan', new Layanan(3, 'Cuci Selimut', 'Pcs', 25000, 3));

        $repo->tambah('paket', new Paket(1, 'Hemat 5 Kg', $cuci, 5, 10));

        // Promo langganan: poin 3 -> diskon 10% (maks. Rp15.000), poin terpakai habis.
        $repo->tambah('promo', new Promo(1, 'LANGGANAN', 'Promo Langganan', 10, 15000, 0, 3, '2026-01-01', '2026-12-31'));

        // Nomor rekening dan e-wallet hanya contoh.
        $repo->tambah('metode', new MetodePembayaran(1, 'Tunai', 'Tunai'));
        $repo->tambah('metode', new MetodePembayaran(2, 'QRIS', 'QRIS'));
        $repo->tambah('metode', new MetodePembayaran(3, 'Transfer BCA', 'Transfer', 2500, '1234567890'));
        $repo->tambah('metode', new MetodePembayaran(4, 'Transfer BRI', 'Transfer', 2500, '2345678901'));
        $repo->tambah('metode', new MetodePembayaran(5, 'Transfer Mandiri', 'Transfer', 2500, '3456789012'));
        $repo->tambah('metode', new MetodePembayaran(6, 'Transfer BNI', 'Transfer', 2500, '4567890123'));
        $repo->tambah('metode', new MetodePembayaran(7, 'GoPay', 'E-Wallet', 0, '081300000010'));
        $repo->tambah('metode', new MetodePembayaran(8, 'OVO', 'E-Wallet', 0, '081300000011'));
        $repo->tambah('metode', new MetodePembayaran(9, 'DANA', 'E-Wallet', 0, '081300000012'));
        $repo->tambah('metode', new MetodePembayaran(10, 'ShopeePay', 'E-Wallet', 0, '081300000013'));

        return $repo;
    }

    // ===== GETTER =====

    public function getNamaUsaha(): string
    {
        return $this->namaUsaha;
    }

    public function getDibuatPada(): string
    {
        return $this->dibuatPada;
    }

    public function getTerakhirDiubah(): string
    {
        return $this->terakhirDiubah;
    }

    public function getJumlahOperasi(): int
    {
        return $this->jumlahOperasi;
    }

    // ===== OPERASI DATA =====

    private function cekJenis(string $jenis): void
    {
        if (!isset(self::KELAS[$jenis])) {
            throw new InvalidArgumentException('Jenis data tidak dikenal: ' . $jenis);
        }
    }

    private function catatPerubahan(): void
    {
        $this->jumlahOperasi++;
        $this->terakhirDiubah = date('Y-m-d H:i:s');
    }

    public function tambah(string $jenis, object $objek): void
    {
        $this->cekJenis($jenis);

        $kelas = self::KELAS[$jenis];

        if (!($objek instanceof $kelas)) {
            throw new InvalidArgumentException('Objek bukan ' . $kelas);
        }

        $id = $objek->getId();

        if (isset($this->data[$jenis][$id])) {
            throw new InvalidArgumentException('ID ' . $id . ' sudah dipakai');
        }

        $this->data[$jenis][$id] = $objek;
        $this->catatPerubahan();
    }

    public function semua(string $jenis): array
    {
        $this->cekJenis($jenis);

        return $this->data[$jenis];
    }

    public function cari(string $jenis, int $id): ?object
    {
        $this->cekJenis($jenis);

        return $this->data[$jenis][$id] ?? null;
    }

    public function hapus(string $jenis, int $id): bool
    {
        $this->cekJenis($jenis);

        if (!isset($this->data[$jenis][$id])) {
            return false;
        }

        unset($this->data[$jenis][$id]);
        $this->catatPerubahan();

        return true;
    }

    public function idBerikutnya(string $jenis): int
    {
        $this->cekJenis($jenis);

        return $this->data[$jenis] === [] ? 1 : max(array_keys($this->data[$jenis])) + 1;
    }

    public function cariPromoByKode(string $kode): ?Promo
    {
        foreach ($this->data['promo'] as $promo) {
            if ($promo->getKode() === strtoupper(trim($kode))) {
                return $promo;
            }
        }

        return null;
    }

    // ===== REKAP PER KASIR (= per shift) =====
    // Transaksi Batal tidak dihitung. Pendapatan = total transaksi tanpa biaya admin.

    public function rekapPerKasir(): array
    {
        $rekap = [];

        foreach ($this->data['kasir'] as $id => $kasir) {
            $rekap[$id] = [
                'kasir'           => $kasir,
                'jumlahTransaksi' => 0,
                'jumlahPelanggan' => 0,
                'pendapatan'      => 0,
            ];
        }

        $pelangganUnik = [];

        foreach ($this->data['transaksi'] as $transaksi) {
            if ($transaksi->getStatus() === 'Batal') {
                continue;
            }

            $idKasir = $transaksi->getKasir()->getId();

            if (!isset($rekap[$idKasir])) {
                continue;
            }

            $rekap[$idKasir]['jumlahTransaksi']++;
            $rekap[$idKasir]['pendapatan'] += $transaksi->hitungTotal();
            $pelangganUnik[$idKasir][$transaksi->getPelanggan()->getId()] = true;
        }

        foreach ($pelangganUnik as $idKasir => $daftar) {
            $rekap[$idKasir]['jumlahPelanggan'] = count($daftar);
        }

        return $rekap;
    }
}
