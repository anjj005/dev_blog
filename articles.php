<?php $title='Articles'; $page='articles'; require 'inc/header.php'; require 'inc/cards.php';
$q=trim($_GET['q']??''); $cat=$_GET['cat']??''; $w=''; $p=[];
if($q!==''){ $w.=' AND (a.title LIKE ? OR a.excerpt LIKE ?)'; $p[]="%$q%"; $p[]="%$q%"; }
if(in_array($cat,CATEGORIES,true)){ $w.=' AND a.category=?'; $p[]=$cat; }
$rows=fetch_posts($db,'article',50,$w,$p); ?>
<h2 class="h">Articles<?= $cat? ' · '.e($cat):'' ?></h2>
<div class="cards"><?php foreach($rows as $a) card($a); if(!$rows) echo '<p class="sub">No articles found.</p>'; ?></div>
<?php require 'inc/footer.php'; ?>
