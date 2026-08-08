<?php
session_start(); require __DIR__.'/db.php';
if(empty($_SESSION['guest_id'])) $_SESSION['guest_id']='g_'.bin2hex(random_bytes(16));
function user():?array{global $pdo;$email=$_SESSION['guest_id'].'@guest.local';$s=$pdo->prepare('SELECT * FROM users WHERE email=?');$s->execute([$email]);$u=$s->fetch();if(!$u){$s=$pdo->prepare('INSERT INTO users(email,password_hash,credits) VALUES(?,?,0)');$s->execute([$email,password_hash(bin2hex(random_bytes(16)),PASSWORD_DEFAULT)]);$id=$pdo->lastInsertId();$s=$pdo->prepare('SELECT * FROM users WHERE id=?');$s->execute([$id]);$u=$s->fetch();}return $u?:null;}
function require_login():array{$u=user();if(!$u){http_response_code(500);echo json_encode(['error'=>'Guest session unavailable'],JSON_UNESCAPED_UNICODE);exit;}return $u;}
