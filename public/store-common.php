<?php
declare(strict_types=1);
require_once __DIR__.'/../app/bootstrap.php';

function store_setup(): PDO {
 $pdo=db();
 $pdo->exec("CREATE TABLE IF NOT EXISTS site_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value TEXT NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 $cols=$pdo->query("SHOW COLUMNS FROM users LIKE 'phone'")->fetch();
 if(!$cols) $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(20) NULL UNIQUE");
 $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  delivery_method VARCHAR(40) NOT NULL,
  delivery_at DATETIME NOT NULL,
  address TEXT NOT NULL,
  recipient_name VARCHAR(160) NOT NULL,
  recipient_phone VARCHAR(20) NOT NULL,
  status VARCHAR(30) NOT NULL DEFAULT 'new',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_orders_user(user_id)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  order_id BIGINT UNSIGNED NOT NULL,
  product_id BIGINT UNSIGNED NOT NULL,
  product_name VARCHAR(200) NOT NULL,
  quantity INT UNSIGNED NOT NULL,
  unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  INDEX idx_order_items_order(order_id)
 ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
 if(!isset($_SESSION['cart'])||!is_array($_SESSION['cart'])) $_SESSION['cart']=[];
 return $pdo;
}
function store_setting(PDO $pdo,string $key,string $fallback=''): string {
 $s=$pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key=?');$s->execute([$key]);$v=$s->fetchColumn();return $v===false?$fallback:(string)$v;
}
function store_customer_required(): void {
 if(empty($_SESSION['customer_id'])) { notice('برای ادامه ابتدا با شماره موبایل وارد حساب کاربری شو.'); go('account.php?next=checkout'); }
}
function store_cart_count(): int { return array_sum(array_map('intval',$_SESSION['cart']??[])); }
function store_header(PDO $pdo,string $title): void {
 $name=store_setting($pdo,'site_name','Kadad');$logo=store_setting($pdo,'site_logo','');
 echo '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>'.e($title).' · '.e($name).'</title><link rel="stylesheet" href="assets/admin.css"><style>
 body{margin:0;background:#f5f7fb;color:#172b3a;font-family:Vazirmatn,Tahoma,sans-serif}.shop-header{background:#fff;border-bottom:1px solid #e5ebf1}.shop-head,.shop-main,.shop-footer{width:min(1000px,calc(100% - 32px));margin:auto}.shop-head{min-height:72px;display:flex;align-items:center;justify-content:space-between;gap:14px;flex-wrap:wrap}.shop-brand{display:flex;align-items:center;gap:10px;text-decoration:none;color:#19394a;font-weight:800;font-size:20px}.shop-logo{width:40px;height:40px;object-fit:contain;border-radius:12px}.shop-nav{display:flex;align-items:center;gap:14px;flex-wrap:wrap}.shop-nav a{color:#315367;text-decoration:none;font-size:13px}.shop-main{padding:30px 0 60px}.shop-panel{background:#fff;border:1px solid #e4ebf1;border-radius:20px;padding:clamp(18px,4vw,30px);margin-bottom:18px;box-shadow:0 8px 24px #18364d08}.shop-panel h1,.shop-panel h2{margin-top:0}.shop-muted{color:#71818d;line-height:1.9;font-size:13px}.shop-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(min(100%,230px),1fr));gap:16px}.shop-card{background:#fff;border:1px solid #e4ebf1;border-radius:18px;padding:20px;color:inherit;text-decoration:none;display:block}.shop-card h3{margin:0 0 10px}.shop-card p{color:#687d8b;line-height:1.9;font-size:13px;white-space:pre-wrap}.shop-price{color:#078171;font-weight:800}.shop-btn{display:inline-flex;align-items:center;justify-content:center;border:0;border-radius:11px;padding:11px 16px;background:#235b71;color:white;text-decoration:none;font:inherit;cursor:pointer}.shop-btn.alt{background:#edf3f6;color:#284b5c}.shop-btn:disabled{opacity:.45;cursor:not-allowed}.shop-form{display:grid;gap:15px;max-width:620px}.shop-form label{display:grid;gap:7px;font-size:13px;font-weight:700}.shop-form input,.shop-form textarea,.shop-form select{width:100%;box-sizing:border-box;border:1px solid #d5e0e8;border-radius:10px;padding:12px;font:inherit;background:#fff;color:#203847}.shop-form textarea{min-height:90px}.shop-table{width:100%;border-collapse:collapse}.shop-table th,.shop-table td{text-align:right;padding:12px 8px;border-bottom:1px solid #edf1f5;font-size:13px}.shop-alert{padding:12px 14px;border-radius:10px;background:#e7f7f1;color:#176a58;margin-bottom:14px}.shop-alert.error{background:#fff0ef;color:#a22e2e}.shop-empty{padding:28px;text-align:center;border:1px dashed #cbd8e2;border-radius:16px;background:#fff;color:#667c8a}.shop-footer{padding:20px 0;color:#81909b;font-size:12px}@media(max-width:600px){.shop-head{padding:12px 0}.shop-nav{gap:10px}.shop-table th,.shop-table td{padding:9px 5px;font-size:12px}}
 </style></head><body><header class="shop-header"><div class="shop-head"><a class="shop-brand" href="./">';
 if($logo!=='') echo '<img class="shop-logo" src="'.e($logo).'" alt="">'; else echo '<span class="shop-logo" style="display:grid;place-items:center;background:#e8f2f4;color:#22677a">K</span>';
 echo '<span>'.e($name).'</span></a><nav class="shop-nav"><a href="./">فروشگاه</a><a href="cart.php">سبد خرید ('.store_cart_count().')</a>';
 if(!empty($_SESSION['customer_id'])) echo '<a href="orders.php">سفارش‌های من</a><a href="account.php">حساب کاربری</a>'; else echo '<a href="account.php">ورود / ثبت‌نام</a>';
 echo '</nav></div></header><main class="shop-main">';
 $message=(string)($_SESSION['_notice']??'');unset($_SESSION['_notice']);if($message) echo '<div class="shop-alert">'.e($message).'</div>';
}
function store_footer(): void { echo '</main><footer class="shop-footer">فروشگاه ساخته‌شده با Kadad CMS · همهٔ محصولات فعلاً رایگان هستند.</footer></body></html>'; }
