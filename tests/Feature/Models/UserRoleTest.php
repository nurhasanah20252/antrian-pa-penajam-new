<?php

use App\Models\User;
use App\UserRole;

describe('UserRole Enum', function () {
    it('has correct string values', function () {
        expect(UserRole::Admin->value)->toBe('admin')
            ->and(UserRole::PetugasUmum->value)->toBe('petugas_umum')
            ->and(UserRole::PetugasPosbakum->value)->toBe('petugas_posbakum')
            ->and(UserRole::PetugasPembayaran->value)->toBe('petugas_pembayaran')
            ->and(UserRole::Masyarakat->value)->toBe('masyarakat');
    });

    it('returns correct labels', function (UserRole $role, string $expectedLabel) {
        expect($role->label())->toBe($expectedLabel);
    })->with([
        'admin' => [UserRole::Admin, 'Administrator'],
        'petugas umum' => [UserRole::PetugasUmum, 'Petugas Umum'],
        'petugas posbakum' => [UserRole::PetugasPosbakum, 'Petugas Posbakum'],
        'petugas pembayaran' => [UserRole::PetugasPembayaran, 'Petugas Pembayaran'],
        'masyarakat' => [UserRole::Masyarakat, 'Masyarakat'],
    ]);

    it('correctly identifies officers', function (UserRole $role, bool $isOfficer) {
        expect($role->isOfficer())->toBe($isOfficer);
    })->with([
        'admin is not officer' => [UserRole::Admin, false],
        'petugas umum is officer' => [UserRole::PetugasUmum, true],
        'petugas posbakum is officer' => [UserRole::PetugasPosbakum, true],
        'petugas pembayaran is officer' => [UserRole::PetugasPembayaran, true],
        'masyarakat is not officer' => [UserRole::Masyarakat, false],
    ]);

    it('correctly identifies staff', function (UserRole $role, bool $isStaff) {
        expect($role->isStaff())->toBe($isStaff);
    })->with([
        'admin is staff' => [UserRole::Admin, true],
        'petugas umum is staff' => [UserRole::PetugasUmum, true],
        'petugas posbakum is staff' => [UserRole::PetugasPosbakum, true],
        'petugas pembayaran is staff' => [UserRole::PetugasPembayaran, true],
        'masyarakat is not staff' => [UserRole::Masyarakat, false],
    ]);
});

describe('User Role Helper Methods', function () {
    it('can create admin user', function () {
        $user = User::factory()->admin()->make();

        expect($user->role)->toBe(UserRole::Admin)
            ->and($user->isAdmin())->toBeTrue()
            ->and($user->isOfficer())->toBeFalse()
            ->and($user->isStaff())->toBeTrue()
            ->and($user->isMasyarakat())->toBeFalse();
    });

    it('can create petugas umum user', function () {
        $user = User::factory()->petugasUmum()->make();

        expect($user->role)->toBe(UserRole::PetugasUmum)
            ->and($user->isAdmin())->toBeFalse()
            ->and($user->isOfficer())->toBeTrue()
            ->and($user->isStaff())->toBeTrue()
            ->and($user->isMasyarakat())->toBeFalse();
    });

    it('can create petugas posbakum user', function () {
        $user = User::factory()->petugasPosbakum()->make();

        expect($user->role)->toBe(UserRole::PetugasPosbakum)
            ->and($user->isAdmin())->toBeFalse()
            ->and($user->isOfficer())->toBeTrue()
            ->and($user->isStaff())->toBeTrue()
            ->and($user->isMasyarakat())->toBeFalse();
    });

    it('can create petugas pembayaran user', function () {
        $user = User::factory()->petugasPembayaran()->make();

        expect($user->role)->toBe(UserRole::PetugasPembayaran)
            ->and($user->isAdmin())->toBeFalse()
            ->and($user->isOfficer())->toBeTrue()
            ->and($user->isStaff())->toBeTrue()
            ->and($user->isMasyarakat())->toBeFalse();
    });

    it('creates masyarakat by default', function () {
        $user = User::factory()->make();

        expect($user->role)->toBe(UserRole::Masyarakat)
            ->and($user->isAdmin())->toBeFalse()
            ->and($user->isOfficer())->toBeFalse()
            ->and($user->isStaff())->toBeFalse()
            ->and($user->isMasyarakat())->toBeTrue();
    });

    it('can create inactive user', function () {
        $user = User::factory()->inactive()->make();

        expect($user->is_active)->toBeFalse();
    });
});
