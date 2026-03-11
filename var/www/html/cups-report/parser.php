<?php

$cache="/var/www/html/cups-report/cache.json";

$data=["details"=>[]];

$logs=glob("/var/log/cups/page_log*");

foreach($logs as $log){

if(!file_exists($log)) continue;

if(substr($log,-3)==".gz"){
$lines=explode("\n",shell_exec("zcat $log"));
}else{
$lines=file($log);
}

foreach($lines as $line){

if(!preg_match('/^(.*?) (.*?) (\d+) \[(.*?)\] total (\d+) - ([^ ]+) (.*?) - -$/',$line,$m))
continue;

$printer=$m[1];
$user=$m[2];
$datetime=$m[4];
$pages=$m[5];
$ip=$m[6];
$file=$m[7];

$dt=DateTime::createFromFormat("d/M/Y:H:i:s O",$datetime);

if(!$dt) continue;

$data["details"][]=[

"date"=>$dt->format("Y-m-d"),
"time"=>$dt->format("H:i:s"),
"printer"=>$printer,
"user"=>$user,
"ip"=>$ip,
"file"=>$file,
"pages"=>$pages

];

}

}

file_put_contents($cache,json_encode($data,JSON_PRETTY_PRINT));

?>
