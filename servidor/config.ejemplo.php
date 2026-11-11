<?php
// Copia este archivo con el nombre config.php (en esta misma carpeta, api/) y escribe tus datos reales.
// config.php NO debe subirse a GitHub.
return array(
  'RESEND_API_KEY' => 'PEGA_AQUI_TU_CLAVE_DE_RESEND',
  'CONTACT_TO' => 'info@dynamedia.mx',
  // Mientras no verifiques tu dominio en Resend, deja este remitente de prueba:
  'CONTACT_FROM' => 'Dynamedia Web <onboarding@resend.dev>',

  // Captcha "No soy un robot" (opcional). Si dejas las claves vacías, el formulario funciona sin captcha.
  // 'recaptcha' = Google reCAPTCHA (casilla v2)   |   'turnstile' = Cloudflare Turnstile
  'CAPTCHA' => 'recaptcha',
  'CAPTCHA_SITE_KEY' => '',   // clave del sitio (pública)
  'CAPTCHA_SECRET' => '',     // clave secreta
);
