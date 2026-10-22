<?php
declare(strict_types=1);
require __DIR__.'/store-common.php';
$pdo=store_setup();$id=filter_input(INPUT_GET,'id',FILTER_VALIDATE_INT)?:0;
$s=$pdo->prepare('SELECT id,name,description,stock FROM products WHERE id=?');$s->execute([$id]);$product=$s->fetch();
if(!$product){http_response_code(404);store_header($pdo,'محصول پیدا نشد');echo '<section class="shop-panel"><h1>محصول پیدا نشد</h1><a class="shop-btn" href="./">بازگشت به فروشگاه</a></section>';store_footer();exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$qty=filter_var($_POST['quantity']??1,FILTER_VALIDATE_INT);$qty=max(1,min(99,(int)$qty));if((int)$product['stock']<1)notice('این محصول فعلاً ناموجود است.');else{$cart=$_SESSION['cart'];$current=(int)($cart[$id]??0);$cart[$id]=min((int)$product['stock'],$current+$qty);$_SESSION['cart']=$cart;notice('محصول به سبد خرید اضافه شد.');go('cart.php');}}
store_header($pdo,(string)$product['name']);
echo '<section class="shop-panel"><p class="shop-muted">جزئیات محصول</p><h1>'.e($product['name']).'</h1><p style="white-space:pre-wrap;line-height:2">'.nl2br(e($product['description'])).'</p><p class="shop-price" style="font-size:22px">رایگان <span style="font-size:13px;font-weight:400">· ۰ تومان</span></p><p class="shop-muted">موجودی: '.(int)$product['stock'].'</p><form method="post" class="shop-form" style="max-width:300px"><input type="hidden" name="_csrf" value="'.e(csrf()).'"><label>تعداد<input type="number" name="quantity" min="1" max="99" value="1" required></label><button class="shop-btn" '.((int)$product['stock']<1?'disabled':'').'>افزودن به سبد خرید</button></form></section>';
store_footer();
