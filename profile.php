<?php require_once 'config.php'; require 'inc/github.php'; require 'inc/cards.php';
$id = isset($_GET['id']) ? (int)$_GET['id'] : (user()['id'] ?? 0);
if (!$id) { flash('Please log in to view your profile.'); redirect('login.php'); }
$st = $db->prepare("SELECT id,name,bio,website,github,created FROM users WHERE id=?"); $st->execute([$id]); $p = $st->fetch();
if (!$p) { http_response_code(404); $title='Not found'; require 'inc/header.php'; echo '<h2 class="h">User not found</h2>'; require 'inc/footer.php'; exit; }
$own = user() && user()['id'] == $p['id'];
$title = $p['name']; $page = $own ? 'profile' : ''; require 'inc/header.php';
$posts = fetch_posts($db, 'article', 20, ' AND a.user_id=?', [$p['id']]);
$posts = array_merge($posts, fetch_posts($db, 'community', 20, ' AND a.user_id=?', [$p['id']]));
$nFollowers = (int)$db->query("SELECT COUNT(*) FROM follows WHERE followee_id=".(int)$p['id'])->fetchColumn();
$nFollowing = (int)$db->query("SELECT COUNT(*) FROM follows WHERE follower_id=".(int)$p['id'])->fetchColumn();
$isFollowing = false;
if (user() && !$own) { $f = $db->prepare("SELECT 1 FROM follows WHERE follower_id=? AND followee_id=?"); $f->execute([user()['id'],$p['id']]); $isFollowing = (bool)$f->fetch(); }
$repos = []; $err = null;
if ($p['github'] && gh_valid($p['github'])) { [$repos,$err] = gh_repos($db, $p['github']); }
?>
<div class="profile"><div class="avatar"><?= e(mb_strtoupper(mb_substr($p['name'],0,1))) ?></div>
<div><h1><?= e($p['name']) ?></h1><div class="meta">Joined <?= e(date('M Y', strtotime($p['created']))) ?> · <?= count($posts) ?> post<?= count($posts)==1?'':'s' ?></div>
<?php if ($p['bio']) echo '<p>'.nl2br(e($p['bio'])).'</p>'; ?>
<div class="meta">
<?php if ($p['github']): ?><a href="https://github.com/<?= e(rawurlencode($p['github'])) ?>" target="_blank" rel="noopener noreferrer">GitHub: @<?= e($p['github']) ?></a> <?php endif; ?>
<?php if ($p['website']): ?>· <a href="<?= e($p['website']) ?>" target="_blank" rel="noopener noreferrer nofollow"><?= e($p['website']) ?></a><?php endif; ?></div>
<div class="meta"><b><?= $nFollowers ?></b> follower<?= $nFollowers==1?'':'s' ?> · <b><?= $nFollowing ?></b> following</div>
<p><?php if ($own): ?><a class="btn ghost" href="edit_profile.php">Edit profile</a>
<?php elseif (user()): ?><form method="post" action="follow.php" style="display:inline"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int)$p['id'] ?>"><input type="hidden" name="action" value="<?= $isFollowing?'unfollow':'follow' ?>"><button class="btn<?= $isFollowing?' ghost':'' ?>"><?= $isFollowing?'Following ✓ (click to unfollow)':'Follow' ?></button></form>
<?php else: ?><a class="btn" href="login.php">Log in to follow</a><?php endif; ?></p></div></div>

<h2 class="h">Posts</h2>
<div class="cards"><?php foreach ($posts as $a) card($a); if (!$posts) echo '<p class="sub">No posts yet.</p>'; ?></div>

<?php if ($p['github']): ?><h2 class="h">GitHub Projects</h2>
<?php if ($err) echo '<p class="err">'.e($err).'</p>'; ?>
<div class="cards"><?php foreach (array_slice($repos,0,12) as $r): ?>
<div class="card repo"><h3><?= e($r['name']) ?></h3><p><?= e($r['description'] ?: 'No description provided.') ?></p>
<div class="stats"><span><?= $r['language'] ? '<i class="lang"></i>'.e($r['language']) : 'No language detected' ?></span><span>★ <?= $r['stars'] ?></span><span>⑂ <?= $r['forks'] ?></span></div>
<a class="src" href="<?= e($r['url']) ?>" target="_blank" rel="noopener noreferrer">View source on GitHub →</a></div>
<?php endforeach; if (!$repos && !$err) echo '<p class="sub">No public repositories to show.</p>'; ?></div><?php endif; ?>
<?php require 'inc/footer.php'; ?>
