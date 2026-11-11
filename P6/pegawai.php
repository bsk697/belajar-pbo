<?php
declare(strict_types=1);

abstract class Pegawai {
    public function __construct(private string $id, private string $nama) {
        if (trim($id) === '' || trim($nama) === '') {
            throw new InvalidArgumentException('ID dan nama wajib diisi');
        }
    }

    public function getId(): string { return $this->id; }
    public function getNama(): string { return $this->nama; }

    abstract public function hitungGaji(): int;
    abstract public function getJenis(): string;
}