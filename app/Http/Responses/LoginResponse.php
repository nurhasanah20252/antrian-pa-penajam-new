<?php

namespace App\Http\Responses;

use App\UserRole;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Symfony\Component\HttpFoundation\Response;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): Response
    {
        $user = $request->user();

        $redirectPath = match ($user->role) {
            UserRole::Admin => '/admin/dashboard',
            UserRole::PetugasUmum, UserRole::PetugasPosbakum, UserRole::PetugasPembayaran => '/officer/dashboard',
            UserRole::Masyarakat => '/dashboard',
            default => '/dashboard',
        };

        return $request->wantsJson()
            ? new JsonResponse(['two_factor' => false, 'redirect' => $redirectPath], 200)
            : redirect()->intended($redirectPath);
    }
}
