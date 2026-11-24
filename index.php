<?php
require __DIR__.'/app.php';
$page=$_GET['page']??(user()?'dashboard':'home');
if($page==='logout'){session_destroy();header('Location: ?');exit;}
if($page==='change_password' && $_SERVER['REQUEST_METHOD']==='POST'){
 require_login(); verify_csrf();
 $old=$_POST['old_password']??''; $new=$_POST['new_password']??'';
 $q=db()->prepare("SELECT password FROM users WHERE id=?");$q->execute([user()['id']]);$row=$q->fetch();
 if(!$row||!password_verify($old,$row['password'])||strlen($new)<10){flash('رمز فعلی نادرست است یا رمز جدید کمتر از ۱۰ کاراکتر است.');go('change_password');}
 db()->prepare("UPDATE users SET password=? WHERE id=?")->execute([password_hash($new,PASSWORD_DEFAULT),user()['id']]);
 flash('رمز عبور با موفقیت تغییر کرد.');go('dashboard');
}
if($page==='change_password'){
 require_login();$c='<div class="auth card"><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><label>رمز فعلی</label><input type="password" name="old_password" required><label>رمز جدید (حداقل ۱۰ کاراکتر)</label><input type="password" name="new_password" minlength="10" required><button class="btn primary full">تغییر رمز</button></form></div>';layout('تغییر رمز عبور',$c);exit;
}
if($page==='register' && $_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf(); $name=trim($_POST['name']??''); $phone=trim($_POST['phone']??''); $pass=$_POST['password']??'';
 if(!$name||!preg_match('/^[0-9+\-\s]{8,18}$/',$phone)||strlen($pass)<8){flash('اطلاعات نامعتبر است؛ رمز باید حداقل ۸ کاراکتر باشد.');go('register');}
 try{$s=db()->prepare("INSERT INTO users(name,phone,password,role) VALUES(?,?,?,'student')");$s->execute([$name,$phone,password_hash($pass,PASSWORD_DEFAULT)]);$_SESSION['user']=['id'=>(int)db()->lastInsertId(),'name'=>$name,'role'=>'student'];go('dashboard');}catch(Throwable $e){flash('این شماره قبلاً ثبت شده یا خطایی رخ داده است.');go('register');}
}
if($page==='login' && $_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$s=db()->prepare("SELECT * FROM users WHERE phone=?");$s->execute([trim($_POST['phone']??'')]);$u=$s->fetch();
 if($u&&password_verify($_POST['password']??'',$u['password'])){session_regenerate_id(true);$_SESSION['user']=['id'=>(int)$u['id'],'name'=>$u['name'],'role'=>$u['role']];go('dashboard');}
 flash('شماره همراه یا رمز عبور اشتباه است.');go('login');
}
if($page==='home'){
 $c='<section class="hero"><div><span class="pill">نسل جدید برنامه‌ریزی درسی</span><h2>درس خوندن،<br><em>هوشمندتر از همیشه.</em></h2><p>برنامه‌ریزی، مدیریت تکالیف، امتحانات و مسیر پیشرفتت؛ همه در یک فضای شخصی.</p><a class="btn primary" href="?page=register">شروع رایگان</a> <a class="btn" href="?page=login">ورود به حساب</a></div><div class="hero-art"><div class="orbit">✦</div><div class="glass"><div class="eyebrow">NEXA STUDY HUB</div><h3>امروز، فرصت پیشرفته</h3><div class="bars"><i></i><i></i><i></i><i></i><i></i><i></i><i></i></div><div class="statline"><b>+ XP</b><b>Focus mode</b><b>Smart plan</b></div></div></div></section><section class="grid"><article class="card"><div class="ico">◈</div><h3>برنامه‌ریز منعطف</h3><p>برنامه‌های درسی متعدد بساز و کارها را جابه‌جا کن.</p></article><article class="card"><div class="ico purple">⌁</div><h3>مرکز امتحانات</h3><p>امتحان‌ها و مباحث مهم را یک‌جا مدیریت کن.</p></article><article class="card"><div class="ico">✧</div><h3>رشد قابل مشاهده</h3><p>فعالیت‌هایت را ثبت کن و روند پیشرفتت را ببین.</p></article></section>';
 layout('به NEXA خوش آمدی',$c);exit;
}
if($page==='register'||$page==='login'){
 $is=$page==='register';$c='<div class="auth card"><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'">'.($is?'<label>نام نمایشی</label><input name="name" required maxlength="80">':'').'<label>شماره همراه</label><input name="phone" inputmode="tel" required><label>رمز عبور</label><input name="password" type="password" minlength="8" required><button class="btn primary full">'.($is?'ساخت حساب':'ورود').'</button></form><p class="muted">'.($is?'حساب داری؟ <a href="?page=login">وارد شو</a>':'حساب نداری؟ <a href="?page=register">ثبت‌نام کن</a>').'</p><small>تأیید پیامکی در این نسخه پس از ثبت کلید معتبر کاوه‌نگار و تکمیل الگوی پیامک فعال می‌شود.</small></div>';layout($is?'ثبت‌نام':'ورود',$c);exit;
}
require_login();
$uid=user()['id'];
if(in_array($page,['admin','admin_users','admin_settings','admin_pages'])) require_admin();
if($page==='admin_settings'&&$_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 foreach(['site_name','accent_color','purple_color','seo_description','calendar_type','kavenegar_api_key','kavenegar_sender','ai_provider','ai_api_key','ai_endpoint','payment_provider','payment_merchant_id','payment_enabled'] as $k){if(isset($_POST[$k])){ $v=trim($_POST[$k]); if($v!=='' || !in_array($k,['kavenegar_api_key','ai_api_key','payment_merchant_id'],true)) save_setting($k,$v); }}
 flash('تنظیمات ذخیره شد.');go('admin_settings');
}
if($page==='admin_pages'&&$_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$action=$_POST['action']??'';
 if($action==='delete'){db()->prepare("DELETE FROM pages WHERE id=?")->execute([(int)$_POST['id']]);}
 else { $id=(int)($_POST['id']??0);$slug=trim($_POST['slug']??'');$title=trim($_POST['title']??'');$body=trim($_POST['body']??''); if($id)db()->prepare("UPDATE pages SET slug=?,title=?,body=? WHERE id=?")->execute([$slug,$title,$body,$id]);else db()->prepare("INSERT INTO pages(slug,title,body) VALUES(?,?,?)")->execute([$slug,$title,$body]);}
 flash('صفحه ذخیره شد.');go('admin_pages');
}
if($page==='admin_users'&&$_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=(int)$_POST['id'];$role=$_POST['role']==='admin'?'admin':'student';if($id!==(int)user()['id'])db()->prepare("UPDATE users SET role=? WHERE id=?")->execute([$role,$id]);flash('نقش کاربر به‌روزرسانی شد.');go('admin_users');
}
if(in_array($page,['tasks','notes','exams','planner'])&&$_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$title=trim($_POST['title']??'');$detail=trim($_POST['detail']??'');$date=$_POST['due_date']?:null;
 if($title){$table=['tasks'=>'tasks','notes'=>'notes','exams'=>'exams','planner'=>'plans'][$page];db()->prepare("INSERT INTO $table(user_id,title,detail,due_date) VALUES(?,?,?,?)")->execute([$uid,$title,$detail,$date]);flash('با موفقیت ثبت شد.');}go($page);
}
if($page==='done'){require_login();$id=(int)($_GET['id']??0);db()->prepare("UPDATE tasks SET done=1 WHERE id=? AND user_id=?")->execute([$id,$uid]);go('tasks');}
if($page==='admin'){
 $c='<div class="grid"><a class="card" href="?page=admin_users"><div class="ico">♙</div><h3>کاربران و مدیران</h3><p>مدیریت نقش و دسترسی‌ها</p></a><a class="card" href="?page=admin_settings"><div class="ico purple">⚙</div><h3>تنظیمات و API</h3><p>ظاهر، کاوه‌نگار، هوش مصنوعی و پرداخت</p></a><a class="card" href="?page=admin_pages"><div class="ico">▤</div><h3>مدیریت صفحات</h3><p>ایجاد و ویرایش محتوای صفحات</p></a></div>';layout('مرکز فرماندهی مدیر',$c);exit;
}
if($page==='admin_settings'){
 $fields=[['site_name','نام سایت','text'],['accent_color','رنگ نئونی اصلی (HEX)','text'],['purple_color','رنگ بنفش (HEX)','text'],['seo_description','توضیحات سئو','text'],['calendar_type','تقویم پیش‌فرض: jalali یا gregorian','text'],['kavenegar_api_key','کاوه‌نگار - API Key','password'],['kavenegar_sender','کاوه‌نگار - شماره فرستنده','text'],['ai_provider','نام سرویس هوش مصنوعی','text'],['ai_api_key','هوش مصنوعی - API Key','password'],['ai_endpoint','آدرس API هوش مصنوعی','url'],['payment_provider','نام درگاه پرداخت','text'],['payment_merchant_id','شناسه پذیرنده درگاه','password'],['payment_enabled','فعال‌سازی پرداخت (0 یا 1)','text']];
 $c='<div class="card"><div class="notice">کلیدهای API فقط در سمت سرور ذخیره می‌شوند. مقدار خالی را برای تغییر ندادن کلید فعلی ارسال نکنید.</div><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><div class="formgrid">';
 foreach($fields as [$k,$label,$type]){$val=setting($k);$shown=str_contains($k,'api_key')||str_contains($k,'merchant')?'':$val;$c.='<div><label>'.e($label).'</label><input type="'.e($type).'" name="'.e($k).'" value="'.e($shown).'" placeholder="'.($shown===''?'برای حفظ مقدار قبلی خالی بگذارید':'').'" '.($type==='url'?'':'').'></div>';}
 $c.='</div><button class="btn primary">ذخیره تنظیمات</button></form></div>';layout('تنظیمات و اتصال API',$c);exit;
}
if($page==='admin_users'){
 $rows=db()->query("SELECT id,name,phone,role,created_at FROM users ORDER BY id DESC")->fetchAll();$c='<div class="card"><h3>کاربران</h3><div class="tablewrap"><table><tr><th>نام</th><th>موبایل</th><th>نقش</th><th>تغییر نقش</th></tr>';
 foreach($rows as $r){$c.='<tr><td>'.e($r['name']).'</td><td>'.e($r['phone']).'</td><td>'.e($r['role']).'</td><td><form method="post" class="inline"><input type="hidden" name="csrf" value="'.e(csrf()).'"><input type="hidden" name="id" value="'.$r['id'].'"><select name="role"><option value="student">دانش‌آموز</option><option value="admin" '.($r['role']==='admin'?'selected':'').'>مدیر</option></select><button class="btn small">ثبت</button></form></td></tr>';}
 $c.='</table></div></div>';layout('مدیریت کاربران',$c);exit;
}
if($page==='admin_pages'){
 $rows=db()->query("SELECT * FROM pages ORDER BY id DESC")->fetchAll();$c='<div class="card"><h3>ایجاد صفحه</h3><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><input type="hidden" name="action" value="save"><div class="formgrid"><div><label>عنوان</label><input name="title" required></div><div><label>شناسه انگلیسی (slug)</label><input name="slug" required></div></div><label>محتوا (متن ساده)</label><textarea name="body" rows="5"></textarea><button class="btn primary">ایجاد صفحه</button></form></div><div class="card"><h3>صفحات ساخته‌شده</h3>';
 foreach($rows as $r){$c.='<details><summary>'.e($r['title']).' <span class="muted">/'.e($r['slug']).'</span></summary><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><input type="hidden" name="id" value="'.$r['id'].'"><input name="title" value="'.e($r['title']).'"><input name="slug" value="'.e($r['slug']).'"><textarea name="body">'.e($r['body']).'</textarea><button class="btn">ذخیره</button></form><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><input type="hidden" name="action" value="delete"><input type="hidden" name="id" value="'.$r['id'].'"><button class="btn danger">حذف</button></form></details>';}
 $c.='</div>';layout('مدیریت صفحات',$c);exit;
}
if($page==='dashboard'){
 $q=db()->prepare("SELECT COUNT(*) n FROM tasks WHERE user_id=? AND done=0");$q->execute([$uid]);$pending=$q->fetch()['n'];$q=db()->prepare("SELECT COUNT(*) n FROM tasks WHERE user_id=? AND done=1");$q->execute([$uid]);$done=$q->fetch()['n'];
 $c='<section class="hero compact"><div><span class="pill">داشبورد شخصی</span><h2>سلام '.e(user()['name']).'،<br><em>وقت درخشیدنه!</em></h2><p>قدم‌های کوچک امروز، پیشرفت بزرگ فردا را می‌سازند.</p></div><div class="score"><strong>'.(int)$done.'</strong><span>کار انجام‌شده</span><small>'.(int)$pending.' کار باقی‌مانده</small></div></section><section class="grid"><a class="card" href="?page=planner"><div class="ico">◷</div><h3>برنامه‌ریزی درسی</h3><p>ساخت و مدیریت برنامه‌ها</p></a><a class="card" href="?page=tasks"><div class="ico purple">✓</div><h3>تکالیف و کارها</h3><p>'.$pending.' کار در انتظار انجام</p></a><a class="card" href="?page=exams"><div class="ico">⌖</div><h3>مرکز امتحانات</h3><p>ثبت امتحان و مباحث</p></a><a class="card" href="?page=notes"><div class="ico purple">▤</div><h3>دفترچه یادداشت</h3><p>یادداشت‌های درسی</p></a><a class="card" href="?page=focus"><div class="ico">◉</div><h3>تایمر تمرکز</h3><p>مدیریت زمان مطالعه</p></a><a class="card" href="?page=groups"><div class="ico purple">♧</div><h3>گروه‌های مطالعه</h3><p>فضای گروهی به‌زودی</p></a></section>';layout('داشبورد دانش‌آموز',$c);exit;
}
if(in_array($page,['tasks','notes','exams','planner'])){
 $titles=['tasks'=>'مدیریت تکالیف و کارها','notes'=>'دفترچه یادداشت','exams'=>'مرکز امتحانات','planner'=>'برنامه‌ریز درسی'];$table=['tasks'=>'tasks','notes'=>'notes','exams'=>'exams','planner'=>'plans'][$page];$q=db()->prepare("SELECT * FROM $table WHERE user_id=? ORDER BY id DESC");$q->execute([$uid]);$rows=$q->fetchAll();
 $c='<div class="card"><h3>افزودن مورد جدید</h3><form method="post"><input type="hidden" name="csrf" value="'.e(csrf()).'"><label>عنوان</label><input name="title" required maxlength="160"><label>توضیحات / درس / مبحث</label><textarea name="detail" rows="3"></textarea><label>تاریخ (اختیاری)</label><input type="date" name="due_date"><button class="btn primary">ثبت</button></form></div><div class="card"><h3>موارد ثبت‌شده</h3>';
 foreach($rows as $r){$c.='<div class="item"><div><b>'.e($r['title']).'</b><p>'.e($r['detail']).'</p><small class="muted">'.e($r['due_date']??'بدون تاریخ').'</small></div>'.($page==='tasks'&&!$r['done']?'<a class="btn small" href="?page=done&id='.$r['id'].'">انجام شد ✓</a>':'').'</div>';}
 $c.='</div>';layout($titles[$page],$c);exit;
}
if($page==='focus'){
 $c='<div class="card timer"><div class="eyebrow">FOCUS SESSION</div><div id="clock">25:00</div><p class="muted">۲۵ دقیقه تمرکز · ۵ دقیقه استراحت</p><div class="actions"><button class="btn primary" id="start">شروع</button><button class="btn" id="pause">توقف</button><button class="btn" id="reset">شروع دوباره</button></div></div>';layout('تایمر مطالعه',$c);exit;
}
if($page==='groups'){layout('گروه‌های مطالعه','<div class="card"><h3>فضای گروهی NEXA</h3><p>زیرساخت گروه‌های عمومی و خصوصی در نقشه راه نسخه بعدی قرار دارد. برای انتشار عمومی، چت زنده، گزارش تخلف و ابزار نظارت باید تکمیل و آزمون امنیتی شوند.</p></div>');exit;}
$slug=$page;$s=db()->prepare("SELECT * FROM pages WHERE slug=?");$s->execute([$slug]);$r=$s->fetch();if($r){layout($r['title'],'<div class="card">'.nl2br(e($r['body'])).'</div>');exit;}
http_response_code(404);layout('صفحه پیدا نشد','<div class="card">این صفحه وجود ندارد.</div>');
