{{-- ============================================ --}}
{{-- Email d'invitation d'un collaborateur          --}}
{{-- Mailable : App\Mail\InvitationMail             --}}
{{-- Variables attendues : $invitation              --}}
{{-- ============================================ --}}
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invitation à rejoindre une entreprise</title>
</head>
<body style="margin:0;padding:0;background:#f4f6f9;font-family:Inter,-apple-system,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f9;padding:32px 16px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:560px;background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                    {{-- Header --}}
                    <tr>
                        <td style="background:#00A86B;padding:28px 32px;text-align:center;">
                            <div style="font-size:38px;line-height:1;">🏢</div>
                            <h1 style="margin:8px 0 0;color:#ffffff;font-size:22px;font-weight:700;">Vous êtes invité à rejoindre</h1>
                            <p style="margin:6px 0 0;color:#d8f5e8;font-size:15px;">
                                {{ $invitation->client?->nom_entreprise ?? 'une entreprise' }}
                            </p>
                        </td>
                    </tr>

                    {{-- Body --}}
                    <tr>
                        <td style="padding:32px;">
                            <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
                                Bonjour{{ $invitation->nom ? ' ' . $invitation->nom : '' }},
                            </p>

                            <p style="margin:0 0 16px;color:#334155;font-size:15px;line-height:1.6;">
                                <strong>{{ $invitation->client?->nom_entreprise ?? 'L\'entreprise' }}</strong> vous invite
                                @if(($invitation->role_invite ?? '') === 'secretaire')
                                    à gérer son <strong>secrétariat</strong> : agenda, relances, documents (GED) et tâches.
                                @else
                                    à gérer sa <strong>comptabilité</strong> : écritures comptables, banque et états financiers.
                                @endif
                            </p>

                            @if($invitation->message)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f0fdf9;border:1px solid #b9f2d9;border-radius:8px;margin-bottom:20px;">
                                    <tr><td style="padding:14px 16px;color:#166534;font-size:14px;line-height:1.5;">{{ $invitation->message }}</td></tr>
                                </table>
                            @endif

                            <p style="margin:0 0 24px;color:#334155;font-size:15px;line-height:1.6;">
                                Pour accepter l'invitation et créer votre compte, cliquez sur le bouton ci-dessous :
                            </p>

                            <table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 auto 24px;">
                                <tr>
                                    <td style="background:#00A86B;border-radius:8px;">
                                        <a href="{{ route('gel.invitation.accept', $invitation->token) }}"
                                           style="display:inline-block;padding:14px 32px;color:#ffffff;font-size:15px;font-weight:600;text-decoration:none;">
                                            ✅ Accepter l'invitation
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:0 0 8px;color:#64748b;font-size:13px;line-height:1.5;">
                                Ce lien est valable jusqu'au <strong>{{ optional($invitation->expire_at)->format('d/m/Y') }}</strong>.
                                Si vous ne connaissez pas cette invitation, vous pouvez ignorer cet email.
                            </p>
                        </td>
                    </tr>

                    {{-- Footer --}}
                    <tr>
                        <td style="background:#f8fafc;padding:20px 32px;text-align:center;color:#94a3b8;font-size:12px;">
                            © {{ date('Y') }} GEL — Gestion Électronique et Logicielle. Tous droits réservés.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
