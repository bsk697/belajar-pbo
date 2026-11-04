<?php

declare(strict_types=1);

class Mahasiswa
{
    // Daftar agama yang valid, aturannya ada di dalam class.
    public const DAFTAR_AGAMA = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Konghucu'];

    // ENKAPSULASI: semua property private, tidak bisa diubah langsung dari luar.
    private string $nim;
    private string $nama;
    private string $fakultas;
    private string $prodi;
    private string $asalSekolah;
    private int $semester;
    private string $hobi;
    private string $agama;

    // Atribut dinamis untuk demo overloading (praktikum 4).
    private array $atributTambahan = [];

    // Constructor: NIM ditetapkan (tidak punya setter), sisanya lewat setter
    // supaya aturan validasi tidak ditulis dua kali.
    public function __construct(
        string $nim,
        string $nama,
        string $fakultas,
        string $prodi,
        string $asalSekolah,
        int $semester,
        string $hobi,
        string $agama
    ) {
        $nim = trim($nim);

        if ($nim === '') {
            throw new InvalidArgumentException('NIM wajib diisi');
        }

        $this->nim = $nim;
        $this->setNama($nama);
        $this->setFakultas($fakultas);
        $this->setProdi($prodi);
        $this->setAsalSekolah($asalSekolah);
        $this->setSemester($semester);
        $this->setHobi($hobi);
        $this->setAgama($agama);
    }

    // ===== GETTER =====

    public function getNim(): string
    {
        return $this->nim;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getFakultas(): string
    {
        return $this->fakultas;
    }

    public function getProdi(): string
    {
        return $this->prodi;
    }

    public function getAsalSekolah(): string
    {
        return $this->asalSekolah;
    }

    public function getSemester(): int
    {
        return $this->semester;
    }

    public function getHobi(): string
    {
        return $this->hobi;
    }

    public function getAgama(): string
    {
        return $this->agama;
    }

    // ===== SETTER + VALIDASI =====
    // NIM sengaja tidak punya setter: hanya bisa dibaca setelah object dibuat.

    public function setNama(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '') {
            throw new InvalidArgumentException('Nama wajib diisi');
        }

        $this->nama = $nama;
    }

    public function setFakultas(string $fakultas): void
    {
        $fakultas = trim($fakultas);

        if ($fakultas === '') {
            throw new InvalidArgumentException('Fakultas wajib diisi');
        }

        $this->fakultas = $fakultas;
    }

    public function setProdi(string $prodi): void
    {
        $prodi = trim($prodi);

        if ($prodi === '') {
            throw new InvalidArgumentException('Prodi wajib diisi');
        }

        $this->prodi = $prodi;
    }

    public function setAsalSekolah(string $asalSekolah): void
    {
        $asalSekolah = trim($asalSekolah);

        if ($asalSekolah === '') {
            throw new InvalidArgumentException('Asal sekolah wajib diisi');
        }

        $this->asalSekolah = $asalSekolah;
    }

    public function setSemester(int $semester): void
    {
        if ($semester < 1 || $semester > 14) {
            throw new InvalidArgumentException('Semester harus 1–14');
        }

        $this->semester = $semester;
    }

    public function setHobi(string $hobi): void
    {
        // Hobi boleh kosong, jadi hanya dirapikan.
        $this->hobi = trim($hobi);
    }

    public function setAgama(string $agama): void
    {
        if (!in_array($agama, self::DAFTAR_AGAMA, true)) {
            throw new InvalidArgumentException('Pilihan agama tidak valid');
        }

        $this->agama = $agama;
    }

    public function ringkasan(): string
    {
        return $this->nim . ' - ' . $this->nama;
    }

    // ===== OVERLOADING (praktikum 4) =====
    // PENTING: saat property private diakses dari luar, PHP memanggil __get/__set.
    // Tanpa penjagaan, akses itu diam-diam masuk ke $atributTambahan dan tidak error.
    // Karena itu property yang sudah dideklarasikan ditolak secara eksplisit.

    private function tolakProperty(string $nama): void
    {
        if (property_exists($this, $nama)) {
            throw new Error('Tidak dapat mengakses property private ' . self::class . '::$' . $nama);
        }
    }

    public function __set(string $nama, mixed $nilai): void
    {
        $this->tolakProperty($nama);
        $this->atributTambahan[$nama] = $nilai;
    }

    public function __get(string $nama): mixed
    {
        $this->tolakProperty($nama);

        return $this->atributTambahan[$nama] ?? null;
    }

    public function __isset(string $nama): bool
    {
        if (property_exists($this, $nama)) {
            return false;
        }

        return isset($this->atributTambahan[$nama]);
    }

    public function __unset(string $nama): void
    {
        $this->tolakProperty($nama);
        unset($this->atributTambahan[$nama]);
    }

    // Overloading method: hanya menerima alias yang sengaja disediakan.
    public function __call(string $nama, array $argumen): mixed
    {
        if ($nama === 'profilSingkat' && count($argumen) === 0) {
            return $this->ringkasan();
        }

        throw new BadMethodCallException('Method tidak tersedia: ' . $nama);
    }

    public function __destruct()
    {
        // Demonstrasi saja. Jangan echo di aplikasi CRUD/session.
        if (defined('DEMO_LIFECYCLE') && DEMO_LIFECYCLE) {
            error_log('Destructor Mahasiswa: ' . $this->nim);
        }
    }
}