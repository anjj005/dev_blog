<?php require_once 'config.php'; require 'inc/github.php';
$me = user() ? $db->query("SELECT github FROM users WHERE id=".(int)user()['id'])->fetch()['github'] : '';
$gh = gh_clean($_GET['user'] ?? '') ?: gh_clean($me); $lang = $_GET['lang'] ?? ''; $repos = []; $err = null;

if ($_SERVER['REQUEST_METHOD']==='POST' && user()) { check_csrf();
  $u = gh_clean($_POST['github'] ?? '');
  if (!gh_valid($u)) flash('Invalid GitHub username (check for special characters).');
  else { [$r,$e] = gh_repos($db,$u);
    if ($e && !$r) flash($e); else { $db->prepare("UPDATE users SET github=? WHERE id=?")->execute([$u,user()['id']]); flash('GitHub account linked.'); }
  } redirect('projects.php'); }

if ($gh !== '') { if (!gh_valid($gh)) $err = 'Invalid GitHub username.'; else [$repos,$err] = gh_repos($db,$gh); }
$langs = array_values(array_unique(array_filter(array_column($repos,'language')))); sort($langs);
if ($lang !== '') $repos = array_filter($repos, fn($r)=>$r['language']===$lang);
$title='Projects'; $page='projects'; require 'inc/header.php'; // header output only after redirects are done
?>
<h2 class="h">Projects <span class="sub">Powered by the GitHub REST API</span></h2>
<form class="toolbar" method="get"><input name="user" placeholder="GitHub username" value="<?= e($gh) ?>">
<select name="lang" onchange="this.form.submit()"><option value="">All languages</option><?php foreach($langs as $l) echo '<option'.($l===$lang?' selected':'').'>'.e($l).'</option>'; ?></select>
<button class="btn">Show repositories</button></form>
<?php if (user()): ?><form class="toolbar" method="post"><?= csrf_field() ?><input name="github" placeholder="Link your GitHub username" value="<?= e($me) ?>"><button class="btn ghost">Save to my profile</button></form><?php endif; ?>
<?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?>
<?php if ($gh !== '' && !$err || $repos): ?><p class="sub">Public repositories by <a href="https://github.com/<?= e(rawurlencode($gh)) ?>" target="_blank" rel="noopener noreferrer">@<?= e($gh) ?></a> (<?= count($repos) ?>)</p><?php endif; ?>
<div class="cards">
<?php foreach ($repos as $r): ?>
<div class="card repo"><h3><?= e($r['name']) ?></h3><p><?= e($r['description'] ?: 'No description provided.') ?></p>
<div class="stats"><span><?= $r['language'] ? '<i class="lang"></i>'.e($r['language']) : 'No language detected' ?></span><span>★ <?= $r['stars'] ?></span><span>⑂ <?= $r['forks'] ?></span></div>
<a class="src" href="<?= e($r['url']) ?>" target="_blank" rel="noopener noreferrer">View source on GitHub →</a></div>
<?php endforeach; if ($gh==='') echo '<p class="sub">Enter a GitHub username to see their public projects.</p>'; elseif(!$repos && !$err) echo '<p class="sub">No public repositories to show.</p>'; ?>
</div>
<?php require 'inc/footer.php'; ?>
