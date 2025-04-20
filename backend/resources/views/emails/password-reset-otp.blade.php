<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6;">
    
    <!-- Container principale -->
    <div style="max-width: 600px; margin: 20px auto; padding: 30px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
        
        <!-- Intestazione -->
        <h1 style="color: #1f2937; font-size: 24px; margin-bottom: 25px; border-bottom: 1px solid #e5e7eb; padding-bottom: 15px;">
            Reset Password OTP
        </h1>

        <!-- Contenuto principale -->
        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 20px;">
            Abbiamo ricevuto una richiesta di reset password per il tuo account. Utilizza il seguente codice OTP per procedere:
        </p>

        <!-- Panel OTP -->
        <div style="background-color: #f8fafc; padding: 20px; border-radius: 6px; border: 1px solid #e2e8f0; margin: 25px 0; text-align: center;">
            <div style="font-size: 18px; color: #1e40af; margin-bottom: 10px;">🔐</div>
            <strong style="font-size: 24px; color: #1f2937; letter-spacing: 2px;">{{ $otp }}</strong>
        </div>

        <!-- Sezione informazioni -->
        <h2 style="color: #1f2937; font-size: 18px; margin: 25px 0 15px;">Informazioni importanti:</h2>
        <ul style="color: #4b5563; line-height: 1.6; padding-left: 20px; margin-bottom: 25px;">
            <li style="margin-bottom: 8px;">Il codice OTP scadrà tra <strong>15 minuti</strong></li>
            <li style="margin-bottom: 8px;">Non condividere questo codice con nessuno</li>
            <li>Se non hai richiesto tu il reset, ignora questa email</li>
        </ul>

        <!-- Avviso sicurezza -->
        <p style="color: #4b5563; line-height: 1.6; margin-bottom: 25px;">
            Se hai problemi con il codice OTP o non hai fatto questa richiesta, 
            <a href="mailto:support@doitapp.com" style="color: #3b82f6; text-decoration: none;">contatta immediatamente il nostro supporto</a>.
        </p>

        <!-- Footer -->
        <div style="margin-top: 40px; padding-top: 25px; border-top: 1px solid #e5e7eb; text-align: center; color: #6b7280; font-size: 14px;">
            <p style="margin: 0 0 5px;">Grazie,</p>
            <p style="margin: 0; font-weight: 500;">Il team di Do!t</p>
            <p style="margin: 20px 0 0;">
                © {{ date('Y') }} Do!t. Tutti i diritti riservati.
            </p>
        </div>
    </div>

</body>
</html>