<?php $title='Home'; $page='home'; require 'inc/header.php'; require 'inc/cards.php';
$feat=fetch_posts($db,'article',3); $comm=fetch_posts($db,'community',3);
$feed=[]; $followCount=0;
if(user()){ $followCount=(int)$db->query("SELECT COUNT(*) FROM follows WHERE follower_id=".(int)user()['id'])->fetchColumn();
  $st=$db->prepare("SELECT a.*,u.name author FROM articles a JOIN users u ON u.id=a.user_id WHERE a.user_id IN (SELECT followee_id FROM follows WHERE follower_id=?) ORDER BY a.created DESC,a.id DESC LIMIT 6"); $st->execute([user()['id']]); $feed=$st->fetchAll(); } ?>
<div class="grid2"><section>
<?php if(user()): ?><h2 class="h">Following <a href="members.php">Find members →</a></h2>
<div class="cards"><?php foreach($feed as $a) card($a); if(!$feed) echo '<p class="sub">'.($followCount?'The people you follow haven\'t posted yet.':'You aren\'t following anyone yet. Visit Members to find people to follow.').'</p>'; ?></div><?php endif; ?>
<h2 class="h">Featured Articles <a href="articles.php">View all →</a></h2><p class="sub">Handpicked tutorials, tips, and insights from our community.</p>
<div class="cards"><?php foreach($feat as $a) card($a); ?></div>
<h2 class="h">Latest from the Community <a href="community.php">View community →</a></h2>
<div class="cards"><?php foreach($comm as $a) card($a); ?></div></section>
<aside>
<div class="panel"><h3>Stay Updated!</h3><p class="sub">Get the latest articles delivered to your inbox.</p>
<form method="post" action="subscribe.php" class="inline"><?= csrf_field() ?><input type="email" name="email" placeholder="Enter your email" required><button class="btn">Subscribe</button></form></div>
<div class="panel"><h3>Popular Categories</h3><div class="cats"><?php foreach(CATEGORIES as $c): ?><a href="articles.php?cat=<?= urlencode($c) ?>"><?= e($c) ?></a><?php endforeach; ?></div></div>
<div class="panel"><h3>Share Your Knowledge</h3><p class="sub">Have a tutorial, project, or tech insight?</p><a class="btn" href="create.php">Create an Article →</a></div>
</aside></div>
<?php require 'inc/footer.php'; ?>
