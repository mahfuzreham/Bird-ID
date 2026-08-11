<?php
require __DIR__.'/auth.php';
require __DIR__.'/config.php';
header('Content-Type: application/json; charset=utf-8');
$u=require_login();
if($_SERVER['REQUEST_METHOD']!=='POST'){http_response_code(405);echo json_encode(['error'=>'POST an audio file as audio'],JSON_UNESCAPED_UNICODE);exit;}
if(!isset($_FILES['audio'])||$_FILES['audio']['error']!==UPLOAD_ERR_OK){http_response_code(400);echo json_encode(['error'=>'Upload a bird audio recording'],JSON_UNESCAPED_UNICODE);exit;}
$f=$_FILES['audio'];
if($f['size']>15*1024*1024){http_response_code(400);echo json_encode(['error'=>'Max 15MB'],JSON_UNESCAPED_UNICODE);exit;}
$mime=mime_content_type($f['tmp_name']);
$allowed=['audio/mpeg','audio/wav','audio/x-wav','audio/mp4','audio/aac','audio/ogg','audio/webm'];
if(!in_array($mime,$allowed,true)){http_response_code(400);echo json_encode(['error'=>'Supported audio: MP3, WAV, M4A/MP4, AAC, OGG or WEBM'],JSON_UNESCAPED_UNICODE);exit;}
$endpoint=$BIRD_SOUND_API_URL??'';$key=$BIRD_SOUND_API_KEY??'';
if($endpoint===''||$key===''){http_response_code(503);echo json_encode(['error'=>'Bird Sound ID is ready but not activated: a verified bird-audio classification API endpoint and server-side API key are still required. No credential was added to GitHub.'],JSON_UNESCAPED_UNICODE);exit;}
$ch=curl_init($endpoint);$cfile=new CURLFile($f['tmp_name'],$mime,$f['name']);curl_setopt_array($ch,[CURLOPT_POST=>true,CURLOPT_HTTPHEADER=>['Authorization: Bearer '.$key],CURLOPT_POSTFIELDS=>['audio'=>$cfile],CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>90]);$res=curl_exec($ch);$http=curl_getinfo($ch,CURLINFO_HTTP_CODE);curl_close($ch);if($http>=400||$res===false){http_response_code(502);echo json_encode(['error'=>'Bird sound provider request failed'],JSON_UNESCAPED_UNICODE);exit;}$obj=json_decode($res,true);$result=$obj['result']??$obj['prediction']??$obj['label']??null;if(!$result){http_response_code(502);echo json_encode(['error'=>'Bird sound provider returned no identification result'],JSON_UNESCAPED_UNICODE);exit;}echo json_encode(['result'=>$result,'credits'=>$u['credits']],JSON_UNESCAPED_UNICODE);
