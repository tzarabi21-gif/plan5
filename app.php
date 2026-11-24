<?php
declare(strict_types=1);
session_start();
require_once __DIR__.'/config.php';
function db(): PDO {
 static $pdo=null; if($pdo) return $pdo;
 $dsn="mysql:host=".DB_HOST.";port=".DB_PORT.";dbname=".DB_NAME.";charset=utf8mb4";
 $pdo=new PDO($dsn,DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);
 return $pdo;
}
function e($v): string { return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8'); }
function setting(string $key,string $default=''): string {
 $s=db()->prepare("SELECT value FROM settings WHERE `key`=?"); $s->execute([$key]); $r=$s->fetch(); return $r ? (string)$r['value'] : $default;
}
function save_setting(string $key,string $value): void {
 $s=db()->prepare("INSERT INTO settings(`key`,value) VALUES(?,?) ON DUPLICATE KEY UPDATE value=VALUES(value)"); $s->execute([$key,$value]);
}
function user(): ?array { return $_SESSION['user'] ?? null; }
function require_login(): void { if(!user()){header('Location: ?page=login');exit;} }
function require_admin(): void { require_login(); if((user()['role']??'')!=='admin'){http_response_code(403);exit('دسترسی مجاز نیست');} }
function csrf(): string { if(empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32)); return $_SESSION['csrf']; }
function verify_csrf(): void { if(!hash_equals(csrf(),$_POST['csrf']??'')){http_response_code(419);exit('درخواست نامعتبر است');} }
function flash(string $m): void { $_SESSION['flash']=$m; }
function go(string $p): never { header('Location: ?page='.$p); exit; }
function layout(string $title,string $content): void {
 $name=setting('site_name','NEXA'); $accent=setting('accent_color','#00E5C0'); $purple=setting('purple_color','#A855F7');
 echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="'.e(setting('seo_description','NEXA؛ فضای هوشمند برنامه‌ریزی و یادگیری')).'"><title>'.e($title).' | '.e($name).'</title><link rel="manifest" href="manifest.json"><meta name="theme-color" content="#080b16"><link rel="stylesheet" href="assets/style.css"><style>:root{--neon:'.e($accent).';--purple:'.e($purple).';}</style></head><body><div class="glow g1"></div><div class="glow g2"></div><header class="top"><a class="brand" href="?page=dashboard">NEXA<span>✦</span></a><nav><a href="?page=dashboard">داشبورد</a><a href="?page=planner">برنامه‌ریز</a><a href="?page=tasks">وظایف</a><a href="?page=exams">امتحانات</a><a href="?page=notes">یادداشت</a>'.(user()&&user()['role']==='admin'?'<a href="?page=admin">مدیریت</a>':'').'</nav><div>'.(user()?'<span class="muted">'.e(user()['name']).'</span> <a class="btn small" href="?page=logout">خروج</a>':'<a class="btn small" href="?page=login">ورود</a> <a class="btn small primary" href="?page=register">ثبت‌نام</a>').'</div></header><main class="wrap"><div class="pagehead"><div><div class="eyebrow">YOUR SMART STUDY SPACE</div><h1>'.e($title).'</h1></div></div>'.(!empty($_SESSION['flash'])?'<div class="notice">'.e($_SESSION['flash']).'</div>':'').$content.'</main><footer>NEXA · یادگیری هوشمند، قدم‌به‌قدم</footer><script src="assets/app.js"></script></body></html>';
 unset($_SESSION['flash']);
}
