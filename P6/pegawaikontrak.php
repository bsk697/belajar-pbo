<?php
require_once __DIR__ . '/Pegawai.php';

class PegawaiKontrak extends Pegawai {
    private int $upahPerJam;
    private int $jamKerja;

    public function __construct(string $id, string $nama, int $upahPerJam, int $jamKerja) {
        parent::__construct($id, $nama);
        if ($upahPerJam < 0 || $jamKerja < 0) {
            throw new InvalidArgumentException('Upah per jam dan jam kerja tidak boleh negatif');
        }
        $this->upahPerJam = $upahPerJam;
        $this->jamKerja = $jamKerja;
    }

    public function hitungGaji(): int { return $this->upahPerJam * $this->jamKerja; }
    public function getJenis(): string { return 'Kontrak'; }
}