<?php
require __DIR__.'/auth.php';
require __DIR__.'/config.php';
$paymentId=trim($_GET['paymentID']??$_GET['paymentId']??'');
$status=trim($_GET['status']??'');
$u=user();
?><!doctype html><html lang="bn"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>bKash Payment</title><style>body{font-family:system-ui;background:#f4f7f5;padding:30px}.card{max-width:520px;margin:auto;background:#fff;padding:25px;border-radius:18px;box-shadow:0 8px 30px #0001}</style></head><body><div class="card"><h2>🦜 Bird ID Payment</h2><p id="msg">Payment status: <?=htmlspecialchars($status ?: 'processing')?></p><a href="index.php">Back to Bird ID</a></div><script>
const paymentId=<?=json_encode($paymentId)?>;
async function verify(){
 if(!paymentId){document.getElementById('msg').textContent='No bKash payment ID was returned.';return;}
 // The invoice is recovered server-side from the stored payment record using the bKash payment ID.
 let f=new FormData();f.append('payment_id',paymentId);
 let r=await fetch('bkash_verify_by_payment.php',{method:'POST',body:f});let d=await r.json();
 document.getElementById('msg').textContent=d.ok?'Payment successful. Scan credit added: '+d.credits_added:(d.error||'Payment verification failed');
}
verify();
</script></body></html>