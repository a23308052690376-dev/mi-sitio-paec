<?php
// config.php - Mantenlo fuera del control de versiones (NO subir a GitHub)

return [
    'db' => [
        // Valores tomados del panel de InfinityFree (ver imagen proporcionada)
        'host' => 'sql206.infinityfree.com',
        'name' => 'if0_40503857_mi_sitio_db',
        'user' => 'if0_40503857',
        'pass' => 'JYhIIxLzty0Bt',
    ],
    
    // 2. REEMPLAZA estos valores con tus credenciales de SMTP
    //    Si NO usas correo, puedes dejar esta sección vacía.
    'smtp' => [
        'host' => 'smtp.ejemplo.net', 
        'username' => 'tu_usuario_smtp',
        'password' => 'TU_CLAVE_API_O_PASSWORD',
        'port' => 587,
        'secure' => 'tls',
        'from_email' => 'no-reply@tu-dominio.com',
        'from_name' => 'Mi Sitio Web',
    ],
];