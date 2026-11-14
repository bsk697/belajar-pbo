<?php

declare(strict_types=1);

require_once __DIR__ . '/Pelanggan.php';
require_once __DIR__ . '/Kasir.php';
require_once __DIR__ . '/DetailTransaksi.php';
require_once __DIR__ . '/Promo.php';
require_once __DIR__ . '/MetodePembayaran.php';

// Transaksi laundry: menggabungkan Pelanggan, Kasir, Detail, Promo, dan Metode (komposisi).
class Transaksi
{
    public const ALUR_STATUS = [
        'Diterima' => ['Diproses', 'Batal'],
        'Diproses' => ['Selesai', 'Batal'],
        'Selesai'  => ['Diambil'],
        'Diambil'  => [],
        'Batal'    => [],
    ];

    public const JENIS_PAKET = ['Reguler', 'Ekspres'];
    public const PENGAMBILAN = ['Datang langsung', 'Diantar'];

    public const PERSEN_EKSPRES = 50;   // Ekspres: harga layanan naik 50%
    public const HARI_EKSPRES = 1;      // Ekspres selesai dalam 1 hari
    public const BIAYA_ANTAR = 5000;    // Biaya antar tetap (Rp)

    private int $id;
    private Pelanggan $pelanggan;
    private Kasir $kasir;
    private MetodePembayaran $metode;
    private ?Promo $promo = null;
    private string $tanggal;
    private string $status = 'Diterima';
    private string $jenisPaket;
    private string $pengambilan;
    private int $poinDipakai = 0;
    /** @var DetailTransaksi[] */
    private array $daftarDetail = [];

    public function __construct(
        int $id,
        Pelanggan $pelanggan,
        Kasir $kasir,
        MetodePembayaran $metode,
        string $tanggal,
        string $jenisPaket = 'Reguler',
        string $pengambilan = 'Datang langsung'
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID transaksi harus lebih dari 0');
        }

        $tgl = DateTime::createFromFormat('Y-m-d', $tanggal);

        if ($tgl === false || $tgl->format('Y-m-d') !== $tanggal) {
            throw new InvalidArgumentException('Tanggal harus berformat YYYY-MM-DD');
        }

        if (!in_array($jenisPaket, self::JENIS_PAKET, true)) {
            throw new InvalidArgumentException('Jenis paket harus Reguler atau Ekspres');
        }

        if (!in_array($pengambilan, self::PENGAMBILAN, true)) {
            throw new InvalidArgumentException('Pengambilan harus Datang langsung atau Diantar');
        }

        if (!$pelanggan->isAktif()) {
            throw new InvalidArgumentException('Pelanggan tidak aktif');
        }

        if (!$kasir->isAktif()) {
            throw new InvalidArgumentException('Kasir tidak aktif');
        }

        if (!$metode->isAktif()) {
            throw new InvalidArgumentException('Metode pembayaran tidak aktif');
        }

        $this->id = $id;
        $this->pelanggan = $pelanggan;
        $this->kasir = $kasir;
        $this->metode = $metode;
        $this->tanggal = $tanggal;
        $this->jenisPaket = $jenisPaket;
        $this->pengambilan = $pengambilan;
    }

    // ===== GETTER =====

    public function getId(): int
    {
        return $this->id;
    }

    public function getPelanggan(): Pelanggan
    {
        return $this->pelanggan;
    }

    public function getKasir(): Kasir
    {
        return $this->kasir;
    }

    public function getMetode(): MetodePembayaran
    {
        return $this->metode;
    }

    public function getPromo(): ?Promo
    {
        return $this->promo;
    }

    public function getTanggal(): string
    {
        return $this->tanggal;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getJenisPaket(): string
    {
        return $this->jenisPaket;
    }

    public function getPengambilan(): string
    {
        return $this->pengambilan;
    }

    public function getPoinDipakai(): int
    {
        return $this->poinDipakai;
    }

    public function getDaftarDetail(): array
    {
        return $this->daftarDetail;
    }

    // ===== OPERASI BISNIS =====
    // Detail, promo, dan status hanya berubah lewat method di bawah, tidak ada setter bebas.

    public function tambahDetail(DetailTransaksi $detail): void
    {
        if ($this->status !== 'Diterima') {
            throw new LogicException('Item hanya bisa ditambah saat status Diterima');
        }

        $this->daftarDetail[] = $detail;
    }

    // Promo langganan: butuh poin cukup, lalu poin dipakai habis.
    public function terapkanPromo(Promo $promo): void
    {
        if ($this->promo !== null) {
            throw new LogicException('Promo sudah diterapkan');
        }

        if (count($this->daftarDetail) === 0) {
            throw new LogicException('Tambahkan item sebelum menerapkan promo');
        }

        if (!$promo->isBerlaku(new DateTime($this->tanggal))) {
            throw new InvalidArgumentException('Promo tidak berlaku pada tanggal transaksi');
        }

        if (!$promo->layakUntuk($this->pelanggan->getPoin())) {
            throw new InvalidArgumentException('Poin pelanggan belum cukup untuk promo ini');
        }

        $this->pelanggan->gunakanPoin($promo->getMinPoin());
        $this->poinDipakai = $promo->getMinPoin();
        $this->promo = $promo;
    }

    public function ubahStatus(string $statusBaru): void
    {
        if (!in_array($statusBaru, self::ALUR_STATUS[$this->status], true)) {
            throw new LogicException('Status ' . $this->status . ' tidak bisa langsung menjadi ' . $statusBaru);
        }

        if ($statusBaru === 'Diproses' && count($this->daftarDetail) === 0) {
            throw new LogicException('Transaksi belum punya item');
        }

        $this->status = $statusBaru;

        // Kartu langganan: pesanan diambil = +1 poin.
        if ($statusBaru === 'Diambil') {
            $this->pelanggan->tambahPoin(1);
        }

        // Dibatalkan: poin yang dipakai untuk diskon dikembalikan.
        if ($statusBaru === 'Batal' && $this->poinDipakai > 0) {
            $this->pelanggan->tambahPoin($this->poinDipakai);
            $this->poinDipakai = 0;
        }
    }

    // ===== PERHITUNGAN =====

    // Jumlah harga semua item (sudah termasuk paket hemat).
    public function hitungSubtotal(): int
    {
        $total = 0;

        foreach ($this->daftarDetail as $detail) {
            $total += $detail->hitungSubtotal();
        }

        return $total;
    }

    public function hitungBiayaEkspres(): int
    {
        if ($this->jenisPaket !== 'Ekspres') {
            return 0;
        }

        return intdiv($this->hitungSubtotal() * self::PERSEN_EKSPRES, 100);
    }

    public function hitungPotongan(): int
    {
        if ($this->promo === null) {
            return 0;
        }

        $dasar = $this->hitungSubtotal() + $this->hitungBiayaEkspres();

        return $this->promo->hitungPotongan($dasar, new DateTime($this->tanggal));
    }

    public function hitungBiayaAntar(): int
    {
        return $this->pengambilan === 'Diantar' ? self::BIAYA_ANTAR : 0;
    }

    // Pendapatan laundry (tanpa biaya admin pembayaran).
    public function hitungTotal(): int
    {
        return $this->hitungSubtotal()
            + $this->hitungBiayaEkspres()
            - $this->hitungPotongan()
            + $this->hitungBiayaAntar();
    }

    // Yang dibayar pelanggan, termasuk biaya admin metode pembayaran.
    public function hitungTotalBayar(): int
    {
        return $this->metode->totalBayar($this->hitungTotal());
    }

    public function perkiraanSelesai(): DateTime
    {
        $hari = 0;

        foreach ($this->daftarDetail as $detail) {
            $hari = max($hari, $detail->getLayanan()->getEstimasiHari());
        }

        if ($this->jenisPaket === 'Ekspres') {
            $hari = min($hari, self::HARI_EKSPRES);
        }

        $selesai = new DateTime($this->tanggal);
        $selesai->modify('+' . $hari . ' days');

        return $selesai;
    }

    public function ringkasan(): string
    {
        return 'Transaksi #' . $this->id . ' - ' . $this->pelanggan->getNama()
            . ' [' . $this->status . '] Rp' . number_format($this->hitungTotalBayar(), 0, ',', '.');
    }
}
