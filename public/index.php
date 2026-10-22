<?php
declare(strict_types=1);
require __DIR__.'/../app/bootstrap.php';

$posts = [];
$products = [];
if (is_file(__DIR__.'/../app/config.php')) {
    try {
        $pdo = db();
        $posts = $pdo->query("SELECT id,title,body,updated_at FROM content_entries WHERE status='published' ORDER BY updated_at DESC")->fetchAll();
        $products = $pdo->query('SELECT id,name,description,price,stock FROM products ORDER BY updated_at DESC')->fetchAll();
    } catch (Throwable $e) {
        http_response_code(503);
    }
}
?><!doctype html>
<html lang="fa" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="description" content="وب‌سایت کاداد؛ محتوا، ابزارها و محصولات در یک جا.">
<title>Kadad — خانه</title>
<link rel="stylesheet" href="assets/admin.css">
<style>
:root{color-scheme:light}body{margin:0;background:#f5f7fb;color:#172b3a;font-family:Vazirmatn,Tahoma,sans-serif}.site-header{background:#fff;border-bottom:1px solid #e4eaf0}.site-header-inner,.site-main,.site-footer{width:min(1100px,calc(100% - 32px));margin:auto}.site-header-inner{min-height:76px;display:flex;align-items:center;gap:14px}.site-brand{font-size:22px;font-weight:900;color:#123747;text-decoration:none}.site-brand-mark{display:inline-grid;place-items:center;background:#123747;color:#fff;border-radius:14px;width:40px;height:40px;margin-left:8px}.site-main{padding:36px 0 60px}.welcome{background:linear-gradient(120deg,#123747,#226b72);color:#fff;padding:38px;border-radius:24px}.welcome h1{font-size:clamp(28px,4vw,42px);margin:8px 0}.welcome p{line-height:2;max-width:650px;color:#e0eff1}.section-title{margin:34px 0 16px}.cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:16px}.site-card{background:#fff;border:1px solid #e4eaf0;border-radius:18px;padding:22px;overflow-wrap:anywhere}.site-card h3{margin:0 0 12px}.site-card p{white-space:pre-wrap;line-height:1.9;color:#526675}.price{font-weight:800;color:#087e72}.muted{color:#607482}.site-footer{padding:24px 0;color:#71818c;border-top:1px solid #e4eaf0}.empty{background:#fff;border:1px dashed #cbd6df;border-radius:16px;padding:22px;color:#607482}
</style>
</head>
<body>
<header class="site-header"><div class="site-header-inner"><a class="site-brand" href="./"><span class="site-brand-mark">K</span>Kadad</a></div></header>
<main class="site-main">
<section class="welcome"><small>به کاداد خوش آمدی</small><h1>یک خانه برای محتوا، ابزارها و محصولات</h1><p>این وب‌سایت با Kadad CMS ساخته شده است. مطالب منتشرشده و محصولات ثبت‌شده در اینجا نمایش داده می‌شوند.</p></section>
<section><h2 class="section-title">مطالب تازه</h2>
<?php if (!$posts): ?><div class="empty">هنوز مطلبی برای نمایش منتشر نشده است.</div><?php else: ?><div class="cards"><?php foreach ($posts as $post): ?><article class="site-card"><h3><?=e($post['title'])?></h3><p><?=e(mb_substr((string)$post['body'],0,500,'UTF-8'))?><?=mb_strlen((string)$post['body'],'UTF-8')>500?'…':''?></p></article><?php endforeach; ?></div><?php endif; ?>
</section>
<section><h2 class="section-title">محصولات</h2>
<?php if (!$products): ?><div class="empty">هنوز محصولی برای نمایش ثبت نشده است.</div><?php else: ?><div class="cards"><?php foreach ($products as $product): ?><article class="site-card"><h3><?=e($product['name'])?></h3><p><?=e($product['description'])?></p><div class="price"><?=e(number_format((float)$product['price'],2))?></div></article><?php endforeach; ?></div><?php endif; ?>
</section>
</main><footer class="site-footer">Kadad CMS · نسخهٔ آزمایشی 0.0.1</footer>
</body></html>