<?php

return [
    // SMTP Server Settings
    'smtp_host' => 'smtp.gmail.com',              // Ganti dengan SMTP host Anda
    'smtp_port' => 587,                           // Port: 587 (TLS) atau 465 (SSL)
    'smtp_secure' => 'tls',                       // 'tls' atau 'ssl'
    'smtp_auth' => true,                          // Enable SMTP authentication
    
    // Email Credentials
    'smtp_username' => 'sulton843@gmail.com',    // Email pengirim
    'smtp_password' => 'auxn xbox mguc auvz',       // App Password (bukan password biasa)
    
    // Sender Info
    'from_email' => 'noreply@lampungsmart.go.id', // Email pengirim yang ditampilkan
    'from_name' => 'LampungSmart System',         // Nama pengirim
    
    // Admin Email
    'admin_email' => 'sulton843@gmail.com',       // Email admin penerima
    'admin_name' => 'LampungSmart System',                     // Nama admin
    
    // SMTP Debug (0 = off, 1 = client, 2 = client + server)
    'smtp_debug' => 0,                            // Set 2 untuk debug, 0 untuk production
];
