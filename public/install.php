<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
$configFile = __DIR__.'/../app/config.php';
if (is_file($configFile)) { http_response_code(403); exit('نصب قبلاً انجام شده است. برای امنیت فایل install.php را حذف کنید.'); }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 check_csrf();
 $host=trim((string)($_POST['db_host']??'127.0.0.1')); $name=trim((string)($_POST['db_name']??'')); $user=trim((string)($_POST['db_user']??'')); $pass=(string)($_POST['db_pass']??'');
 $admin=trim((string)($_POST['admin_user']??'')); $password=(string)($_POST['admin_pass']??'');
 if ($name==='' || !preg_match('/^[a-zA-Z0-9_]+$/',$name) || $user==='' || $admin==='' || strlen($admin)>80 || strlen($password)<12) {
  $error='نام دیتابیس، کاربر دیتابیس و مدیر الزامی است؛ رمز مدیر باید حداقل ۱۲ نویسه داشته باشد.';
 } else {
  try {
   $pdo=new PDO('mysql:host='.$host.';dbname='.$name.';charset=utf8mb4',$user,$pass,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_EMULATE_PREPARES=>false]);
   $sql=file_get_contents(__DIR__.'/../database/schema.sql');
   foreach(array_filter(array_map('trim',explode(';',(string)$sql))) as $statement) $pdo->exec($statement);
   $stmt=$pdo->prepare('INSERT INTO users(username,password_hash) VALUES(?,?)');
   $stmt->execute([$admin,password_hash($password,PASSWORD_DEFAULT)]);
   $config="<?php\nreturn ".var_export(['host'=>$host,'name'=>$name,'user'=>$user,'pass'=>$pass],true).";\n";
   if(file_put_contents($configFile,$config,LOCK_EX)===false) throw new RuntimeException('Cannot write config');
   @chmod($configFile,0600);
   notice('نصب اولیه انجام شد؛ برای امنیت install.php را از سرور حذف کنید.');
   go('admin/index.php?page=login');
  } catch(Throwable $e) { $error='نصب انجام نشد. اطلاعات دیتابیس و دسترسی نوشتن پوشه app را بررسی کنید.'; }
 }
}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>نصب Kadad CMS</title><link rel="stylesheet" href="assets/admin.css"></head><body class="auth-page"><main class="auth-card"><div class="logo">K</div><p class="eyebrow">KADAD CMS · 0.0.1</p><h1>راه‌اندازی کاداد</h1><p class="muted">اطلاعات دیتابیس و مدیر اصلی را وارد کن.</p><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><label>میزبان دیتابیس<input name="db_host" value="<?=e($_POST['db_host']??'127.0.0.1')?>" required></label><label>نام دیتابیس<input name="db_name" required value="<?=e($_POST['db_name']??'')?>"></label><label>کاربر دیتابیس<input name="db_user" required value="<?=e($_POST['db_user']??'')?>"></label><label>رمز دیتابیس<input type="password" name="db_pass" autocomplete="new-password"></label><hr><label>نام کاربری مدیر<input name="admin_user" required maxlength="80" value="<?=e($_POST['admin_user']??'')?>"></label><label>رمز مدیر (حداقل ۱۲ نویسه)<input type="password" name="admin_pass" required minlength="12" autocomplete="new-password"></label><button class="button primary full">نصب و ایجاد مدیر</button></form></main></body></html>
