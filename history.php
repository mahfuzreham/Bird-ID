<?php
require __DIR__.'/auth.php';
$u=require_login();
$s=$pdo->prepare('SELECT id,result_text,created_at FROM scan_logs WHERE user_id=? ORDER BY id DESC LIMIT 50');
$s->execute([$u['id']]);
$rows=$s->fetchAll();
?><!doctype html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Bird ID scan history — review recent AI bird identification results."><title>Scan History — Bird ID</title><style>body{font-family:system-ui;margin:0;background:#f4f7f5;color:#16321f}.wrap{max-width:860px;margin:30px auto;padding:20px}.card{background:#fff;padding:24px;border-radius:20px;box-shadow:0 10px 35px #0001;margin-bottom:14px}.result{white-space:pre-wrap;line-height:1.6}.back{color:#176b35;font-weight:700}</style></head><body><main class="wrap"><p><a class="back" href="index.php">← Back to Bird ID</a></p><div class="card"><h1>📚 Scan History</h1><p>এই browser session-এর সর্বশেষ ৫০টি সফল scan দেখানো হচ্ছে।</p></div><?php if(!$rows): ?><div class="card">এখনও কোনো successful scan নেই।</div><?php else: foreach($rows as $row): ?><article class="card"><small><?=htmlspecialchars($row['created_at'],ENT_QUOTES,'UTF-8')?></small><div class="result"><?=htmlspecialchars($row['result_text'],ENT_QUOTES,'UTF-8')?></div></article><?php endforeach; endif; ?></main></body></html>
