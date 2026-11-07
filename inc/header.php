<?php require_once __DIR__.'/../config.php'; $title = $title ?? 'DevBlog'; $page = $page ?? ''; $u = user(); ?>
<!doctype html><html lang="en"><head><meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= e($title) ?> · DevBlog</title><link rel="stylesheet" href="assets/style.css"></head>
<body class="<?= e($bodyClass ?? '') ?>">
<header class="nav"><a class="logo" href="index.php"><span>&lt;/&gt;</span> DevBlog</a>
<nav>
<?php foreach (['index.php'=>['Home','home'],'articles.php'=>['Articles','articles'],'projects.php'=>['Projects','projects'],'community.php'=>['Community','community'],'members.php'=>['Members','members'],'about.php'=>['About','about']] as $f=>[$l,$k]): ?>
<a href="<?= $f ?>" class="<?= $page===$k?'active':'' ?>"><?= $l ?></a><?php endforeach; ?>
<details class="dd"><summary>Categories ▾</summary><div>
<?php foreach (CATEGORIES as $c): ?><a href="articles.php?cat=<?= urlencode($c) ?>"><?= e($c) ?></a><?php endforeach; ?></div></details>
</nav>
<form class="search" action="articles.php"><input name="q" placeholder="Search articles…" value="<?= e($_GET['q'] ?? '') ?>"></form>
<div class="auth"><?php if ($u): ?><a class="btn ghost" href="create.php">+ New</a><a class="who" href="profile.php"><?= e($u['name']) ?></a><a class="btn ghost" href="logout.php">Log out</a>
<?php else: ?><a class="btn ghost" href="login.php">Log In</a><a class="btn" href="register.php">Sign Up</a><?php endif; ?></div></header>
<main>
<?php if ($fl = flash()): ?><div class="flash"><?= e($fl) ?></div><?php endif; ?>
