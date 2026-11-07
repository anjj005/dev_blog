<?php require_once 'config.php'; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $n=trim($_POST['name']??''); $em=strtolower(trim($_POST['email']??'')); $pw=$_POST['password']??'';
  if($n===''||!filter_var($em,FILTER_VALIDATE_EMAIL)||strlen($pw)<8) $err='Enter a name, a valid email, and a password of 8+ characters.';
  else try{ $db->prepare("INSERT INTO users(name,email,pass) VALUES(?,?,?)")->execute([$n,$em,password_hash($pw,PASSWORD_DEFAULT)]);
    $_SESSION['user']=['id'=>$db->lastInsertId(),'name'=>$n]; session_regenerate_id(true); flash('Welcome to DevBlog!'); redirect('index.php');
  }catch(PDOException $x){ $err='That email is already registered.'; } }
$title='Sign Up'; $bodyClass='authpage'; require 'inc/header.php'; ?>
<form method="post" class="formcard"><div class="logo big"><span>&lt;/&gt;</span> DevBlog</div><h2>Create Account</h2>
<?php if($err) echo '<p class="err">'.e($err).'</p>'; ?><?= csrf_field() ?>
<input name="name" placeholder="Full name" required><input type="email" name="email" placeholder="Email Address" required><input type="password" name="password" placeholder="Password (8+ chars)" required>
<button class="btn full">Sign Up</button><p class="sub c">Already have an account? <a href="login.php">Log in</a></p></form>
<?php require 'inc/footer.php'; ?>
