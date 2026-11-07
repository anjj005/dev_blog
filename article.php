<?php $title='Article'; require 'inc/header.php';
$st=$db->prepare("SELECT a.*,u.name author FROM articles a JOIN users u ON u.id=a.user_id WHERE a.id=?"); $st->execute([(int)($_GET['id']??0)]); $a=$st->fetch();
if(!$a){ http_response_code(404); echo '<h2 class="h">Not found</h2>'; require 'inc/footer.php'; exit; }
$db->prepare("UPDATE articles SET views=views+1 WHERE id=?")->execute([$a['id']]); ?>
<article class="read"><span class="tag"><?= e($a['category']) ?></span><h1><?= e($a['title']) ?></h1>
<div class="meta">by <a class="sub" href="profile.php?id=<?= (int)$a['user_id'] ?>"><?= e($a['author']) ?></a> · <?= e(date('M j, Y', strtotime($a['created']))) ?> · <?= (int)$a['views']+1 ?> views</div>
<div class="body"><?= nl2br(e($a['body'])) ?></div></article>
<?php require 'inc/footer.php'; ?>
