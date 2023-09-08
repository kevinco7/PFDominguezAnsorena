
<?php
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: index.html");
    exit();
}

// Validate and sanitize user input
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$telefono = trim($_POST['telefono']);
$email = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
$descripcion = trim($_POST['descripcion']);

// Validate required fields
if (empty($nombre) || empty($apellido) || empty($telefono) || empty($email) || empty($descripcion)) {
    header("Location: index.html");
    exit();
}

// Verify reCAPTCHA
$recaptcha_secret = '6Lfm9qcnAAAAADs5_uY2WhRuQd-k_OrUzlHgKuad';
$recaptcha_response = $_POST['g-recaptcha-response']; // Corrected field name

$recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
$recaptcha_data = array(
    'secret' => $recaptcha_secret,
    'response' => $recaptcha_response
);

$recaptcha_options = array(
    'http' => array(
        'method' => 'POST',
        'content' => http_build_query($recaptcha_data)
    )
);

$recaptcha_context = stream_context_create($recaptcha_options);
$recaptcha_result = file_get_contents($recaptcha_url, false, $recaptcha_context);
$recaptcha_result = json_decode($recaptcha_result);

if (!$recaptcha_result->success) {
    echo "<script>alert('La verificación de reCAPTCHA falló. Por favor, regrese y vuelva a intentarlo.'); setTimeout(function(){ window.location.href = 'index.html'; }, 2000);</script>";
    exit();
}



$body = <<<html
<h1>Contacto desde la web</h1>
<p>De: $nombre $apellido / $email</p>
<h2>Mensaje</h2>
<p>$descripcion</p>
html;

$headers = "MIME-Version: 1.0\r\n";
$headers .= "Content-type: text/html; charset=utf-8\r\n";
$headers .= "From: $nombre $apellido <$email>\r\n";
$headers .= "To: sitio web <web@haraspampasrl.com.ar>\r\n";

// Attempt to send the email
$mailSent = mail('web@haraspampasrl.com.ar', "Mensaje web: Consulta desde el sitio web", $body, $headers);

if ($mailSent) {
    header("Location: gracias.html");
} else {
    echo "Hubo un error al enviar el mensaje. Por favor, inténtelo nuevamente más tarde.";
}
?>