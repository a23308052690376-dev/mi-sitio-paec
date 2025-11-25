<?php
// config.php - Mantenlo fuera del control de versiones (NO subir a GitHub)

return [
    'db' => [
        // 1. REEMPLAZA estos valores con los que te dio InfinityFree
        //    (Hostname, Nombre de BD completo, Nombre de Usuario completo, Contraseña)
        'host' => 'sqlXXX.epizy.com', // Ej: el Hostname que te dio InfinityFree
        'name' => 'if0_12345678_mi_sitio_db', // Nombre de BD COMPLETO con prefijo
        'user' => 'if0_12345678', // Nombre de Usuario COMPLETO con prefijo
        'pass' => 'tu_contraseña_del_panel', // Tu contraseña de panel de InfinityFree
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