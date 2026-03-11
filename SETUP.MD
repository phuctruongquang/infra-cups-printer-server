# System Setup Guide

This guide describes the steps required to deploy the centralized printing system using CUPS and Apache.

---

# 1. Install Ubuntu Server

Create a virtual machine on Hyper-V.

Recommended configuration:

CPU: 2 cores
RAM: 4 GB
Disk: 40 GB

Install **Ubuntu Server 22.04 LTS**.

---

# 2. Update & Install packet

```bash
sudo apt update

sudo apt install -y \
apache2 \
php \
php-cli \
cups \
cups-client \
logrotate \
mailutils
```
image 1-2

Enable Service
```bash
sudo systemctl enable apache2
sudo systemctl enable cups
```



===============================
---

# 3. Config CUPS

```bash
nano /etc/cups/cupsd.conf
```

Change Listen:
Image 3

Config web admin:

image 4

```bash
LogLevel warn
Port 631
Listen /run/cups/cups.sock
Browsing Off
BrowseLocalProtocols dnssd
DefaultAuthType Basic
DefaultEncryption Never
WebInterface Yes
<Location />
  Allow all
</Location>
<Location /admin>
  AuthType Basic
  Require user @SYSTEM
  Order allow,deny
  Allow all
</Location>
<Location /admin/conf>
  AuthType Basic
  Require user @SYSTEM
  Order allow,deny
  Allow all
</Location>
<Policy default>
  # Job-related operations must be done by the owner or an administrator...
  <Limit Send-Document Send-URI Hold-Job Release-Job Restart-Job Purge-Jobs Set-Job-Attributes Create-Job-Subscription Renew-Subscription Cancel-Subscription Get-Notifications Reprocess-Job Cancel-C>
    Require user @OWNER @SYSTEM
    Order deny,allow
  </Limit>
  # All administration operations require an administrator to authenticate...
  <Limit Pause-Printer Resume-Printer Set-Printer-Attributes Enable-Printer Disable-Printer Pause-Printer-After-Current-Job Hold-New-Jobs Release-Held-New-Jobs Deactivate-Printer Activate-Printer Re>
    AuthType Default
    Require user @SYSTEM
    Order deny,allow
</Limit>
  # Only the owner or an administrator can cancel a job...
  <Limit Cancel-Job>
    Order deny,allow
    Require user @OWNER @SYSTEM
  </Limit>
  <Limit All>
  Order deny,allow
  </Limit>
</Policy>
MaxClients 100
MaxJobs 0
```

Restart service:
```bash
sudo systemctl restart cups
```
Add user to Lpadmin
```bash
sudo usermod -aG lpadmin your_user
```

Access web page:
```bash
http://IP:631
```
imgae 5
=================================================


# 4. Log Rotation Configuration

Edit time save log rotate 7 change to 60
```bash
sudo nano /etc/logrotate.d/cups-daemon
```
Image 6


======================

# 5. Create Dashboard Directory
Create Dashboard managemnet
```bash
mkdir -p /var/www/html/cups-report
cd /var/www/html/cups-report
```

Create file
```bash
touch parser.php index.php cache.json monthly_report.php

```
------------
```bash
nano parser.php
```
-------------
Edit parser
```bash
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

if(!preg_match('/^(.*?) (.*?) (\d+) \[(.*?)\] total (\d+) - ([0-9\.]+) (.*?) - -$/',$line,$m))
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
```

Edit index.php
```bash
<?php

$json=file_exists("cache.json") ?
json_decode(file_get_contents("cache.json"),true):[];

$all=$json['details'] ?? [];

$month=$_GET['month'] ?? date("Y-m");
$search=strtolower($_GET['search'] ?? "");

$details=[];

foreach($all as $d){

if(substr($d['date'],0,7)!=$month) continue;

$text=strtolower(
$d['user'].$d['printer'].$d['ip'].$d['file']
);

if($search && strpos($text,$search)===false)
continue;

$details[]=$d;

}

usort($details,function($a,$b){
return strtotime($b['date']." ".$b['time'])
-
strtotime($a['date']." ".$a['time']);
});

# --------------------
# PAGINATION
# --------------------

$perPage=10;

$total=count($details);

$page=isset($_GET['page'])?(int)$_GET['page']:1;

if($page<1)$page=1;

$start=($page-1)*$perPage;

$detailsPage=array_slice($details,$start,$perPage);

$totalPages=ceil($total/$perPage);

# --------------------

$users=[];
$printers=[];
$hours=[];
$days=[];

foreach($details as $d){

$users[$d['user']] =
($users[$d['user']] ?? 0) + $d['pages'];

$printers[$d['printer']] =
($printers[$d['printer']] ?? 0) + $d['pages'];

$h=date("H",strtotime($d['time']));
$hours[$h]=($hours[$h] ?? 0)+$d['pages'];

$day=$d['date'];
$days[$day]=($days[$day] ?? 0)+$d['pages'];

}

arsort($users);
arsort($printers);
ksort($hours);
ksort($days);

if(isset($_GET['export'])){

header("Content-Type:text/csv");
header("Content-Disposition:attachment;filename=cups_report.csv");

echo "Date,Time,Printer,User,IP,File,Pages\n";

foreach($details as $d){

echo "{$d['date']},{$d['time']},{$d['printer']},{$d['user']},{$d['ip']},{$d['file']},{$d['pages']}\n";

}

exit;

}

?>

<!DOCTYPE html>
<html>

<head>

<title>Pharmacity Print Dashboard</title>

<meta http-equiv="refresh" content="30">

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>

body{
font-family:Arial;
background:#f3f3f3;
margin:40px;
}

h1,h2{
text-align:center;
}

table{
border-collapse:collapse;
margin:auto;
width:90%;
background:white;
margin-bottom:20px;
}

th,td{
padding:8px;
border:1px solid #ddd;
text-align:center;
}

th{
background:#333;
color:white;
}

.controls{
text-align:center;
margin-bottom:25px;
}

.controls input{
padding:6px;
margin-right:10px;
}

.controls button{
padding:6px 12px;
cursor:pointer;
}

.chart{
width:700px;
margin:auto;
margin-bottom:50px;
}

.pagination{
text-align:center;
margin-bottom:40px;
}

.pagination a{
padding:6px 10px;
border:1px solid #ccc;
margin:3px;
text-decoration:none;
color:black;
}

.pagination a.active{
background:#333;
color:white;
}

</style>

</head>

<body>

<h1>Pharmacity Print Dashboard</h1>

<div class="controls">

<form>

<input type="month" name="month"
value="<?php echo $month;?>">

<input type="text" name="search"
placeholder="Search user / printer / IP / file"
value="<?php echo $_GET['search'] ?? '';?>">

<button>View</button>

<a href="?month=<?php echo $month ?>&search=<?php echo $search ?>&export=1">
<button type="button">Export Excel</button>
</a>

</form>

</div>

<h2>Print Details</h2>

<table>

<tr>
<th>Date</th>
<th>Time</th>
<th>Printer</th>
<th>User</th>
<th>IP</th>
<th>File</th>
<th>Pages</th>
</tr>

<?php

foreach($detailsPage as $d){

echo "<tr>
<td>{$d['date']}</td>
<td>{$d['time']}</td>
<td>{$d['printer']}</td>
<td>{$d['user']}</td>
<td>{$d['ip']}</td>
<td>{$d['file']}</td>
<td>{$d['pages']}</td>
</tr>";

}

?>

</table>

<div class="pagination">

<?php

if($page>1){

$prev=$page-1;

echo "<a href='?month=$month&search=$search&page=$prev'>Prev</a>";

}

for($i=1;$i<=$totalPages;$i++){

$class=$i==$page?"active":"";

echo "<a class='$class'
href='?month=$month&search=$search&page=$i'>$i</a>";

}

if($page<$totalPages){

$next=$page+1;

echo "<a href='?month=$month&search=$search&page=$next'>Next</a>";

}

?>

</div>

<h2>Top Users</h2>

<table>
<tr><th>User</th><th>Totals</th></tr>

<?php

foreach($users as $u=>$c){

echo "<tr><td>$u</td><td>$c</td></tr>";

}

?>

</table>

<div class="chart">
<canvas id="userChart"></canvas>
</div>

<h2>Top Printers</h2>

<table>
<tr><th>Printer</th><th>Totals</th></tr>

<?php

foreach($printers as $p=>$c){

echo "<tr><td>$p</td><td>$c</td></tr>";

}

?>

</table>

<div class="chart">
<canvas id="printerChart"></canvas>
</div>

<div class="chart">

<h2>Printing by Hour</h2>

<canvas id="hourChart"></canvas>

</div>

<div class="chart">

<h2>Printing by Day</h2>

<canvas id="dayChart"></canvas>

</div>

<script>

new Chart(document.getElementById("userChart"),{

type:'bar',

data:{
labels:<?php echo json_encode(array_keys($users));?>,
datasets:[{
label:'Pages',
data:<?php echo json_encode(array_values($users));?>
}]
}

});

new Chart(document.getElementById("printerChart"),{

type:'pie',

data:{
labels:<?php echo json_encode(array_keys($printers));?>,
datasets:[{
data:<?php echo json_encode(array_values($printers));?>
}]
}

});

new Chart(document.getElementById("hourChart"),{

type:'line',

data:{
labels:<?php echo json_encode(array_keys($hours));?>,
datasets:[{
label:'Pages',
data:<?php echo json_encode(array_values($hours));?>
}]
}

});

new Chart(document.getElementById("dayChart"),{

type:'line',

data:{
labels:<?php echo json_encode(array_keys($days));?>,
datasets:[{
label:'Pages',
data:<?php echo json_encode(array_values($days));?>
}]
}

});

</script>

</body>
</html>
```

edit sent mail
```bash
<?php

$data=json_decode(file_get_contents("cache.json"),true);

$month = $argv[1] ?? date("Y-m",strtotime("first day of last month"));

$csv="/tmp/print_report_$month.csv";

$users=[];
$printers=[];
$totalPages=0;
$totalJobs=0;

# -------------------------
# CREATE CSV
# -------------------------

$f=fopen($csv,"w");

fputcsv($f,["Date","Time","Printer","User","IP","File","Pages"]);

foreach($data['details'] as $d){

if(substr($d['date'],0,7)!=$month) continue;

fputcsv($f,[
$d['date'],
$d['time'],
$d['printer'],
$d['user'],
$d['ip'],
$d['file'],
$d['pages']
]);

$totalJobs++;
$totalPages += $d['pages'];

$users[$d['user']] =
($users[$d['user']] ?? 0) + $d['pages'];

$printers[$d['printer']] =
($printers[$d['printer']] ?? 0) + $d['pages'];

}

fclose($f);

arsort($users);
arsort($printers);

$topUsers=array_slice($users,0,5,true);
$topPrinters=array_slice($printers,0,5,true);

# -------------------------
# EMAIL BODY
# -------------------------

$body="Pharmacity Monthly Print Report\n";
$body.="-------------------------------------\n";
$body.="Report Month : $month\n";
$body.="Server       : sgpmcsscups\n";
$body.="Generated At : ".date("Y-m-d H:i:s")."\n\n";

$body.="SUMMARY\n";
$body.="Total Jobs  : $totalJobs\n";
$body.="Total Pages : $totalPages\n\n";

$body.="TOP USERS\n";

foreach($topUsers as $u=>$p){

$body.=sprintf("%-20s %s pages\n",$u,$p);

}

$body.="\nTOP PRINTERS\n";

foreach($topPrinters as $p=>$c){

$body.=sprintf("%-20s %s pages\n",$p,$c);

}

$body.="\nFull report attached (CSV).\n";
$body.="\nThis is an automated report generated by the CUPS Print Monitoring System.\n";

# -------------------------
# SEND MAIL
# -------------------------

$to="phuc.truongquang@pharmacity.vn,thanh.ngodinh@pharmacity.vn,nam.lieuhoang@pharmacity.vn,phat.nt@pharmacity.vn,tuan.tranquang@pharmacity.vn";

$subject="Pharmacity Print Report $month";

$boundary=md5(time());

$headers="From: printer-report@pharmacity.vn\r\n";
$headers.="MIME-Version: 1.0\r\n";
$headers.="Content-Type: multipart/mixed; boundary=\"$boundary\"";

$message="--$boundary\r\n";
$message.="Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$message.=$body."\r\n";

$content=file_get_contents($csv);

$message.="--$boundary\r\n";
$message.="Content-Type: application/octet-stream; name=\"".basename($csv)."\"\r\n";
$message.="Content-Disposition: attachment; filename=\"".basename($csv)."\"\r\n";
$message.="Content-Transfer-Encoding: base64\r\n\r\n";
$message.=chunk_split(base64_encode($content));

$message.="--$boundary--";

mail($to,$subject,$message,$headers);

echo "CSV report sent for $month\n";

?>
```






```bash
```

```bash
```

```bash
```



# 6. Add Network Printers

Add printers using **JetDirect (RAW)** connection.

Example:

Printer-01

```
socket://PRINTER_IP:9100
```

Printer-02

```
socket://PRINTER_IP:9100
```

---

# 7. Install Apache Web Server

```bash
sudo apt install apache2 -y
```

Start service:

```bash
sudo systemctl enable apache2
sudo systemctl start apache2
```

Test access:

```
http://SERVER_IP
```

---

# 8. Deploy Print Report Web Interface

Copy web files to:

```
/var/www/html/
```

Restart Apache:

```bash
sudo systemctl restart apache2
```

---

# 9. Configure Monthly Report Script

Create cron job:

```bash
crontab -e
```

Add task:

```
0 1 1 * * /scripts/send_report.sh
```

This task will generate and send the monthly report automatically.

---

# 10. Verify System

Check the following:

* CUPS service running
* Printers accessible
* Web dashboard accessible
* Reports generated successfully
* Email notification working

---

# Deployment Completed

The centralized printing system is now ready for use.
