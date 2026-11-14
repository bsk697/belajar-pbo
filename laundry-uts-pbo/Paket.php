<?php

declare(strict_types=1);

require_once __DIR__ . '/Layanan.php';

// Paket hemat: memakai satu Layanan (komposisi) dengan diskon jika jumlah cukup.
class Paket
{
    private int $id;
    private string $nama;
    private Layanan $layanan;
    private float $jumlahMinimal;
    private int $diskonPersen;
    private bool $aktif;

    public function __construct(
        int $id,
        string $nama,
        Layanan $layanan,
        float $jumlahMinimal,
        int $diskonPersen,
        bool $aktif = true
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID paket harus lebih dari 0');
        }

        $this->id = $id;
        $this->layanan = $layanan;
        $this->setNama($nama);
        $this->setJumlahMinimal($jumlahMinimal);
        $this->setDiskonPersen($diskonPersen);
        $this->setAktif($aktif);
    }

    // ===== GETTER =====

    public function getId(): int
    {
        return $this->id;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getLayanan(): Layanan
    {
        return $this->layanan;
    }

    public function getJumlahMinimal(): float
    {
        return $this->jumlahMinimal;
    }

    public function getDiskonPersen(): int
    {
        return $this->diskonPersen;
    }

    public function isAktif(): bool
    {
        return $this->aktif;
    }

    // ===== SETTER + VALIDASI =====

    public function setNama(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '' || mb_strlen($nama) > 60) {
            throw new InvalidArgumentException('Nama paket wajib diisi (maks. 60 karakter)');
        }

        $this->nama = $nama;
    }

    public function setJumlahMinimal(float $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah minimal harus lebih dari 0');
        }

        $this->jumlahMinimal = $jumlah;
    }

    public function setDiskonPersen(int $persen): void
    {
        if ($persen < 0 || $persen > 100) {
            throw new InvalidArgumentException('Diskon harus 0–100 persen');
        }

        $this->diskonPersen = $persen;
    }

    public function setAktif(bool $aktif): void
    {
        $this->aktif = $aktif;
    }

    // ===== PERILAKU =====

    public function memenuhiSyarat(float $jumlah): bool
    {
        return $this->aktif && $jumlah >= $this->jumlahMinimal;
    }

    // Harga normal dari Layanan, lalu dipotong diskon jika memenuhi syarat.
    public function hitungHarga(float $jumlah): int
    {
        $normal = $this->layanan->hitungHarga($jumlah);

        if (!$this->memenuhiSyarat($jumlah)) {
            return $normal;
        }

        return $normal - intdiv($normal * $this->diskonPersen, 100);
    }

    public function ringkasan(): string
    {
        return $this->nama . ' (' . $this->layanan->getNama() . ', min. '
            . $this->jumlahMinimal . ' ' . $this->layanan->getSatuan()
            . ', diskon ' . $this->diskonPersen . '%)';
    }
}
