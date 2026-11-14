<?php

declare(strict_types=1);

require_once __DIR__ . '/Orang.php';

// Kelas anak: Pelanggan adalah Orang (is-a) dengan data khusus pelanggan.
final class Pelanggan extends Orang
{
    public const JENIS_MEMBER = ['Reguler', 'Premium'];

    // Atribut milik Pelanggan sendiri (selain yang diwarisi dari Orang).
    private string $tglDaftar;
    private string $jenisMember;
    private int $poin;
    private bool $aktif;
    private string $catatan;

    public function __construct(
        int $id,
        string $nama,
        string $noHp,
        string $alamat,
        string $email,
        string $tglDaftar,
        string $jenisMember = 'Reguler',
        int $poin = 0,
        bool $aktif = true,
        string $catatan = ''
    ) {
        // Data umum diteruskan ke constructor induk.
        parent::__construct($id, $nama, $noHp, $alamat, $email);

        $tglDaftar = trim($tglDaftar);
        $tanggal   = DateTime::createFromFormat('Y-m-d', $tglDaftar);

        if ($tanggal === false || $tanggal->format('Y-m-d') !== $tglDaftar) {
            throw new InvalidArgumentException('Tanggal daftar harus berformat YYYY-MM-DD');
        }

        if ($poin < 0) {
            throw new InvalidArgumentException('Poin tidak boleh negatif');
        }

        $this->tglDaftar = $tglDaftar;
        $this->poin      = $poin;
        $this->setJenisMember($jenisMember);
        $this->setAktif($aktif);
        $this->setCatatan($catatan);
    }

    // ===== GETTER =====

    public function getTglDaftar(): string
    {
        return $this->tglDaftar;
    }

    public function getJenisMember(): string
    {
        return $this->jenisMember;
    }

    public function getPoin(): int
    {
        return $this->poin;
    }

    public function isAktif(): bool
    {
        return $this->aktif;
    }

    public function getCatatan(): string
    {
        return $this->catatan;
    }

    // ===== SETTER + VALIDASI =====

    public function setJenisMember(string $jenisMember): void
    {
        if (!in_array($jenisMember, self::JENIS_MEMBER, true)) {
            throw new InvalidArgumentException('Jenis member harus Reguler atau Premium');
        }

        $this->jenisMember = $jenisMember;
    }

    public function setAktif(bool $aktif): void
    {
        $this->aktif = $aktif;
    }

    public function setCatatan(string $catatan): void
    {
        $catatan = trim($catatan);

        if (strlen($catatan) > 255) {
            throw new InvalidArgumentException('Catatan maksimal 255 karakter');
        }

        $this->catatan = $catatan;
    }

    // ===== PERILAKU =====
    // Poin hanya berubah lewat operasi bisnis berikut, tidak ada setPoin() bebas.

    public function tambahPoin(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Poin yang ditambahkan harus lebih dari 0');
        }

        $this->poin += $jumlah;
    }

    public function gunakanPoin(int $jumlah): void
    {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException('Poin yang dipakai harus lebih dari 0');
        }

        if ($jumlah > $this->poin) {
            throw new InvalidArgumentException('Poin tidak mencukupi');
        }

        $this->poin -= $jumlah;
    }

    public function isPremium(): bool
    {
        return $this->jenisMember === 'Premium';
    }

    // Implementasi method abstract dari Orang.
    public function getPeran(): string
    {
        return 'Pelanggan';
    }

    // Overriding: menambah jenis member di atas ringkasan milik induk.
    public function ringkasan(): string
    {
        return parent::ringkasan() . ' [' . $this->jenisMember . ']';
    }
}
