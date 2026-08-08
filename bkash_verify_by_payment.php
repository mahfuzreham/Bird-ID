<?php
require __DIR__.'/auth.php'; require __DIR__.'/config.php'; header('Content-Type: application/json; charset=utf-8');
$paymentId=trim($_POST['payment_id']??''); if(!$paymentId){http_response_code(400);echo json_encode(['error'=>'payment_id required']);exit;}
$base=rtrim($BKASH_MODE==='live'?$BKASH_LIVE_URL:$BKASH_SANDBOX_URL,'/');
function bcall($method,$url,$headers=[],$body=null){$ch=curl_init($url);$o=[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30];if($body!==null)$o[CURLOPT_POSTFIELDS]=json_encode($body);curl_setopt_array($ch,$o);$r=curl_exec($ch);$h=curl_getinfo($ch,CURLINFO_HTTP_CODE);$e=curl_error($ch);curl_close($ch);if($r===false)throw new Exception($e);$d=json_decode($r,true);if($h>=400)throw new Exception($d['statusMessage']??$d['message']??'bKash API error');return $d?:[];}
function btoken($base){global $BKASH_APP_KEY,$BKASH_APP_SECRET,$BKASH_USERNAME,$BKASH_PASSWORD;$d=bcall('POST',$base.'/checkout/token/grant',['Content-Type: application/json','Accept: application/json','username: '.$BKASH_USERNAME,'password: '.$BKASH_PASSWORD],['app_key'=>$BKASH_APP_KEY,'app_secret'=>$BKASH_APP_SECRET]);if(empty($d['id_token']))throw new Exception('bKash token unavailable');return $d['id_token'];}
try{
 $q=$pdo->prepare('SELECT * FROM payments WHERE trx_id=? AND provider="bkash"');$q->execute([$paymentId]);$p=$q->fetch();if(!$p)throw new Exception('Payment order not found');
 if($p['status']==='paid'){echo json_encode(['ok'=>true,'already_paid'=>true,'credits_added'=>(int)$p['scans']]);exit;}
 $token=btoken($base);$d=bcall('POST',$base.'/checkout/payment/execute/'.$paymentId,['Content-Type: application/json','Accept: application/json','Authorization: '.$token,'X-APP-Key: '.$BKASH_APP_KEY]);
 if(($d['transactionStatus']??'')!=='Completed'){$token=btoken($base);$d=bcall('GET',$base.'/checkout/payment/status/'.$paymentId,['Content-Type: application/json','Accept: application/json','Authorization: '.$token,'X-APP-Key: '.$BKASH_APP_KEY]);}
 if(($d['transactionStatus']??'')!=='Completed')throw new Exception('Payment is not completed');
 if(($d['merchantInvoiceNumber']??'')!==$p['invoice_id'])throw new Exception('Invoice mismatch');
 if(abs((float)($d['amount']??0)-(float)$p['amount'])>0.001)throw new Exception('Amount mismatch');
 $trx=$d['trxID']??$paymentId;if(!$trx)throw new Exception('Transaction ID missing');
 $pdo->beginTransaction();$q=$pdo->prepare('SELECT * FROM payments WHERE id=? FOR UPDATE');$q->execute([$p['id']]);$locked=$q->fetch();
 if($locked['status']!=='paid'){$q=$pdo->prepare("UPDATE payments SET status='paid',trx_id=?,paid_at=NOW() WHERE id=?");$q->execute([$trx,$locked['id']]);$q=$pdo->prepare('UPDATE users SET credits=credits+? WHERE id=?');$q->execute([(int)$locked['scans'],(int)$locked['user_id']]);}
 $pdo->commit();echo json_encode(['ok'=>true,'trx_id'=>$trx,'credits_added'=>(int)$p['scans']],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){if($pdo->inTransaction())$pdo->rollBack();http_response_code(400);echo json_encode(['error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);}
