<?php $title='Members'; $page='members'; require 'inc/header.php';
$q = trim($_GET['q'] ?? ''); $like = "%$q%";
$st = $db->prepare("SELECT u.id,u.name,u.bio,u.github,(SELECT COUNT(*) FROM articles a WHERE a.user_id=u.id) posts
  FROM users u WHERE ?='' OR u.name LIKE ? OR u.github LIKE ? ORDER BY u.name COLLATE NOCASE LIMIT 60");
$st->execute([$q, $like, $like]); $rows = $st->fetchAll(); ?>
<h2 class="h">Members</h2><p class="sub">Find developers by name or GitHub username.</p>
<form class="toolbar" method="get"><input name="q" placeholder="Search members…" value="<?= e($q) ?>"><button class="btn">Search</button></form>
<div class="cards">
<?php foreach ($rows as $m): ?>
<a class="card member" href="profile.php?id=<?= (int)$m['id'] ?>"><div class="avatar"><?= e(mb_strtoupper(mb_substr($m['name'],0,1))) ?></div>
<div><h3><?= e($m['name']) ?></h3><div class="meta"><?= (int)$m['posts'] ?> post<?= $m['posts']==1?'':'s' ?><?= $m['github'] ? ' · @'.e($m['github']) : '' ?></div>
<?php if ($m['bio']) echo '<div class="sub">'.e(mb_strimwidth($m['bio'],0,80,'…')).'</div>'; ?></div></a>
<?php endforeach; if (!$rows) echo '<p class="sub">No members found.</p>'; ?>
</div>
<?php require 'inc/footer.php'; ?>
