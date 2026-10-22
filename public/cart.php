<?php
declare(strict_types=1);
require __DIR__.'/store-common.php';
$pdo=store_setup();
if($_SERVER['REQUEST_METHOD']==='POST'){
 check_csrf();$action=(string)($_POST['action']??'');$id=(int)($_POST['id']??0);
 if($action==='remove'){unset($_SESSION['cart'][$id]);notice('محصول از سبد حذف شد.');}
 elseif($action==='update-one'){$qty=max(0,min(99,(int)($_POST['quantity']??1)));if($qty===0)unset($_SESSION['cart'][$id]);else $_SESSION['cart'][$id]=$qty;notice('تعداد محصول به‌روزرسانی شد.');}
 go('cart.php');
}
$items=[];$ids=array_values(array_filter(array_map('intval',array_keys($_SESSION['cart']))));
if($ids){$marks=implode(',',array_fill(0,count($ids),'?'));$s=$pdo->prepare("SELECT id,name,stock FROM products WHERE id IN ($marks)");$s->execute($ids);foreach($s->fetchAll() as $p){$qty=min((int)($_SESSION['cart'][$p['id']]??0),(int)$p['stock']);if($qty<1){unset($_SESSION['cart'][$p['id']]);continue;}$_SESSION['cart'][$p['id']]=$qty;$p['quantity']=$qty;$items[]=$p;}}
store_header($pdo,'سبد خرید');echo '<section class="shop-panel"><h1>سبد خرید</h1><p class="shop-muted">تمام محصولات فعلاً رایگان‌اند؛ مبلغ پرداختی ۰ تومان است.</p>';
if(!$items)echo '<div class="shop-empty">سبد خریدت خالی است.<p><a class="shop-btn" href="./">دیدن محصولات</a></p></div>';
else {
 echo '<div style="overflow:auto"><table class="shop-table"><thead><tr><th>محصول</th><th>تعداد</th><th>مبلغ</th><th>عملیات</th></tr></thead><tbody>';
 foreach($items as $p){echo '<tr><td><a href="product.php?id='.(int)$p['id'].'">'.e($p['name']).'</a></td><td><form method="post" style="display:flex;gap:6px;align-items:center"><input type="hidden" name="_csrf" value="'.e(csrf()).'"><input type="hidden" name="action" value="update-one"><input type="hidden" name="id" value="'.(int)$p['id'].'"><input aria-label="تعداد" style="width:70px;padding:8px;border:1px solid #d5e0e8;border-radius:8px" type="number" min="0" max="'.(int)$p['stock'].'" name="quantity" value="'.(int)$p['quantity'].'"><button class="shop-btn alt" style="padding:8px 10px">ذخیره</button></form></td><td>۰ تومان</td><td><form method="post"><input type="hidden" name="_csrf" value="'.e(csrf()).'"><input type="hidden" name="action" value="remove"><input type="hidden" name="id" value="'.(int)$p['id'].'"><button class="shop-btn alt">حذف</button></form></td></tr>';}
 echo '</tbody></table></div><p style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap"><strong>جمع کل: ۰ تومان</strong><a class="shop-btn" href="checkout.php">ادامه و ثبت سفارش</a></p>';
}
echo '</section>';store_footer();
