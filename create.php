<?php require_once 'config.php'; if(!user()){ flash('Please log in to write an article.'); redirect('login.php'); }
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $t=trim($_POST['title']??''); $x=trim($_POST['excerpt']??''); $b=trim($_POST['body']??''); $c=$_POST['category']??''; $ty=($_POST['type']??'')==='community'?'community':'article';
  if($t===''||$b===''||!in_array($c,CATEGORIES,true)) $err='Title, category and body are required.';
  else{ $db->prepare("INSERT INTO articles(user_id,title,excerpt,body,category,type) VALUES(?,?,?,?,?,?)")->execute([user()['id'],$t,$x?:mb_substr($b,0,120),$b,$c,$ty]); flash('Published!'); redirect('index.php'); } }
$title='Create'; require 'inc/header.php'; ?>
<form method="post" class="formcard wide"><h2>Create an Article</h2><?php if($err) echo '<p class="err">'.e($err).'</p>'; ?><?= csrf_field() ?>
<label>Title<input name="title" required maxlength="150"></label>
<label>Short summary<input name="excerpt" maxlength="200"></label>
<label>Category<select name="category"><?php foreach(CATEGORIES as $c) echo '<option>'.e($c).'</option>'; ?></select></label>
<label>Post to<select name="type"><option value="article">Articles</option><option value="community">Community</option></select></label>
<label>Body<textarea name="body" rows="10" required></textarea></label><button class="btn">Publish</button></form>
<?php require 'inc/footer.php'; ?>
