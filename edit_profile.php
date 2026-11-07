<?php require_once 'config.php'; require 'inc/github.php';
if (!user()) { flash('Please log in to edit your profile.'); redirect('login.php'); }
$st = $db->prepare("SELECT name,bio,website,github FROM users WHERE id=?"); $st->execute([user()['id']]); $p = $st->fetch(); $err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') { check_csrf();
  $n = trim($_POST['name'] ?? ''); $bio = trim($_POST['bio'] ?? ''); $web = trim($_POST['website'] ?? ''); $gh = gh_clean($_POST['github'] ?? '');
  if ($n === '' || mb_strlen($n) > 60) $err = 'Name is required (max 60 characters).';
  elseif (mb_strlen($bio) > 300) $err = 'Bio must be 300 characters or less.';
  elseif ($web !== '' && !preg_match('#^https?://#i', $web)) $err = 'Website must start with http:// or https://';
  elseif ($gh !== '' && !gh_valid($gh)) $err = 'Invalid GitHub username.';
  else {
    $db->prepare("UPDATE users SET name=?,bio=?,website=?,github=? WHERE id=?")->execute([$n,$bio,$web ?: null,$gh ?: null,user()['id']]);
    $_SESSION['user']['name'] = $n; flash('Profile updated.'); redirect('profile.php');
  }
  $p = ['name'=>$n,'bio'=>$bio,'website'=>$web,'github'=>$gh];
}
$title = 'Edit Profile'; require 'inc/header.php'; ?>
<form method="post" class="formcard wide"><h2>Edit Profile</h2><?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?><?= csrf_field() ?>
<label>Display name<input name="name" maxlength="60" required value="<?= e($p['name']) ?>"></label>
<label>Bio<textarea name="bio" rows="4" maxlength="300"><?= e($p['bio']) ?></textarea></label>
<label>GitHub username<input name="github" value="<?= e($p['github']) ?>"></label>
<label>Website<input name="website" placeholder="https://" value="<?= e($p['website']) ?>"></label>
<button class="btn">Save changes</button><a class="sub c" href="profile.php">Cancel</a></form>
<?php require 'inc/footer.php'; ?>
