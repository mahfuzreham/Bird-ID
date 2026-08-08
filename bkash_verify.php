<?php
require __DIR__.'/auth.php';
require __DIR__.'/config.php';
header('Content-Type: application/json; charset=utf-8');
$u=require_login();

$invoice=trim($_POST['invoice_id']??'');
$paymentId=trim($_POST['payment_id']??'');
if(!$invoice||!$paymentId){http_response_code(400);echo json_encode(['error'=>'invoice_id and payment_id required'],JSON_UNESCAPED_UNICODE);exit;}

$base=rtrim($BKASH_MODE==='live'?$BKASH_LIVE_URL:$BKASH_SANDBOX_URL,'/');
function bkash_call_verify($method,$url,$headers=[],$body=null){
 $ch=curl_init($url);$opts=[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30];
 if($body!==null)$opts[CURLOPT_POSTFIELDS]=json_encode($body);curl_setopt_array($ch,$opts);
 $res=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);$err=curl_error($ch);curl_close($ch);
 if($res===false)throw new Exception('bKash connection failed: '.$err);$d=json_decode($res,true);
 if($http>=400)throw new Exception($d['statusMessage']??$d['message']??'bKash API error');return $d?:[];
}
function get_token_verify($base){
 global $BKASH_APP_KEY,$BKASH_APP_SECRET,$BKASH_USERNAME,$BKASH_PASSWORD;
 $d=bkash_call_verify('POST',$base.'/checkout/token/grant',['Content-Type: application/json','Accept: application/json','username: '.$BKASH_USERNAME,'password: '.$BKASH_PASSWORD],['app_key'=>$BKASH_APP_KEY,'app_secret'=>$BKASH_APP_SECRET]);
 if(empty($d['id_token']))throw new Exception('bKash token unavailable');return $d['id_token'];
}

try{
 $q=$pdo->prepare('SELECT * FROM payments WHERE invoice_id=? AND user_id=?');$q->execute([$invoice,$u['id']]);$p=$q->fetch();
 if(!$p)throw new Exception('Order not found');
 if($p['status']==='paid'){echo json_encode(['ok'=>true,'already_paid'=>true],JSON_UNESCAPED_UNICODE);exit;}
 if($p['provider']!=='bkash')throw new Exception('Payment provider mismatch');

 $token=get_token_verify($base);
 // Execute first; if already executed, query the payment status.
 $data=bkash_call_verify('POST',$base.'/checkout/payment/execute/'.$paymentId,['Content-Type: application/json','Accept: application/json','Authorization: '.$token,'X-APP-Key: '.$BKASH_APP_KEY],null);
 if(($data['statusCode']??'')!=='0000' && ($data['transactionStatus']??'')!=='Completed'){
   $token=get_token_verify($base);
   $data=bkash_call_verify('GET',$base.'/checkout/payment/status/'.$paymentId,['Content-Type: application/json','Accept: application/json','Authorization: '.$token,'X-APP-Key: '.$BKASH_APP_KEY]);
 }

 $status=$data['transactionStatus']??'';
 $returnedInvoice=$data['merchantInvoiceNumber']??'';
 $returnedAmount=(float)($data['amount']??0);
 $trx=$data['trxID']??'';
 if($status!=='Completed')throw new Exception('Payment is not completed');
 if($returnedInvoice!==$invoice)throw new Exception('Invoice mismatch');
 if(abs($returnedAmount-(float)$p['amount'])>0.001)throw new Exception('Amount mismatch');
 if(!$trx)throw new Exception('Transaction ID missing');

 $pdo->beginTransaction();
 $q=$pdo->prepare('SELECT * FROM payments WHERE id=? FOR UPDATE');$q->execute([$p['id']]);$locked=$q->fetch();
 if($locked['status']!=='paid'){
   $q=$pdo->prepare("UPDATE payments SET status='paid',trx_id=?,paid_at=NOW() WHERE id=?");$q->execute([$trx,$locked['id']]);
   $q=$pdo->prepare('UPDATE users SET credits=credits+? WHERE id=?');$q->execute([(int)$locked['scans'],(int)$locked['user_id']]);
 }
 $pdo->commit();
 echo json_encode(['ok'=>true,'trx_id'=>$trx,'credits_added'=>(int)$p['scans']],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(400);echo json_encode(['error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
