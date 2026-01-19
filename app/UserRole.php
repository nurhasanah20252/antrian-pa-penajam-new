<?php

namespace App;

enum UserRole: string
{
    case Admin = 'admin';
    case PetugasUmum = 'petugas_umum';
    case PetugasPosbakum = 'petugas_posbakum';
    case PetugasPembayaran = 'petugas_pembayaran';
    case Masyarakat = 'masyarakat';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrator',
            self::PetugasUmum => 'Petugas Umum',
            self::PetugasPosbakum => 'Petugas Posbakum',
            self::PetugasPembayaran => 'Petugas Pembayaran',
            self::Masyarakat => 'Masyarakat',
        };
    }

    public function isOfficer(): bool
    {
        return in_array($this, [self::PetugasUmum, self::PetugasPosbakum, self::PetugasPembayaran]);
    }

    public function isStaff(): bool
    {
        return $this !== self::Masyarakat;
    }
}
