<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';
$products=[];$siteName='Kadad';$logo='';
if(is_file(__DIR__.'/../app/config.php')) {
 try {
  $pdo=db();
  $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
  $s=$pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key=?');
  $s->execute(['site_name']);$v=$s->fetchColumn();if($v!==false&&trim((string)$v)!=='')$siteName=(string)$v;
  $s->execute(['site_logo']);$v=$s->fetchColumn();if($v!==false)$logo=(string)$v;
  $products=$pdo->query('SELECT id,name,description,price,stock FROM products ORDER BY updated_at DESC')->fetchAll();
 } catch(Throwable $e) { $products=[]; }
}
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="<?=e($siteName)?>؛ فروشگاه محصولات">
<title><?=e($siteName)?></title>
<link rel="stylesheet" href="assets/admin.css">
<style>
:root{font-family:Vazirmatn,Tahoma,sans-serif;color:#172b3a;background:#f6f8fc}body{margin:0;font-size:15px}.site-header{position:sticky;top:0;background:#ffffffed;backdrop-filter:blur(12px);border-bottom:1px solid #e7edf3;z-index:2}.site-header-inner,.site-main,.site-footer{width:min(1120px,calc(100% - 36px));margin:auto}.site-header-inner{min-height:78px;display:flex;align-items:center;justify-content:space-between;gap:20px}.site-brand{display:flex;align-items:center;gap:12px;color:#153747;text-decoration:none;font-size:22px;font-weight:800}.site-logo{width:44px;height:44px;object-fit:contain;border-radius:13px}.site-mark{display:grid;place-items:center;width:44px;height:44px;border-radius:14px;background:linear-gradient(135deg,#5e51df,#31a5a0);color:#fff;font-weight:900;font-size:22px}.site-main{padding:38px 0 64px}.intro{border-radius:28px;padding:clamp(28px,6vw,62px);color:#fff;background:radial-gradient(circle at 90% 0,#ffffff20,transparent 30%),linear-gradient(120deg,#182e49,#315b78);box-shadow:0 20px 45px #1b38521c}.intro .eyebrow{color:#b9e5e4;letter-spacing:0}.intro h1{font-size:clamp(30px,5vw,48px);line-height:1.35;margin:12px 0}.intro p{color:#e0eaf2;line-height:2;max-width:650px;margin:0}.section-head{display:flex;align-items:end;justify-content:space-between;gap:16px;margin:38px 0 18px}.section-head h2{font-size:24px;margin:0}.section-head p{margin:6px 0 0;color:#6d7f90;font-size:13px}.products{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,250px),1fr));gap:18px}.product{background:#fff;border:1px solid #e6edf3;border-radius:20px;padding:22px;box-shadow:0 8px 25px #18364d08;transition:transform .18s,box-shadow .18s}.product:hover{transform:translateY(-3px);box-shadow:0 14px 30px #18364d12}.product-symbol{width:46px;height:46px;display:grid;place-items:center;border-radius:15px;background:#eef5f7;color:#286875;font-size:22px;margin-bottom:18px}.product h3{margin:0 0 10px;font-size:18px;overflow-wrap:anywhere}.product p{margin:0;color:#637585;line-height:1.9;font-size:13px;white-space:pre-wrap;overflow-wrap:anywhere}.product-bottom{display:flex;justify-content:space-between;align-items:center;gap:10px;border-top:1px solid #edf1f5;padding-top:16px;margin-top:18px}.price{font-size:17px;font-weight:800;color:#087e72}.stock{font-size:11px;color:#7b8b97}.empty{padding:46px 22px;text-align:center;border:1px dashed #cbd8e2;border-radius:20px;background:#fff;color:#607482}.empty strong{display:block;font-size:17px;color:#243e51;margin-bottom:8px}.site-footer{padding:24px 0 34px;border-top:1px solid #e3eaf0;color:#71818c;font-size:12px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap}@media(max-width:600px){.site-header-inner{min-height:68px}.site-main{padding-top:22px}.section-head{align-items:start;flex-direction:column}.intro{border-radius:20px}.site-footer{flex-direction:column}}
</style>
</head>
<body>
<header class="site-header"><div class="site-header-inner"><a class="site-brand" href="./"><?php if($logo):?><img class="site-logo" src="<?=e($logo)?>" alt="لوگوی <?=e($siteName)?>"><?php else:?><span class="site-mark">K</span><?php endif;?><span><?=e($siteName)?></span></a></div></header>
<main class="site-main">
<section class="intro"><small class="eyebrow">به <?=e($siteName)?> خوش آمدید</small><h1>محصول موردنظرت را پیدا کن.</h1><p>محصولات موجود را بررسی کن و جزئیات هرکدام را ببین. این فروشگاه به‌تدریج امکانات بیشتری دریافت می‌کند.</p></section>
<section><div class="section-head"><div><h2>محصولات فروشگاه</h2><p>محصولات ثبت‌شده در <?=e($siteName)?></p></div></div>
<?php if(!$products):?><div class="empty"><strong>هنوز محصولی برای نمایش وجود ندارد</strong>به‌زودی محصولات اینجا نمایش داده می‌شوند.</div><?php else:?><div class="products"><?php foreach($products as $product):?><article class="product"><div class="product-symbol">◇</div><h3><?=e($product['name'])?></h3><?php if(trim((string)$product['description'])!==''):?><p><?=e($product['description'])?></p><?php endif;?><div class="product-bottom"><span class="price"><?=e(number_format((float)$product['price'],2))?></span><span class="stock"><?=((int)$product['stock']>0)?'موجود':'ناموجود'?></span></div></article><?php endforeach;?></div><?php endif;?>
</section>
</main><footer class="site-footer"><span>© <?=date('Y')?> <?=e($siteName)?>. تمامی حقوق محفوظ است.</span><span>ساخته‌شده با Kadad CMS</span></footer>
</body></html>