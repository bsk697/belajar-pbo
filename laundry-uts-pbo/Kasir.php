<?php

declare(strict_types=1);

require_once __DIR__ . '/Orang.php';

// Kelas anak: Kasir adalah Orang (is-a) dengan data khusus kasir.
final class Kasir extends Orang
{
    // Nama shift => [jam mulai, jam selesai].
    public const SHIFT = [
        'Pagi'  => ['07.00', '11.00'],
        'Siang' => ['11.00', '15.00'],
        'Sore'  => ['15.00', '19.00'],
        'Malam' => ['19.00', '23.00'],
    ];

    // Atribut milik Kasir sendiri (selain yang diwarisi dari Orang).
    private string $username;
    private string $shift;
    private string $tglMasuk;
    private int $gajiPokok;
    private bool $aktif;

    public function __construct(
        int $id,
        string $nama,
        string $noHp,
        string $alamat,
        string $email,
        string $username,
        string $shift,
        string $tglMasuk,
        int $gajiPokok,
        bool $aktif = true
    ) {
        // Data umum diteruskan ke constructor induk.
        parent::__construct($id, $nama, $noHp, $alamat, $email);

        $username = trim($username);

        if (!preg_match('/^[a-z0-9_]{3,20}$/', $username)) {
            throw new InvalidArgumentException('Username 3–20 karakter: huruf kecil, angka, atau garis bawah');
        }

        $tglMasuk = trim($tglMasuk);
        $tanggal  = DateTime::createFromFormat('Y-m-d', $tglMasuk);

        if ($tanggal === false || $tanggal->format('Y-m-d') !== $tglMasuk) {
            throw new InvalidArgumentException('Tanggal masuk harus berformat YYYY-MM-DD');
        }

        if ($gajiPokok < 0) {
            throw new InvalidArgumentException('Gaji pokok tidak boleh negatif');
        }

        $this->username  = $username;
        $this->tglMasuk  = $tglMasuk;
        $this->gajiPokok = $gajiPokok;
        $this->setShift($shift);
        $this->setAktif($aktif);
    }

    // ===== GETTER =====

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getShift(): string
    {
        return $this->shift;
    }

    // Contoh hasil: 07.00–11.00
    public function getJamKerja(): string
    {
        return self::SHIFT[$this->shift][0] . '–' . self::SHIFT[$this->shift][1];
    }

    public function getTglMasuk(): string
    {
        return $this->tglMasuk;
    }

    public function getGajiPokok(): int
    {
        return $this->gajiPokok;
    }

    public function isAktif(): bool
    {
        return $this->aktif;
    }

    // ===== SETTER + VALIDASI =====

    public function setShift(string $shift): void
    {
        if (!array_key_exists($shift, self::SHIFT)) {
            throw new InvalidArgumentException('Shift harus Pagi, Siang, Sore, atau Malam');
        }

        $this->shift = $shift;
    }

    public function setAktif(bool $aktif): void
    {
        $this->aktif = $aktif;
    }

    // ===== PERILAKU =====
    // Gaji hanya berubah lewat operasi bisnis ini, tidak ada setGajiPokok() bebas.

    public function naikkanGaji(int $persen): void
    {
        if ($persen < 1 || $persen > 100) {
            throw new InvalidArgumentException('Kenaikan gaji harus 1–100 persen');
        }

        $this->gajiPokok += intdiv($this->gajiPokok * $persen, 100);
    }

    // Menghitung lama bekerja (hari) sampai tanggal acuan (default: hari ini).
    public function hitungMasaKerjaHari(?DateTime $acuan = null): int
    {
        $acuan = $acuan ?? new DateTime('today');
        $masuk = new DateTime($this->tglMasuk);

        if ($acuan < $masuk) {
            return 0;
        }

        return (int) $masuk->diff($acuan)->days;
    }

    // Implementasi method abstract dari Orang.
    public function getPeran(): string
    {
        return 'Kasir';
    }

    // Overriding: menambah informasi shift di atas ringkasan milik induk.
    public function ringkasan(): string
    {
        return parent::ringkasan() . ' [Shift ' . $this->shift . ']';
    }
}
