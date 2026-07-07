<?php

namespace App\Services;

use App\Models\Client;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Str;

class CompanyCodeService
{
    /**
     * Préfixe du code entreprise.
     */
    const PREFIX = 'ENT';

    /**
     * Longueur de la partie aléatoire.
     */
    const RANDOM_LENGTH = 6;

    /**
     * Génère un code unique au format ENT-XXXXXX.
     */
    public function generate(): string
    {
        do {
            $code = self::PREFIX . '-' . Str::upper(Str::random(self::RANDOM_LENGTH));
        } while (Client::where('client_code', $code)->exists());

        return $code;
    }

    /**
     * Valide un code saisi par un client et retourne le client correspondant.
     */
    public function resolve(string $code): ?Client
    {
        $code = strtoupper(trim($code));

        return Client::where('client_code', $code)
            ->where('status', 'actif')
            ->first();
    }

    /**
     * Génère un QR code SVG en data URI pour un code donné.
     */
    public function qrCodeDataUri(string $code): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(280, 4),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $svg = $writer->writeString($code);

        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Vérifie qu'un utilisateur n'est pas déjà lié à ce client.
     */
    public function canAttachUser(int $userId, int $clientId): bool
    {
        return ! \App\Models\UserClient::where('user_id', $userId)
            ->where('client_id', $clientId)
            ->exists();
    }
}
