<?php
// ============================================================
//  Подключение к PostgreSQL (Supabase через IPv4 Pooler)
// ============================================================
define('SUPABASE_URL', 'https://xssjhmkwfigswqtjibel.supabase.co'); // Твой URL проекта
define('SUPABASE_KEY', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Inhzc2pobWt3Zmlnc3dxdGppYmVsIiwicm9sZSI6ImFub24iLCJpYXQiOjE3Nzk5MTE0ODMsImV4cCI6MjA5NTQ4NzQ4M30.h2clZbhEHITlEkmCS2X43PrB-wAfkEnrA2kloC_IDkQ'); // <-- Скопируй ключ anon из Project Settings -> API
define('DB_HOST', 'aws-1-ap-south-1.pooler.supabase.com'); 
define('DB_PORT', '6543'); 
define('DB_NAME', 'postgres');
define('DB_USER', 'postgres.xssjhmkwfigswqtjibel'); 
define('DB_PASS', 'Astera7540@'); // <-- Вставь сюда свой пароль

function getDB() {
    static $db = null;
    if ($db === null) {
        try {
            // Формируем DSN строку с обязательным параметром sslmode=require
            $dsn = 'pgsql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';sslmode=require';
            $db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            die(
                '<div style="font-family:sans-serif;padding:40px;background:#fff3f3;border:1px solid #f00;margin:40px;border-radius:4px;">' .
                '<h2 style="color:#c00">Ошибка подключения к базе данных Supabase</h2>' .
                '<p>Убедитесь, что параметры подключения верны и пароль указан правильно.</p>' .
                '<p style="color:#666;font-size:13px;">' . htmlspecialchars($e->getMessage()) . '</p>' .
                '</div>'
            );
        }
    }
    return $db;
}
?>
