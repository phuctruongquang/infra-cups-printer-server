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
# PAGINATION DETAILS
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

# --------------------
# TOP USERS PAGINATION
# --------------------

$userPerPage=10;

$userPage=isset($_GET['user_page'])?(int)$_GET['user_page']:1;

if($userPage<1)$userPage=1;

$totalUsers=count($users);

$userStart=($userPage-1)*$userPerPage;

$usersPage=array_slice($users,$userStart,$userPerPage,true);

$totalUserPages=ceil($totalUsers/$userPerPage);

# --------------------

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

<title>Print Dashboard</title>

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

<h1>Print Dashboard</h1>

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

foreach($usersPage as $u=>$c){

echo "<tr><td>$u</td><td>$c</td></tr>";

}

?>

</table>

<div class="pagination">

<?php

if($userPage>1){

$prev=$userPage-1;

echo "<a href='?month=$month&search=$search&page=$page&user_page=$prev'>Prev</a>";

}

for($i=1;$i<=$totalUserPages;$i++){

$class=$i==$userPage?"active":"";

echo "<a class='$class'
href='?month=$month&search=$search&page=$page&user_page=$i'>$i</a>";

}

if($userPage<$totalUserPages){

$next=$userPage+1;

echo "<a href='?month=$month&search=$search&page=$page&user_page=$next'>Next</a>";

}

?>

</div>

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
