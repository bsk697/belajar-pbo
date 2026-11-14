<?php

declare(strict_types=1);

// Promo langganan: memotong total transaksi jika poin pelanggan cukup.
class Promo
{
    private int $id;
    private string $kode;
    private string $nama;
    private int $persen;
    private int $maksPotongan;
    private int $minTransaksi;
    private int $minPoin;
    private string $tglMulai;
    private string $tglSelesai;

    public function __construct(
        int $id,
        string $kode,
        string $nama,
        int $persen,
        int $maksPotongan,
        int $minTransaksi,
        int $minPoin,
        string $tglMulai,
        string $tglSelesai
    ) {
        if ($id < 1) {
            throw new InvalidArgumentException('ID promo harus lebih dari 0');
        }

        $this->id = $id;
        $this->setKode($kode);
        $this->setNama($nama);
        $this->setPersen($persen);
        $this->setMaksPotongan($maksPotongan);
        $this->setMinTransaksi($minTransaksi);
        $this->setMinPoin($minPoin);
        $this->setPeriode($tglMulai, $tglSelesai);
    }

    // ===== GETTER =====

    public function getId(): int
    {
        return $this->id;
    }

    public function getKode(): string
    {
        return $this->kode;
    }

    public function getNama(): string
    {
        return $this->nama;
    }

    public function getPersen(): int
    {
        return $this->persen;
    }

    public function getMaksPotongan(): int
    {
        return $this->maksPotongan;
    }

    public function getMinTransaksi(): int
    {
        return $this->minTransaksi;
    }

    public function getMinPoin(): int
    {
        return $this->minPoin;
    }

    public function getTglMulai(): string
    {
        return $this->tglMulai;
    }

    public function getTglSelesai(): string
    {
        return $this->tglSelesai;
    }

    // ===== SETTER + VALIDASI =====

    public function setKode(string $kode): void
    {
        $kode = strtoupper(trim($kode));

        if (!preg_match('/^[A-Z0-9]{4,12}$/', $kode)) {
            throw new InvalidArgumentException('Kode promo 4–12 karakter huruf/angka');
        }

        $this->kode = $kode;
    }

    public function setNama(string $nama): void
    {
        $nama = trim($nama);

        if ($nama === '' || mb_strlen($nama) > 60) {
            throw new InvalidArgumentException('Nama promo wajib diisi (maks. 60 karakter)');
        }

        $this->nama = $nama;
    }

    public function setPersen(int $persen): void
    {
        if ($persen < 1 || $persen > 100) {
            throw new InvalidArgumentException('Persen promo harus 1–100');
        }

        $this->persen = $persen;
    }

    public function setMaksPotongan(int $nilai): void
    {
        if ($nilai < 1) {
            throw new InvalidArgumentException('Maksimal potongan harus lebih dari 0');
        }

        $this->maksPotongan = $nilai;
    }

    public function setMinTransaksi(int $nilai): void
    {
        if ($nilai < 0) {
            throw new InvalidArgumentException('Minimal transaksi tidak boleh negatif');
        }

        $this->minTransaksi = $nilai;
    }

    public function setMinPoin(int $nilai): void
    {
        if ($nilai < 1 || $nilai > 100) {
            throw new InvalidArgumentException('Minimal poin harus 1–100');
        }

        $this->minPoin = $nilai;
    }

    // Tanggal mulai dan selesai divalidasi bersama agar urutannya benar.
    public function setPeriode(string $mulai, string $selesai): void
    {
        $a = DateTime::createFromFormat('Y-m-d', $mulai);
        $b = DateTime::createFromFormat('Y-m-d', $selesai);

        if ($a === false || $b === false
            || $a->format('Y-m-d') !== $mulai || $b->format('Y-m-d') !== $selesai) {
            throw new InvalidArgumentException('Tanggal promo harus berformat YYYY-MM-DD');
        }

        if ($b < $a) {
            throw new InvalidArgumentException('Tanggal selesai tidak boleh sebelum tanggal mulai');
        }

        $this->tglMulai = $mulai;
        $this->tglSelesai = $selesai;
    }

    // ===== PERILAKU =====

    // Promo langganan: pelanggan berhak jika poinnya cukup.
    public function layakUntuk(int $poinPelanggan): bool
    {
        return $poinPelanggan >= $this->minPoin;
    }

    public function isBerlaku(?DateTime $acuan = null): bool
    {
        $hari = ($acuan ?? new DateTime('today'))->format('Y-m-d');

        return $hari >= $this->tglMulai && $hari <= $this->tglSelesai;
    }

    // Potongan = persen dari subtotal, dibatasi maksimal. 0 jika syarat tidak terpenuhi.
    public function hitungPotongan(int $subtotal, ?DateTime $acuan = null): int
    {
        if (!$this->isBerlaku($acuan) || $subtotal < $this->minTransaksi) {
            return 0;
        }

        return min(intdiv($subtotal * $this->persen, 100), $this->maksPotongan);
    }
}
