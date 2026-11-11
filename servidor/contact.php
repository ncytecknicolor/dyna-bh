<?php
// Atiende /api/contact en Bluehost (Apache + PHP). Envía el mensaje del formulario con Resend.
// La clave de Resend NO va en GitHub: se guarda en api/config.php, que se crea directamente en el servidor.
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$cfg = file_exists(__DIR__ . '/config.php') ? require __DIR__ . '/config.php' : array();
$key = isset($cfg['RESEND_API_KEY']) ? $cfg['RESEND_API_KEY'] : '';
$to = !empty($cfg['CONTACT_TO']) ? $cfg['CONTACT_TO'] : 'info@dynamedia.mx';
$from = !empty($cfg['CONTACT_FROM']) ? $cfg['CONTACT_FROM'] : 'Dynamedia Web <onboarding@resend.dev>';
// Captcha: 'recaptcha' (Google reCAPTCHA v2) o 'turnstile' (Cloudflare). Solo se exige si hay clave pública y secreta.
$captcha = (isset($cfg['CAPTCHA']) && $cfg['CAPTCHA'] === 'turnstile') ? 'turnstile' : 'recaptcha';
$ckey = isset($cfg['CAPTCHA_SITE_KEY']) ? trim($cfg['CAPTCHA_SITE_KEY']) : '';
$csecret = isset($cfg['CAPTCHA_SECRET']) ? trim($cfg['CAPTCHA_SECRET']) : '';
$captchaActivo = ($ckey !== '' && $csecret !== '');

function salida($datos, $codigo = 200) {
  http_response_code($codigo);
  echo json_encode($datos, JSON_UNESCAPED_UNICODE);
  exit;
}
function limpio($valor, $max) {
  $v = is_scalar($valor) ? trim((string)$valor) : '';
  return cortar($v, $max);
}
function esc($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function cortar($s, $max) { return function_exists('mb_substr') ? mb_substr($s, 0, $max) : substr($s, 0, $max); }
function largo($s) { return function_exists('mb_strlen') ? mb_strlen($s) : strlen($s); }

// Revisión rápida: abre /api/contact en el navegador para ver si el correo está configurado (no muestra claves).
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  $partes = explode('@', $to);
  $usuario = isset($partes[0]) ? $partes[0] : '';
  $dominio = isset($partes[1]) ? $partes[1] : '';
  salida(array('clave_configurada' => $key !== '', 'captcha_activo' => $captchaActivo, 'captcha' => $captchaActivo ? array('proveedor' => $captcha, 'clave' => $ckey) : null, 'destino' => cortar($usuario, 2) . '***@' . $dominio, 'remitente' => $from));
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') salida(array('error' => 'Método no permitido', 'codigo' => 'metodo'), 405);

$b = json_decode(file_get_contents('php://input'), true);
if (!is_array($b)) salida(array('error' => 'Solicitud inválida', 'codigo' => 'solicitud'), 400);

// Anti-spam: campo trampa y envíos demasiado rápidos. Se responde "ok" sin enviar nada.
$trampa = isset($b['web']) ? limpio($b['web'], 200) : '';
$tiempo = isset($b['tiempo']) ? (float)$b['tiempo'] : 0;
if ($trampa !== '' || $tiempo < 2500) salida(array('ok' => true));

$nombre = preg_replace('/[\r\n]+/', ' ', limpio(isset($b['nombre']) ? $b['nombre'] : '', 120));
$correo = limpio(isset($b['correo']) ? $b['correo'] : '', 200);
$telefono = limpio(isset($b['telefono']) ? $b['telefono'] : '', 40);
$empresa = limpio(isset($b['empresa']) ? $b['empresa'] : '', 120);
$cuando = limpio(isset($b['cuando']) ? $b['cuando'] : '', 60);
$pagina = limpio(isset($b['pagina']) ? $b['pagina'] : '', 300);
$mensaje = limpio(isset($b['mensaje']) ? $b['mensaje'] : '', 4000);
$servicios = array();
if (isset($b['servicios']) && is_array($b['servicios'])) {
  foreach (array_slice($b['servicios'], 0, 12) as $s) {
    $s = limpio($s, 80);
    if ($s !== '') $servicios[] = $s;
  }
}
if (largo($nombre) < 2 || !filter_var($correo, FILTER_VALIDATE_EMAIL) || largo($mensaje) < 5) {
  salida(array('error' => 'Datos incompletos', 'codigo' => 'datos'), 400);
}
if ($key === '') salida(array('error' => 'Servicio de correo no configurado', 'codigo' => 'sin-clave'), 503);

// Verificación del captcha con el proveedor
if ($captchaActivo) {
  $tok = limpio(isset($b['captcha']) ? $b['captcha'] : '', 4000);
  if ($tok === '') salida(array('error' => 'Confirma que no eres un robot', 'codigo' => 'captcha'), 400);
  $url = $captcha === 'turnstile' ? 'https://challenges.cloudflare.com/turnstile/v0/siteverify' : 'https://www.google.com/recaptcha/api/siteverify';
  $cv = curl_init($url);
  curl_setopt_array($cv, array(
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 10,
    CURLOPT_POSTFIELDS => http_build_query(array('secret' => $csecret, 'response' => $tok, 'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')),
  ));
  $rv = curl_exec($cv);
  curl_close($cv);
  $verif = ($rv === false) ? null : json_decode($rv, true);
  if (!is_array($verif) || empty($verif['success'])) {
    error_log('Captcha rechazado: ' . ($rv === false ? 'sin respuesta' : $rv));
    salida(array('error' => 'No se pudo verificar el captcha', 'codigo' => 'captcha'), 400);
  }
}

function fila($k, $v) {
  if ($v === '') return '';
  return '<tr><td style="padding:6px 12px;color:#666">' . $k . '</td><td style="padding:6px 12px"><strong>' . esc($v) . '</strong></td></tr>';
}
$lista = implode(', ', $servicios);
$html = '<div style="font-family:Arial,sans-serif;max-width:560px;color:#2B2F36">'
  . '<h2 style="color:#1B4F9C;margin-bottom:8px">Nuevo mensaje desde dynamedia.mx</h2>'
  . '<table style="border-collapse:collapse;background:#F4F5F7;border-radius:8px;width:100%">'
  . fila('Nombre', $nombre) . fila('Correo', $correo) . fila('Teléfono / WhatsApp', $telefono) . fila('Empresa', $empresa)
  . fila('Le interesa', $lista) . fila('Para cuándo', $cuando) . fila('Enviado desde', $pagina)
  . '</table><h3 style="margin-top:24px">Mensaje</h3>'
  . '<p style="white-space:pre-wrap;line-height:1.5">' . esc($mensaje) . '</p>'
  . '<p style="color:#888;font-size:12px">Puedes responder directamente a este correo: llegará a ' . esc($correo) . '.</p></div>';
$texto = "Nuevo mensaje desde dynamedia.mx\n\nNombre: $nombre\nCorreo: $correo\nTeléfono: $telefono\nEmpresa: $empresa\nLe interesa: $lista\nPara cuándo: $cuando\nEnviado desde: $pagina\n\n$mensaje";

$carga = json_encode(array(
  'from' => $from, 'to' => array($to), 'reply_to' => $correo,
  'subject' => 'Nuevo contacto web: ' . $nombre, 'html' => $html, 'text' => $texto,
), JSON_UNESCAPED_UNICODE);

$ch = curl_init('https://api.resend.com/emails');
curl_setopt_array($ch, array(
  CURLOPT_POST => true,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT => 15,
  CURLOPT_HTTPHEADER => array('Authorization: Bearer ' . $key, 'Content-Type: application/json'),
  CURLOPT_POSTFIELDS => $carga,
));
$respuesta = curl_exec($ch);
$estado = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($respuesta === false) salida(array('error' => 'No se pudo enviar', 'codigo' => 'red'), 502);
if ($estado < 200 || $estado >= 300) {
  error_log('Error de Resend ' . $estado . ': ' . $respuesta);
  salida(array('error' => 'No se pudo enviar', 'codigo' => 'resend-' . $estado), 502);
}
salida(array('ok' => true));
