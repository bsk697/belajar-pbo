<?php

declare(strict_types=1);

// Cara bayar: tunai, QRIS, transfer bank, atau dompet digital.
class MetodePembayaran
{
    public const JENIS = ['Tunai', 'QRIS', 'Transfer', 'E-Wallet'];

    private int $id;
    private string $nama;
    private string $jenis;
    private int $biayaAdmin;
    private string $nomorTujuan;
    private bool $aktif;

    public function __construct(
        int $id,
        string $nama,
        string $jenis,
        int $biayaAdmin = 0,
        string $nomorTujuan = '',
        bool $aktif = true
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID metode harus lebih dari 0');
        }

        $this->id = $id;
        $this->setNama($nama);
        $this->setJenis($jenis);
        $this->setBiayaAdmin($biayaAdmin);
        $this->setNomorTujuan($nomorTujuan);
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

    public function getJenis(): string
    {
        return $this->jenis;
    }

    public function getBiayaAdmin(): int
    {
        return $this->biayaAdmin;
    }

    public function getNomorTujuan(): string
    {
        return $this->nomorTujuan;
    }

    public function isAktif(): bool
    {
        return $this->aktif;
    }

    // ===== SETTER + VALIDASI =====

    public function setNama(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '' || mb_strlen($nama) > 40) {
            throw new InvalidArgumentException('Nama metode wajib diisi (maks. 40 karakter)');
        }

        $this->nama = $nama;
    }

    public function setJenis(string $jenis): void
    {
        if (!in_array($jenis, self::JENIS, true)) {
            throw new InvalidArgumentException('Jenis harus Tunai, QRIS, Transfer, atau E-Wallet');
        }

        $this->jenis = $jenis;
    }

    public function setBiayaAdmin(int $nominal): void
    {
        if ($nominal < 0 || $nominal > 50000) {
            throw new InvalidArgumentException('Biaya admin harus Rp0–Rp50.000');
        }

        $this->biayaAdmin = $nominal;
    }

    public function setNomorTujuan(string $nomor): void
    {
        $nomor = trim($nomor);

        if ($nomor !== '' && !preg_match('/^[0-9]{6,20}$/', $nomor)) {
            throw new InvalidArgumentException('Nomor tujuan 6–20 digit angka');
        }

        $this->nomorTujuan = $nomor;
    }

    public function setAktif(bool $aktif): void
    {
        $this->aktif = $aktif;
    }

    // ===== PERILAKU =====

    public function butuhNomorTujuan(): bool
    {
        return in_array($this->jenis, ['Transfer', 'E-Wallet'], true);
    }

    // Biaya admin berupa nominal tetap (contoh: BI-FAST Rp2.500).
    public function hitungBiayaAdmin(): int
    {
        return $this->biayaAdmin;
    }

    public function totalBayar(int $total): int
    {
        if ($total < 0) {
            throw new InvalidArgumentException('Total tidak boleh negatif');
        }

        return $total + $this->biayaAdmin;
    }
}
