<?php
require __DIR__.'/auth.php';
require __DIR__.'/config.php';
header('Content-Type: application/json; charset=utf-8');
$u=require_login();

if (!in_array($BKASH_MODE, ['sandbox','live'], true)) {
    http_response_code(500); echo json_encode(['error'=>'BKASH_MODE must be sandbox or live'], JSON_UNESCAPED_UNICODE); exit;
}
$base = $BKASH_MODE === 'live' ? $BKASH_LIVE_URL : $BKASH_SANDBOX_URL;
$base = rtrim($base, '/');

$scans=max(1,min(100,(int)($_POST['scans']??1)));
$amount=round($scans*$PRICE_PER_SCAN,2);
$invoice='BIRD-BK-'.date('YmdHis').'-'.bin2hex(random_bytes(4));

function bkash_call($method,$url,$headers=[],$body=null){
    $ch=curl_init($url);
    $opts=[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>30];
    if($body!==null){$opts[CURLOPT_POSTFIELDS]=json_encode($body);}
    curl_setopt_array($ch,$opts);
    $res=curl_exec($ch); $http=curl_getinfo($ch,CURLINFO_HTTP_CODE); $err=curl_error($ch); curl_close($ch);
    if($res===false) throw new Exception('bKash connection failed: '.$err);
    $data=json_decode($res,true);
    if($http>=400) throw new Exception($data['statusMessage'] ?? $data['message'] ?? 'bKash API error');
    return $data ?: [];
}

function bkash_token($base){
    global $BKASH_APP_KEY,$BKASH_APP_SECRET,$BKASH_USERNAME,$BKASH_PASSWORD;
    $data=bkash_call('POST',$base.'/checkout/token/grant',[
        'Content-Type: application/json','Accept: application/json','username: '.$BKASH_USERNAME,'password: '.$BKASH_PASSWORD
    ],['app_key'=>$BKASH_APP_KEY,'app_secret'=>$BKASH_APP_SECRET]);
    if(empty($data['id_token'])) throw new Exception('bKash token was not returned');
    return $data['id_token'];
}

try{
    $s=$pdo->prepare("INSERT INTO payments(user_id,invoice_id,amount,scans,status,provider) VALUES(?,?,?,?, 'pending','bkash')");
    $s->execute([$u['id'],$invoice,$amount]);

    $token=bkash_token($base);
    $data=bkash_call('POST',$base.'/checkout/payment/create',[
        'Content-Type: application/json','Accept: application/json','Authorization: '.$token,'X-APP-Key: '.$BKASH_APP_KEY
    ],[
        'mode'=>'0011','payerReference'=>(string)$u['id'],'callbackURL'=>$APP_URL.'/bkash_callback.php',
        'amount'=>number_format($amount,2,'.',''),'currency'=>'BDT','intent'=>'sale','merchantInvoiceNumber'=>$invoice
    ]);

    $paymentId=$data['paymentID']??'';
    if(!$paymentId || empty($data['bkashURL'])) throw new Exception('bKash checkout URL was not returned');
    $s=$pdo->prepare('UPDATE payments SET trx_id=? WHERE invoice_id=?'); $s->execute([$paymentId,$invoice]);
    echo json_encode(['ok'=>true,'invoice_id'=>$invoice,'payment_id'=>$paymentId,'checkout_url'=>$data['bkashURL'],'mode'=>$BKASH_MODE],JSON_UNESCAPED_UNICODE);
}catch(Throwable $e){
    http_response_code(502); echo json_encode(['error'=>$e->getMessage()],JSON_UNESCAPED_UNICODE);
}
