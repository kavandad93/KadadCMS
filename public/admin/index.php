<?php
declare(strict_types=1);
require __DIR__.'/../../app/bootstrap.php';
$configFile = __DIR__.'/../../app/config.php';
if (!is_file($configFile)) go('../install.php');
$page=(string)($_GET['page']??'dashboard');

function ensure_settings(PDO $pdo): void {
 $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 foreach (['site_name'=>'Kadad','site_logo'=>''] as $key=>$value) {
  $s=$pdo->prepare('INSERT IGNORE INTO site_settings(setting_key,setting_value) VALUES(?,?)');
  $s->execute([$key,$value]);
 }
}
function setting(PDO $pdo,string $key,string $fallback=''): string {
 $s=$pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key=?');
 $s->execute([$key]); $v=$s->fetchColumn(); return $v===false?$fallback:(string)$v;
}
if($page==='login') {
 $error='';
 if($_SERVER['REQUEST_METHOD']==='POST') {
  check_csrf(); $username=trim((string)($_POST['username']??'')); $password=(string)($_POST['password']??'');
  try { $s=db()->prepare('SELECT id,username,password_hash FROM users WHERE username=? LIMIT 1'); $s->execute([$username]); $u=$s->fetch();
   if($u && password_verify($password,$u['password_hash'])) { session_regenerate_id(true); $_SESSION['user_id']=(int)$u['id']; $_SESSION['username']=$u['username']; go('index.php'); }
   $error='نام کاربری یا رمز عبور اشتباه است.';
  } catch(Throwable $e) { $error='ورود ممکن نیست؛ تنظیمات دیتابیس را بررسی کن.'; }
 }
 $message=(string)($_SESSION['_notice']??''); unset($_SESSION['_notice']);
 ?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ورود مدیر</title><link rel="stylesheet" href="../assets/admin.css"></head><body class="auth-page"><main class="auth-card"><div class="logo">K</div><p class="eyebrow">KADAD CMS</p><h1>ورود به مدیریت</h1><p class="muted">برای ادامه وارد حساب خودت شو.</p><?php if($message):?><div class="alert"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?><form method="post"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><label>نام کاربری<input name="username" required autocomplete="username" autofocus></label><label>رمز عبور<input type="password" name="password" required autocomplete="current-password"></label><button class="button primary full">ورود</button></form></main></body></html><?php exit;
}
if($page==='logout') { if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);exit('Method not allowed');} check_csrf(); $_SESSION=[]; session_destroy(); go('index.php?page=login'); }
require_login();
$pdo=db(); ensure_settings($pdo); $error='';
if($_SERVER['REQUEST_METHOD']==='POST') {
 check_csrf(); $action=(string)($_POST['action']??'');
 try {
  if($action==='save-product') {
   $id=(int)($_POST['id']??0); $name=trim((string)($_POST['name']??'')); $sku=trim((string)($_POST['sku']??'')); $price=(string)($_POST['price']??'0'); $stock=filter_var($_POST['stock']??0,FILTER_VALIDATE_INT); $desc=trim((string)($_POST['description']??''));
   if($name===''||!is_numeric($price)||(float)$price<0||$stock===false||$stock<0) throw new RuntimeException('نام، قیمت معتبر و موجودی صفر یا بیشتر وارد کن.');
   if($id){$s=$pdo->prepare('UPDATE products SET name=?,sku=?,price=?,stock=?,description=? WHERE id=?');$s->execute([$name,$sku,number_format((float)$price,2,'.',''),$stock,$desc,$id]);}
   else{$s=$pdo->prepare('INSERT INTO products(name,sku,price,stock,description) VALUES(?,?,?,?,?)');$s->execute([$name,$sku,number_format((float)$price,2,'.',''),$stock,$desc]);}
   notice('محصول ذخیره شد.'); go('index.php?page=products');
  } elseif($action==='delete-product') {
   $s=$pdo->prepare('DELETE FROM products WHERE id=?');$s->execute([(int)($_POST['id']??0)]);notice('محصول حذف شد.');go('index.php?page=products');
  } elseif($action==='save-settings') {
   $siteName=trim((string)($_POST['site_name']??'')); if($siteName===''||mb_strlen($siteName,'UTF-8')>100) throw new RuntimeException('نام سایت باید بین ۱ تا ۱۰۰ نویسه باشد.');
   $s=$pdo->prepare('INSERT INTO site_settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');$s->execute(['site_name',$siteName]);
   if(isset($_POST['remove_logo'])) { $oldLogo=setting($pdo,'site_logo'); if($oldLogo!=='') { $oldPath=__DIR__.'/../'.ltrim(str_replace('../','',$oldLogo),'/'); if(is_file($oldPath)) @unlink($oldPath); } $s->execute(['site_logo','']); }
   if(isset($_FILES['site_logo']) && $_FILES['site_logo']['error']!==UPLOAD_ERR_NO_FILE) {
    if($_FILES['site_logo']['error']!==UPLOAD_ERR_OK || $_FILES['site_logo']['size']>2*1024*1024) throw new RuntimeException('لوگو باید حداکثر ۲ مگابایت باشد.');
    $tmp=$_FILES['site_logo']['tmp_name']; $mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);
    $extensions=['image/png'=>'png','image/jpeg'=>'jpg','image/webp'=>'webp'];
    if(!isset($extensions[$mime])) throw new RuntimeException('فرمت لوگو باید PNG، JPG یا WebP باشد.');
    $dir=__DIR__.'/../uploads'; if(!is_dir($dir) && !mkdir($dir,0755,true) && !is_dir($dir)) throw new RuntimeException('ساخت پوشهٔ لوگو ممکن نشد.');
    $file='logo-'.bin2hex(random_bytes(12)).'.'.$extensions[$mime];
    if(!move_uploaded_file($tmp,$dir.'/'.$file)) throw new RuntimeException('ذخیرهٔ لوگو ناموفق بود.');
    $s->execute(['site_logo','uploads/'.$file]);
   }
   notice('تنظیمات سایت ذخیره شد.');go('index.php?page=settings');
  } elseif($action==='create-user') {
   $username=trim((string)($_POST['username']??''));$password=(string)($_POST['password']??'');
   if(!preg_match('/^[p{L}p{N}_.-]{3,80}$/u',$username)||strlen($password)<12) throw new RuntimeException('نام کاربری باید ۳ تا ۸۰ نویسه و رمز حداقل ۱۲ نویسه باشد.');
   $s=$pdo->prepare('INSERT INTO users(username,password_hash) VALUES(?,?)');$s->execute([$username,password_hash($password,PASSWORD_DEFAULT)]);
   notice('حساب کاربری ساخته شد.');go('index.php?page=accounts');
  }
 } catch(Throwable $e) { $error=$e instanceof RuntimeException?$e->getMessage():'عملیات انجام نشد؛ ممکن است نام کاربری تکراری باشد یا دسترسی فایل کافی نباشد.'; }
}
$allowed=['dashboard','products','product-form','settings','accounts'];if(!in_array($page,$allowed,true))$page='dashboard';
$edit=null;if($page==='product-form'&&isset($_GET['id'])){$s=$pdo->prepare('SELECT * FROM products WHERE id=?');$s->execute([(int)$_GET['id']]);$edit=$s->fetch()?:null;}
$stats=['products'=>(int)$pdo->query('SELECT COUNT(*) FROM products')->fetchColumn(),'users'=>(int)$pdo->query('SELECT COUNT(*) FROM users WHERE phone IS NULL')->fetchColumn()];
$message=(string)($_SESSION['_notice']??'');unset($_SESSION['_notice']);
$titles=['dashboard'=>'نمای کلی','products'=>'محصولات','product-form'=>'محصول','settings'=>'تنظیمات سایت','accounts'=>'حساب‌های کاربری'];
$siteName=setting($pdo,'site_name','Kadad');$logo=setting($pdo,'site_logo','');
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($titles[$page])?> · <?=e($siteName)?></title><link rel="stylesheet" href="../assets/admin.css"></head><body><div class="layout"><aside class="sidebar"><a class="brand" href="index.php"><span class="logo mini"><?php if($logo):?><img src="../<?=e($logo)?>" alt="" style="width:100%;height:100%;object-fit:contain;border-radius:inherit"><?php else:?>K<?php endif;?></span><span><b><?=e($siteName)?></b><small>مدیریت سایت</small></span></a><p class="nav-label">مدیریت</p><a class="nav <?=$page==='dashboard'?'active':''?>" href="index.php">▦ <span>نمای کلی</span></a><a class="nav <?=in_array($page,['products','product-form'],true)?'active':''?>" href="index.php?page=products">◇ <span>محصولات</span></a><a class="nav" href="orders.php">▤ <span>سفارش‌ها</span></a><a class="nav" href="index.php?page=accounts">♙ <span>حساب‌های مدیریت</span></a><a class="nav <?=$page==='settings'?'active':''?>" href="index.php?page=settings">⚙ <span>تنظیمات سایت</span></a><div class="side-bottom"><span class="avatar"><?=e(mb_substr((string)($_SESSION['username']??'K'),0,1,'UTF-8'))?></span><span class="username"><?=e($_SESSION['username']??'مدیر')?><small>مدیر سایت</small></span><form method="post" action="index.php?page=logout"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><button class="logout" title="خروج">↪</button></form></div></aside><main class="main"><header class="topbar"><div><small class="eyebrow"><?=e($siteName)?></small><h1><?=e($titles[$page])?></h1></div><a class="button secondary" href="../" target="_blank" rel="noopener">مشاهدهٔ سایت ↗</a></header><section class="content"><?php if($message):?><div class="alert"><?=e($message)?></div><?php endif;?><?php if($error):?><div class="alert error"><?=e($error)?></div><?php endif;?>
<?php if($page==='dashboard'):?><section class="hero"><div><small class="eyebrow">پنل مدیریت</small><h2>سلام، <?=e($_SESSION['username']??'مدیر')?> 👋</h2><p>محصولات، حساب‌ها و ظاهر سایت را از اینجا مدیریت کن.</p></div><span class="hero-mark">K</span></section><div class="stats"><article class="stat"><span>همهٔ محصولات</span><strong><?=$stats['products']?></strong><a href="index.php?page=products">مدیریت محصولات ←</a></article><article class="stat"><span>حساب‌های کاربری</span><strong><?=$stats['users']?></strong><a href="index.php?page=accounts">مدیریت حساب‌ها ←</a></article></div><section class="panel"><h3>دسترسی سریع</h3><div class="quick"><a href="index.php?page=product-form"><b>＋</b><span>افزودن محصول<small>ثبت کالا در فروشگاه</small></span></a><a href="index.php?page=settings"><b>⚙</b><span>تنظیمات سایت<small>نام و لوگوی سایت</small></span></a></div></section>
<?php elseif($page==='products'): $rows=$pdo->query('SELECT id,name,sku,price,stock FROM products ORDER BY updated_at DESC')->fetchAll();?><section class="panel"><div class="panel-head"><div><h3>محصولات فروشگاه</h3><p>مدیریت نام، قیمت، موجودی و توضیحات محصولات</p></div><a class="button primary" href="index.php?page=product-form">＋ محصول جدید</a></div><?php if(!$rows):?><div class="empty"><b>هنوز محصولی ثبت نشده</b><p>با افزودن محصول، فروشگاهت را راه‌اندازی کن.</p></div><?php else:?><div class="table-wrap"><table><thead><tr><th>نام محصول</th><th>شناسهٔ کالا</th><th>قیمت</th><th>موجودی</th><th>عملیات</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['name'])?></b></td><td><?=e($r['sku']?:'—')?></td><td><?=e(number_format((float)$r['price'],2))?></td><td><?=(int)$r['stock']?></td><td class="actions"><a href="index.php?page=product-form&id=<?=(int)$r['id']?>">ویرایش</a><form method="post" onsubmit="return confirm('این محصول حذف شود؟')"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="delete-product"><input type="hidden" name="id" value="<?=(int)$r['id']?>"><button class="text-button danger">حذف</button></form></td></tr><?php endforeach;?></tbody></table></div><?php endif;?></section>
<?php elseif($page==='product-form'):?><section class="panel form-panel"><div class="panel-head"><div><h3><?=$edit?'ویرایش محصول':'افزودن محصول'?></h3><p>اطلاعات محصول را وارد کن.</p></div><a class="button secondary" href="index.php?page=products">بازگشت</a></div><form method="post" class="form"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="save-product"><input type="hidden" name="id" value="<?=(int)($edit['id']??0)?>"><label>نام محصول<input name="name" maxlength="200" required value="<?=e($edit['name']??'')?>"></label><div class="fields"><label>شناسهٔ کالا (SKU)<input name="sku" maxlength="100" value="<?=e($edit['sku']??'')?>"></label><label>قیمت<input type="number" name="price" min="0" step="0.01" required value="<?=e($edit['price']??'0.00')?>"></label><label>موجودی<input type="number" name="stock" min="0" step="1" required value="<?=e($edit['stock']??0)?>"></label></div><label>توضیحات محصول<textarea name="description" rows="7"><?=e($edit['description']??'')?></textarea></label><div class="form-actions"><button class="button primary">ذخیرهٔ محصول</button><a class="button secondary" href="index.php?page=products">لغو</a></div></form></section>
<?php elseif($page==='settings'):?><section class="panel form-panel"><div class="panel-head"><div><h3>تنظیمات عمومی</h3><p>نام و لوگویی که در هدر و صفحهٔ اصلی نمایش داده می‌شوند.</p></div></div><form method="post" enctype="multipart/form-data" class="form"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="save-settings"><label>نام سایت<input name="site_name" maxlength="100" required value="<?=e($siteName)?>"></label><label>لوگوی سایت (PNG، JPG یا WebP؛ حداکثر ۲ مگابایت)<input type="file" name="site_logo" accept="image/png,image/jpeg,image/webp"></label><?php if($logo):?><div><p>لوگوی فعلی</p><img src="../<?=e($logo)?>" alt="لوگوی فعلی" style="max-width:180px;max-height:100px;object-fit:contain;background:#f5f6fb;border-radius:12px;padding:10px"><label style="display:flex;grid-template-columns:auto 1fr;align-items:center;margin-top:12px"><input type="checkbox" name="remove_logo" value="1" style="width:auto"> حذف لوگوی فعلی</label></div><?php endif;?><p class="muted">اگر لوگوی جدیدی انتخاب نکنی، لوگوی فعلی حفظ می‌شود.</p><div class="form-actions"><button class="button primary">ذخیرهٔ تنظیمات</button></div></form></section>
<?php elseif($page==='accounts'): $rows=$pdo->query('SELECT id,username,created_at FROM users WHERE phone IS NULL ORDER BY id ASC')->fetchAll();?><div class="panel"><div class="panel-head"><div><h3>حساب‌های مدیریت</h3><p>ایجاد حساب تازه برای ورود به پنل مدیریت</p></div></div><form method="post" class="form" style="max-width:560px;margin-bottom:28px"><input type="hidden" name="_csrf" value="<?=e(csrf())?>"><input type="hidden" name="action" value="create-user"><label>نام کاربری<input name="username" minlength="3" maxlength="80" required></label><label>رمز عبور موقت (حداقل ۱۲ نویسه)<input type="password" name="password" minlength="12" required autocomplete="new-password"></label><button class="button primary">＋ ساخت حساب</button></form><div class="table-wrap"><table><thead><tr><th>نام کاربری</th><th>تاریخ ساخت</th></tr></thead><tbody><?php foreach($rows as $r):?><tr><td><b><?=e($r['username'])?></b><?=((int)$r['id']===(int)($_SESSION['user_id']??0))?' (حساب فعلی)':''?></td><td><?=e($r['created_at'])?></td></tr><?php endforeach;?></tbody></table></div></div><?php endif;?>
<footer><?=e($siteName)?> · Kadad CMS 0.0.1</footer></section></main></div></body></html>