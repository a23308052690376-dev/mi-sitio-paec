<?php
// submit_form.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // PHPMailer via Composer
// DB config
$host = 'localhost';
$db   = 'your_db';
$user = 'your_user';
$pass = 'your_pass';
$dsn  = "mysql:host=$host;dbname=$db;charset=utf8mb4";

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$comments = $_POST['comments'] ?? '';

if(!$name || !filter_var($email, FILTER_VALIDATE_EMAIL)){
  http_response_code(400);
  echo json_encode(['error'=>'Invalid input']);
  exit;
}

try {
  $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);

  $stmt = $pdo->prepare('INSERT INTO contacts (name,email,comments) VALUES (?,?,?)');
  $stmt->execute([$name,$email,$comments]);
  $id = $pdo->lastInsertId();

  // Send email with infografia.pdf
  $mail = new PHPMailer(true);
  $mail->setFrom('no-reply@tu-dominio.com','Tu sitio');
  $mail->addAddress($email, $name);
  $mail->Subject = 'Infografía sobre Comer con Conciencia';
  $mail->Body = 'Gracias por registrarte. Adjuntamos una infografía con información relevante.';
  $mail->addAttachment(__DIR__.'/infografia.pdf', 'infografia.pdf');
  $mail->isSMTP();
  $mail->Host = 'smtp.tu-proveedor.com';
  $mail->SMTPAuth = true;
  $mail->Username = 'smtp-user';
  $mail->Password = 'smtp-pass';
  $mail->SMTPSecure = 'tls';
  $mail->Port = 587;
  $mail->send();

  // mark sent (optional)
  $pdo->prepare('UPDATE contacts SET infographic_sent=1 WHERE id=?')->execute([$id]);

  header('Content-Type: application/json');
  echo json_encode(['success'=>true,'message'=>'Gracias, correo enviado.']);
} catch(Exception $e){
  http_response_code(500);
  echo json_encode(['error'=>'Server error: '.$e->getMessage()]);
}