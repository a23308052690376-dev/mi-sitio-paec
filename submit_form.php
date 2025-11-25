<?php
// submit_form.php
// Enhanced: reads config.php (example provided), inserts to DB and tries to send an email
// with the infographic using (in order of preference): SendGrid API, PHPMailer (SMTP), or fallback mail() with a download link.

header('Content-Type: application/json; charset=utf-8');

// Load configuration (copy config.php.example -> config.php and fill values)
if(!file_exists(__DIR__.'/config.php')){
  http_response_code(500);
  echo json_encode(['error'=>'Server misconfiguration: config.php not found. Copy config.php.example and fill values.']);
  exit;
}
$config = require __DIR__ . '/config.php';

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$comments = trim($_POST['comments'] ?? '');

if(empty($name) || !filter_var($email, FILTER_VALIDATE_EMAIL)){
  http_response_code(400);
  echo json_encode(['error'=>'Invalid input']);
  exit;
}

// Insert into DB
try {
  $db = $config['db'];
  $dsn = "mysql:host={$db['host']};dbname={$db['name']};charset=utf8mb4";
  $pdo = new PDO($dsn, $db['user'], $db['pass'], [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);

  $stmt = $pdo->prepare('INSERT INTO contacts (name,email,comments) VALUES (?,?,?)');
  $stmt->execute([$name, $email, $comments]);
  $id = $pdo->lastInsertId();
} catch (Exception $e) {
  http_response_code(500);
  error_log('DB error: '.$e->getMessage());
  echo json_encode(['error'=>'Database error']);
  exit;
}

// Email sending: prefer SendGrid API if api key provided
$infographic_path = $config['infographic_path'] ?? (__DIR__.'/infografia.pdf');
$site_url = rtrim($config['site_url'] ?? '', '/');

$sent = false; $send_error = '';

// 1) SendGrid API (recommended for hosts that block SMTP)
if(!empty($config['sendgrid_api_key'])){
  $apiKey = $config['sendgrid_api_key'];
  if(file_exists($infographic_path)){
    $content = base64_encode(file_get_contents($infographic_path));
    $filename = basename($infographic_path);
    $payload = [
      'personalizations' => [[ 'to' => [[ 'email' => $email, 'name' => $name ]], 'subject' => 'Infografía sobre Comer con Conciencia' ]],
      'from' => [ 'email' => $config['smtp']['from_email'] ?? 'no-reply@'.$site_url, 'name' => $config['smtp']['from_name'] ?? 'Mi sitio' ],
      'content' => [[ 'type' => 'text/plain', 'value' => "Gracias por registrarte. Adjuntamos una infografía con información relevante." ]],
      'attachments' => [[ 'content' => $content, 'filename' => $filename, 'type' => mime_content_type($infographic_path), 'disposition' => 'attachment' ]]
    ];

    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Authorization: Bearer '.$apiKey,
      'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $resp = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err = curl_error($ch);
    curl_close($ch);

    if($httpCode >=200 && $httpCode < 300){
      $sent = true;
    } else {
      $send_error = "SendGrid error ($httpCode): $err | resp: $resp";
    }
  } else {
    $send_error = 'Infographic file not found for attachment.';
  }
}

// 2) PHPMailer via SMTP (if available and SendGrid not used/failed)
if(!$sent && file_exists(__DIR__.'/vendor/autoload.php')){
  try{
    require __DIR__.'/vendor/autoload.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    if(!empty($config['smtp'])){
      $smtp = $config['smtp'];
      $mail->isSMTP();
      $mail->Host = $smtp['host'];
      $mail->SMTPAuth = true;
      $mail->Username = $smtp['username'];
      $mail->Password = $smtp['password'];
      $mail->SMTPSecure = $smtp['secure'] ?? 'tls';
      $mail->Port = $smtp['port'] ?? 587;
    }

    $mail->setFrom($config['smtp']['from_email'] ?? ('no-reply@'.$site_url), $config['smtp']['from_name'] ?? 'Mi sitio');
    $mail->addAddress($email, $name);
    $mail->Subject = 'Infografía sobre Comer con Conciencia';
    $mail->Body = 'Gracias por registrarte. Adjuntamos una infografía con información relevante.';

    if(file_exists($infographic_path)){
      $mail->addAttachment($infographic_path, basename($infographic_path));
    }

    $mail->send();
    $sent = true;
  } catch(Exception $e){
    $send_error .= ' PHPMailer error: '.$e->getMessage();
  }
}

// 3) Fallback: native mail() with link to infographic (if attachments not possible)
if(!$sent){
  $to = $email;
  $subject = 'Infografía sobre Comer con Conciencia';
  $infog_link = $site_url ? ($site_url.'/'.basename($infographic_path)) : ('https://'.$_SERVER['HTTP_HOST'].'/'.basename($infographic_path));
  $message = "Gracias for registering, $name.\n\nYou can download the infographic here: $infog_link\n\nComments:\n$comments";
  $headers = 'From: '.($config['smtp']['from_email'] ?? 'no-reply@'.$_SERVER['HTTP_HOST'])."\r\n";
  if(mail($to, $subject, $message, $headers)){
    $sent = true;
  } else {
    $send_error .= ' mail() failed.';
  }
}

// If email sent, mark infographic_sent
if($sent){
  try{ $pdo->prepare('UPDATE contacts SET infographic_sent=1 WHERE id=?')->execute([$id]); } catch(Exception $e){}
  echo json_encode(['success'=>true,'message'=>'Gracias, correo enviado.']);
} else {
  http_response_code(500);
  error_log('Send error: '.$send_error);
  echo json_encode(['error'=>'Could not send email','detail'=>$send_error]);
}
