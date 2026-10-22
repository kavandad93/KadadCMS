<?php
declare(strict_types=1);
require __DIR__.'/store-common.php';
$pdo=store_setup();$siteName=store_setting($pdo,'site_name','Kadad');$products=$pdo->query('SELECT id,name,description,stock FROM products ORDER BY updated_at DESC')->fetchAll();
store_header($pdo,$siteName);
?>
<section style="border-radius:26px;padding:clamp(26px,6vw,58px);color:#fff;background:linear-gradient(120deg,#182e49,#315b78);margin-bottom:32px">
 <small style="color:#b9e5e4">به <?=e($siteName)?> خوش آمدید</small>
 <h1 style="font-size:clamp(30px,5vw,46px);margin:12px 0">محصول موردنظرت را پیدا کن.</h1>
 <p style="line-height:2;color:#e0eaf2;max-width:650px">محصولات را بررسی کن، جزئیاتشان را ببین و رایگان سفارش بده.</p>
 <a class="shop-btn" href="cart.php">مشاهده سبد خرید (<?=store_cart_count()?>)</a>
</section>
<section><div style="display:flex;justify-content:space-between;align-items:end;gap:12px;flex-wrap:wrap;margin-bottom:18px"><div><h2 style="margin:0">محصولات فروشگاه</h2><p class="shop-muted">همهٔ محصولات فعلاً رایگان‌اند · ۰ تومان</p></div></div>
<?php if(!$products):?><div class="shop-empty"><b>هنوز محصولی برای نمایش وجود ندارد</b><p>به‌زودی محصولات اینجا نمایش داده می‌شوند.</p></div>
<?php else:?><div class="shop-grid"><?php foreach($products as $p):?><a class="shop-card" href="product.php?id=<?=(int)$p['id']?>"><div style="width:46px;height:46px;display:grid;place-items:center;border-radius:14px;background:#eef5f7;color:#286875;font-size:24px;margin-bottom:16px">◇</div><h3><?=e($p['name'])?></h3><?php if(trim((string)$p['description'])!==''):?><p><?=e(mb_strimwidth((string)$p['description'],0,150,'…','UTF-8'))?></p><?php endif;?><div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin-top:18px;padding-top:14px;border-top:1px solid #edf1f5"><span class="shop-price">رایگان · ۰ تومان</span><span class="shop-muted"><?=((int)$p['stock']>0)?'مشاهده و سفارش ←':'ناموجود'?></span></div></a><?php endforeach;?></div><?php endif;?>
</section>
<?php store_footer(); ?>
