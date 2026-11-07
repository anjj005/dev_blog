<?php require_once 'config.php'; check_csrf(); $em=strtolower(trim($_POST['email']??''));
if(filter_var($em,FILTER_VALIDATE_EMAIL)){ $db->prepare("INSERT OR IGNORE INTO subscribers(email) VALUES(?)")->execute([$em]); flash('Subscribed! (Email delivery is not set up yet; addresses are only stored.)'); } else flash('Invalid email.');
redirect('index.php');
