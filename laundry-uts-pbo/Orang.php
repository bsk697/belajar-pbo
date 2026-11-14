<?php

declare(strict_types=1);

// Kelas induk untuk semua orang di aplikasi laundry (pelanggan dan kasir).
abstract class Orang
{
    // ENKAPSULASI: semua atribut private, diakses lewat getter dan setter.
    private int $id;
    private string $nama;
    private string $noHp;
    private string $alamat;
    private string $email;

    public function __construct(
        int $id,
        string $nama,
        string $noHp,
        string $alamat,
        string $email = ''
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID harus berupa bilangan positif');
        }

        $this->id = $id;

        // Setter dipakai ulang di constructor agar aturan validasi ditulis sekali.
        $this->setNama($nama);
        $this->setNoHp($noHp);
        $this->setAlamat($alamat);
        $this->setEmail($email);
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

    public function getNoHp(): string
    {
        return $this->noHp;
    }

    public function getAlamat(): string
    {
        return $this->alamat;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    // ===== SETTER + VALIDASI =====
    // ID sengaja tidak punya setter: hanya bisa dibaca setelah objek dibuat.

    public function setNama(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '') {
            throw new InvalidArgumentException('Nama wajib diisi');
        }

        if (strlen($nama) > 100) {
            throw new InvalidArgumentException('Nama maksimal 100 karakter');
        }

        $this->nama = $nama;
    }

    public function setNoHp(string $noHp): void
    {
        $noHp = trim($noHp);

        if (!preg_match('/^[0-9]{10,15}$/', $noHp)) {
            throw new InvalidArgumentException('No. HP harus berupa 10–15 digit angka');
        }

        $this->noHp = $noHp;
    }

    public function setAlamat(string $alamat): void
    {
        $alamat = trim($alamat);

        if ($alamat === '') {
            throw new InvalidArgumentException('Alamat wajib diisi');
        }

        $this->alamat = $alamat;
    }

    public function setEmail(string $email): void
    {
        $email = trim($email);

        // Email boleh kosong, tapi kalau diisi harus berformat benar.
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new InvalidArgumentException('Format email tidak valid');
        }

        $this->email = $email;
    }

    // ===== PERILAKU =====

    // Tiap anak wajib menyebutkan perannya sendiri (pelanggan atau kasir).
    abstract public function getPeran(): string;

    public function ringkasan(): string
    {
        return $this->getPeran() . ' #' . $this->id . ' - ' . $this->nama . ' (' . $this->noHp . ')';
    }
}