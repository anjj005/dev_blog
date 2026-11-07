<?php require_once 'config.php'; $err='';
if($_SERVER['REQUEST_METHOD']==='POST'){ check_csrf();
  $st=$db->prepare("SELECT * FROM users WHERE email=?"); $st->execute([strtolower(trim($_POST['email']??''))]); $u=$st->fetch();
  if($u && password_verify($_POST['password']??'',$u['pass'])){ session_regenerate_id(true); $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name']]; redirect('index.php'); }
  $err='Invalid email or password.'; }
$title='Log In'; $bodyClass='authpage'; require 'inc/header.php'; ?>
<form method="post" class="formcard"><div class="logo big"><span>&lt;/&gt;</span> DevBlog</div><h2>Welcome Back</h2><p class="sub">Log in to your account to continue.</p>
<?php if($err) echo '<p class="err">'.e($err).'</p>'; ?><?= csrf_field() ?>
<input type="email" name="email" placeholder="Email Address" required><input type="password" name="password" placeholder="Password" required>
<div class="row"><label class="chk"><input type="checkbox" name="remember"> Remember me</label><a href="#" onclick="alert('Password reset needs an email service; not configured yet.');return false">Forgot password?</a></div>
<button class="btn full">Log In</button><p class="or">or</p>
<button type="button" class="btn ghost full" disabled title="Needs Google OAuth credentials">Continue with Google (not configured)</button>
<p class="sub c">Don't have an account? <a href="register.php">Sign up</a></p></form>
<?php require 'inc/footer.php'; ?>
