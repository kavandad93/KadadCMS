<?php
declare(strict_types=1);
require __DIR__.'/store-common.php';
$pdo=store_setup();store_customer_required();$s=$pdo->prepare('SELECT * FROM orders WHERE user_id=? ORDER BY id DESC');$s->execute([(int)$_SESSION['customer_id']]);$orders=$s->fetchAll();
store_header($pdo,'سفارش‌های من');echo '<section class="shop-panel"><h1>سفارش‌های من</h1>';
if(!$orders)echo '<div class="shop-empty">هنوز سفارشی ثبت نکرده‌ای.</div>';
else foreach($orders as $o){$items=$pdo->prepare('SELECT product_name,quantity FROM order_items WHERE order_id=?');$items->execute([$o['id']]);$method=['courier'=>'ارسال به آدرس','pickup'=>'تحویل حضوری','digital'=>'دیجیتال / آنلاین'][$o['delivery_method']]??$o['delivery_method'];echo '<article style="padding:18px 0;border-bottom:1px solid #edf1f5"><h3>سفارش شماره '.(int)$o['id'].' · <span class="shop-price">۰ تومان</span></h3><p class="shop-muted">وضعیت: '.e($o['status']==='new'?'ثبت‌شده':$o['status']).' · نحوه تحویل: '.e($method).' · زمان: '.e($o['delivery_at']).'</p><p>'.e($o['address']).'</p><ul>';foreach($items->fetchAll() as $it)echo '<li>'.e($it['product_name']).' × '.(int)$it['quantity'].'</li>';echo '</ul></article>';}
echo '</section>';store_footer();
