<?php

declare(strict_types=1);

require_once __DIR__ . '/Layanan.php';
require_once __DIR__ . '/Paket.php';

// Satu baris item dalam transaksi: layanan (atau paket) beserta jumlahnya.
class DetailTransaksi
{
    private int $id;
    private Layanan $layanan;
    private ?Paket $paket;
    private float $jumlah;
    private int $hargaSatuan;
    private string $catatan;

    public function __construct(
        int $id,
        Layanan $layanan,
        float $jumlah,
        ?Paket $paket = null,
        string $catatan = ''
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID detail harus lebih dari 0');
        }

        if ($paket !== null && $paket->getLayanan()->getId() !== $layanan->getId()) {
            throw new InvalidArgumentException('Paket tidak sesuai dengan layanan yang dipilih');
        }

        $this->id = $id;
        $this->layanan = $layanan;
        $this->paket = $paket;
        // Harga disimpan saat transaksi dibuat, jadi perubahan harga nanti tidak mengubah nota lama.
        $this->hargaSatuan = $layanan->getHargaPerSatuan();
        $this->setJumlah($jumlah);
        $this->setCatatan($catatan);
    }

    // ===== GETTER =====

    public function getId(): int
    {
        return $this->id;
    }

    public function getLayanan(): Layanan
    {
        return $this->layanan;
    }

    public function getPaket(): ?Paket
    {
        return $this->paket;
    }

    public function getJumlah(): float
    {
        return $this->jumlah;
    }

    public function getHargaSatuan(): int
    {
        return $this->hargaSatuan;
    }

    public function getCatatan(): string
    {
        return $this->catatan;
    }

    // ===== SETTER + VALIDASI =====

    public function setJumlah(float $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah harus lebih dari 0');
        }

        $this->jumlah = $jumlah;
    }

    public function setCatatan(string $catatan): void
    {
        $catatan = trim($catatan);

        if (mb_strlen($catatan) > 150) {
            throw new InvalidArgumentException('Catatan maksimal 150 karakter');
        }

        $this->catatan = $catatan;
    }

    // ===== PERILAKU =====

    public function hitungSubtotal(): int
    {
        if ($this->paket !== null) {
            return $this->paket->hitungHarga($this->jumlah);
        }

        return (int) round($this->jumlah * $this->hargaSatuan);
    }

    public function ringkasan(): string
    {
        return $this->layanan->getNama() . ' x ' . $this->jumlah . ' ' . $this->layanan->getSatuan()
            . ' = Rp' . number_format($this->hitungSubtotal(), 0, ',', '.');
    }
}