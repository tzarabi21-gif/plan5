<?php
require __DIR__.'/config.php';
try{
 $pdo=new PDO("mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4",DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
 $sql=[
 "CREATE TABLE IF NOT EXISTS users(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,phone VARCHAR(30) NOT NULL UNIQUE,password VARCHAR(255) NOT NULL,role ENUM('student','admin','parent') NOT NULL DEFAULT 'student',created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
 "CREATE TABLE IF NOT EXISTS settings(`key` VARCHAR(100) PRIMARY KEY,value LONGTEXT NOT NULL)",
 "CREATE TABLE IF NOT EXISTS pages(id INT AUTO_INCREMENT PRIMARY KEY,slug VARCHAR(120) UNIQUE,title VARCHAR(200),body LONGTEXT)",
 "CREATE TABLE IF NOT EXISTS tasks(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(160),detail TEXT,due_date DATE NULL,done TINYINT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id))",
 "CREATE TABLE IF NOT EXISTS notes(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(160),detail TEXT,due_date DATE NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id))",
 "CREATE TABLE IF NOT EXISTS exams(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(160),detail TEXT,due_date DATE NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id))",
 "CREATE TABLE IF NOT EXISTS plans(id INT AUTO_INCREMENT PRIMARY KEY,user_id INT NOT NULL,title VARCHAR(160),detail TEXT,due_date DATE NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,INDEX(user_id))"
 ];foreach($sql as $q)$pdo->exec($q);
 $pdo->prepare("INSERT IGNORE INTO users(name,phone,password,role) VALUES(?,?,?,'admin')")->execute(['مدیر NEXA','09000000000',password_hash('ChangeMe-Immediately-2026',PASSWORD_DEFAULT)]);
 echo '<meta charset="utf-8"><body dir="rtl" style="font:18px Tahoma;background:#080b16;color:#fff;padding:40px"><h1>نصب دیتابیس موفق بود</h1><p>حساب اولیه مدیر ساخته شد. شماره آزمایشی: 09000000000</p><p>رمز اولیه فقط برای نصب است؛ پس از ورود از لینک «تغییر رمز» در نوار بالا فوراً آن را عوض کنید. تا قبل از انتشار، نصب را عمومی نکنید.</p><a style="color:#00e5c0" href="index.php">ورود به NEXA</a></body>';
}catch(Throwable $e){http_response_code(500);echo 'خطا در اتصال دیتابیس: '.htmlspecialchars($e->getMessage());}
