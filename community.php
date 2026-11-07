<?php $title='Community'; $page='community'; require 'inc/header.php'; require 'inc/cards.php'; ?>
<h2 class="h">Community</h2><p class="sub">Discover what developers are building and sharing.</p>
<div class="cards"><?php foreach(fetch_posts($db,'community',50) as $a) card($a); ?></div>
<?php require 'inc/footer.php'; ?>
