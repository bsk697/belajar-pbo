<?php

declare(strict_types=1);

// Layanan laundry, misalnya Cuci Kiloan atau Setrika.
class Layanan
{
    public const SATUAN = ['Kg', 'Pcs', 'Meter'];

    private int $id;
    private string $nama;
    private string $satuan;
    private int $hargaPerSatuan;
    private int $estimasiHari;
    private bool $aktif;

    public function __construct(
        int $id,
        string $nama,
        string $satuan,
        int $hargaPerSatuan,
        int $estimasiHari = 2,
        bool $aktif = true
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID layanan harus lebih dari 0');
        }

        $this->id = $id;
        $this->setNama($nama);
        $this->setSatuan($satuan);
        $this->setHargaPerSatuan($hargaPerSatuan);
        $this->setEstimasiHari($estimasiHari);
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

    public function getSatuan(): string
    {
        return $this->satuan;
    }

    public function getHargaPerSatuan(): int
    {
        return $this->hargaPerSatuan;
    }

    public function getEstimasiHari(): int
    {
        return $this->estimasiHari;
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
            throw new InvalidArgumentException('Nama layanan wajib diisi (maks. 60 karakter)');
        }

        $this->nama = $nama;
    }

    public function setSatuan(string $satuan): void
    {
        if (!in_array($satuan, self::SATUAN, true)) {
            throw new InvalidArgumentException('Satuan harus Kg, Pcs, atau Meter');
        }

        $this->satuan = $satuan;
    }

    public function setHargaPerSatuan(int $harga): void
    {
        if ($harga < 1) {
            throw new InvalidArgumentException('Harga per satuan harus lebih dari 0');
        }

        $this->hargaPerSatuan = $harga;
    }

    public function setEstimasiHari(int $hari): void
    {
        if ($hari < 0 || $hari > 30) {
            throw new InvalidArgumentException('Estimasi hari harus 0–30');
        }

        $this->estimasiHari = $hari;
    }

    public function setAktif(bool $aktif): void
    {
        $this->aktif = $aktif;
    }

    // ===== PERILAKU =====

    // Harga = jumlah x harga per satuan, dibulatkan ke rupiah.
    public function hitungHarga(float $jumlah): int
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Jumlah harus lebih dari 0');
        }

        return (int) round($jumlah * $this->hargaPerSatuan);
    }

    public function perkiraanSelesai(DateTime $tglMasuk): DateTime
    {
        $selesai = clone $tglMasuk;
        $selesai->modify('+' . $this->estimasiHari . ' days');

        return $selesai;
    }

    public function ringkasan(): string
    {
        return $this->nama . ' - Rp' . number_format($this->hargaPerSatuan, 0, ',', '.') . '/' . $this->satuan;
    }
}
