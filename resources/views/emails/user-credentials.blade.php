<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body style="font-family: Arial, sans-serif; background-color: #f4f6f9; padding: 30px 0; margin: 0;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td align="center">
                <table role="presentation" width="480" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden;">

                    <tr>
                        <td style="background-color: #002D5D; padding: 24px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 20px;">StockApp</h1>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 32px;">
                            <p style="font-size: 16px; color: #333;">Bonjour <strong>{{ $user->full_name }}</strong>,</p>

                            <p style="font-size: 15px; color: #555; line-height: 1.6;">
                                Un compte a été créé pour vous sur StockApp. Voici vos identifiants de connexion :
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f4f6f9; border-radius: 6px; margin: 20px 0;">
                                <tr>
                                    <td style="padding: 16px 20px;">
                                        <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">Email</p>
                                        <p style="margin: 0 0 16px 0; font-size: 15px; color: #002D5D; font-weight: bold;">{{ $user->email }}</p>

                                        <p style="margin: 0 0 8px 0; font-size: 14px; color: #666;">Mot de passe</p>
                                        <p style="margin: 0; font-size: 15px; color: #002D5D; font-weight: bold;">{{ $password }}</p>
                                    </td>
                                </tr>
                            </table>

                            <p style="font-size: 14px; color: #999; line-height: 1.6;">
                                Pour votre sécurité, nous vous recommandons de changer ce mot de passe dès votre première connexion.
                            </p>

                            <div style="text-align: center; margin-top: 28px;">
                                <a href="{{ config('app.frontend_url', config('app.url')) }}/login"
                                   style="background-color: #002D5D; color: #ffffff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 15px; display: inline-block;">
                                    Se connecter
                                </a>
                            </div>
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 20px; text-align: center; background-color: #f4f6f9;">
                            <p style="margin: 0; font-size: 12px; color: #999;">
                                Cet email a été généré automatiquement, merci de ne pas y répondre.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>
