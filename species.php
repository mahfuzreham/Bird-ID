<?php
$path=__DIR__.'/species.json';
$species=json_decode(file_get_contents($path),true) ?: [];
$q=trim($_GET['q']??'');
$region=trim($_GET['region']??'');
if($q!==''||$region!==''){
  $species=array_values(array_filter($species,function($b)use($q,$region){
    $hay=strtolower(($b['common_name']??'').' '.($b['scientific_name']??'').' '.($b['habitat']??''));
    $okQ=$q===''||str_contains($hay,strtolower($q));
    $okR=$region===''||in_array($region,$b['regions']??[],true);
    return $okQ&&$okR;
  }));
}
$regions=[];foreach(json_decode(file_get_contents($path),true)?:[] as $b)foreach($b['regions']??[] as $r)$regions[$r]=true;$regions=array_keys($regions);sort($regions);
?><!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><meta name="description" content="Bird ID species directory with common names, scientific names, habitats and regional information."><title>Bird Species Directory — Bird ID</title><style>body{font-family:system-ui;margin:0;background:#f4f7f5;color:#16321f}.wrap{max-width:980px;margin:auto;padding:24px}.card{background:#fff;padding:20px;border-radius:18px;box-shadow:0 10px 35px #0001;margin:14px 0}.grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:14px}.muted{color:#5f7065}.search{display:flex;gap:8px;flex-wrap:wrap}.search input,.search select,.search button{padding:11px;border:1px solid #ccd8d0;border-radius:10px}.search button{font-weight:700;cursor:pointer}.back{font-weight:700;color:#176b35}</style></head><body><main class="wrap"><p><a class="back" href="index.php">← Bird ID</a></p><section class="card"><h1>🦜 Bird Species Directory</h1><p class="muted">Browse the starter species database. Expand <code>species.json</code> as the catalog grows.</p><form class="search" method="get"><input name="q" value="<?=htmlspecialchars($q,ENT_QUOTES,'UTF-8')?>" placeholder="Search species or scientific name"><select name="region"><option value="">All regions</option><?php foreach($regions as $r): ?><option value="<?=htmlspecialchars($r,ENT_QUOTES,'UTF-8')?>" <?=$region===$r?'selected':''?>><?=htmlspecialchars($r,ENT_QUOTES,'UTF-8')?></option><?php endforeach; ?></select><button type="submit">Search</button></form></section><section class="grid"><?php foreach($species as $b): ?><article class="card"><h2><?=htmlspecialchars($b['common_name'],ENT_QUOTES,'UTF-8')?></h2><p><em><?=htmlspecialchars($b['scientific_name'],ENT_QUOTES,'UTF-8')?></em></p><p><?=htmlspecialchars($b['notes']??'',ENT_QUOTES,'UTF-8')?></p><p><strong>Habitat:</strong> <?=htmlspecialchars($b['habitat']??'',ENT_QUOTES,'UTF-8')?></p><p class="muted"><strong>Regions:</strong> <?=htmlspecialchars(implode(', ',$b['regions']??[]),ENT_QUOTES,'UTF-8')?></p></article><?php endforeach; ?></section><?php if(!$species): ?><section class="card">No matching species found.</section><?php endif; ?></main></body></html>
