<?php require_once 'config.php'; check_csrf();
if (!user()) { flash('Please log in to follow members.'); redirect('login.php'); }
$id = (int)($_POST['id'] ?? 0); $me = (int)user()['id'];
$chk = $db->prepare("SELECT 1 FROM users WHERE id=?"); $chk->execute([$id]);
if ($id && $id !== $me && $chk->fetch()) {
  if (($_POST['action'] ?? '') === 'unfollow') $db->prepare("DELETE FROM follows WHERE follower_id=? AND followee_id=?")->execute([$me,$id]);
  else $db->prepare("INSERT OR IGNORE INTO follows(follower_id,followee_id) VALUES(?,?)")->execute([$me,$id]);
}
redirect('profile.php?id='.$id);
