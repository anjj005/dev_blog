<?php function card($a){ ?>
<a class="card" href="article.php?id=<?= (int)$a['id'] ?>"><span class="tag"><?= e($a['category']) ?></span>
<h3><?= e($a['title']) ?></h3><p><?= e($a['excerpt']) ?></p>
<div class="meta">by <?= e($a['author']) ?> · <?= e(date('M j, Y', strtotime($a['created']))) ?> · <?= (int)$a['views'] ?> views</div></a>
<?php }
function fetch_posts($db,$type,$limit=3,$where='',$params=[]){
  $st=$db->prepare("SELECT a.*,u.name author FROM articles a JOIN users u ON u.id=a.user_id WHERE a.type=? $where ORDER BY a.created DESC,a.id DESC LIMIT $limit");
  $st->execute(array_merge([$type],$params)); return $st->fetchAll(); }
