<?php
session_start();
$db = new PDO('sqlite:' . __DIR__ . '/data/devblog.sqlite');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
$db->exec("CREATE TABLE IF NOT EXISTS users(id INTEGER PRIMARY KEY, name TEXT, email TEXT UNIQUE, pass TEXT, created TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS articles(id INTEGER PRIMARY KEY, user_id INT, title TEXT, excerpt TEXT, body TEXT, category TEXT, type TEXT DEFAULT 'article', views INT DEFAULT 0, created TEXT DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE IF NOT EXISTS subscribers(email TEXT UNIQUE, created TEXT DEFAULT CURRENT_TIMESTAMP);");

if(!in_array('github', array_column($db->query("PRAGMA table_info(users)")->fetchAll(),'name'))) $db->exec("ALTER TABLE users ADD COLUMN github TEXT");
foreach (['bio','website'] as $col) if(!in_array($col, array_column($db->query("PRAGMA table_info(users)")->fetchAll(),'name'))) $db->exec("ALTER TABLE users ADD COLUMN $col TEXT");
$db->exec("CREATE TABLE IF NOT EXISTS gh_cache(username TEXT PRIMARY KEY, json TEXT, fetched INTEGER)");

$db->exec("CREATE TABLE IF NOT EXISTS follows(follower_id INT, followee_id INT, created TEXT DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(follower_id,followee_id))");

const CATEGORIES = ['Web Development','Programming','AI & Machine Learning','Projects','Cybersecurity','Tech News'];

function e($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function user(){ return $_SESSION['user'] ?? null; }
function redirect($u){ header("Location: $u"); exit; }
function csrf(){ return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function csrf_field(){ return '<input type="hidden" name="csrf" value="'.csrf().'">'; }
function check_csrf(){ if(!hash_equals(csrf(), $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid request.'); } }
function flash($m=null){ if($m!==null){ $_SESSION['flash']=$m; return; } $f=$_SESSION['flash']??null; unset($_SESSION['flash']); return $f; }

// Seed starter content once (clearly sample content; view counts start at 0)
if (!$db->query("SELECT COUNT(*) c FROM users")->fetch()['c']) {
  $db->prepare("INSERT INTO users(name,email,pass) VALUES('DevBlog Team','team@devblog.local',?)")->execute([password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT)]);
  $s = $db->prepare("INSERT INTO articles(user_id,title,excerpt,body,category,type) VALUES(1,?,?,?,?,?)");
  foreach ([
    ['Build a Modern React App from Scratch','Learn how to create a responsive React application step-by-step.','Sample article. Start with a build tool such as Vite, structure your components, then add routing and state.','Web Development','article'],
    ['Python for Beginners: Your First Project','Start your coding journey with a simple Python project.','Sample article. Install Python, write a small script, then grow it into a command-line tool.','Programming','article'],
    ['Build a Task Manager Web App','A practical project to organize your daily tasks.','Sample article. Plan the data model, build the CRUD pages, then add filtering.','Projects','article'],
    ['My Open Source Portfolio','Just finished my portfolio website. Happy to share the code!','Sample community post.','Projects','community'],
    ['Deploying a Flask App on Render','A step-by-step guide for beginners.','Sample community post.','Programming','community'],
    ['Tips for Better Code Structure','Simple tips to write cleaner code.','Sample community post.','Programming','community'],
  ] as $a) $s->execute($a);
}
