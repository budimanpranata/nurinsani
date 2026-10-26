<?php require_once __DIR__ . '/../php8_compat.php'; 
include "koneksi.php";

$ambil=$_GET['kirim'];

if ($ambil=='semua'){
 $sql="SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol FROM dwh inner join branch on branch.unit = dwh.Ket group by ket order by region";
  $total=mysql_fetch_array(mysql_query("SELECT sum(os) as tot_os, count(noa) as tot_noa, sum(ytd_os) as tot_ytd_os, sum(mtd_os) as tot_mtd_os, sum(dd_os) as tot_dd_os, count(ytd_noa) as tot_ytd_noa, count(mtd_noa) as tot_mtd_noa,
 count(dd_noa) as tot_dd_noa, sum(ytd_vol) as tot_ytd_vol, sum(mtd_vol) as tot_mtd_vol, sum(dd_vol) as tot_dd_vol FROM dwh")); 
 var_dump($total['tot_os']);
 } else {
 $sql="SELECT ket, os, noa, ytd_os, mtd_os, dd_os, ytd_noa, mtd_noa,dd_noa, ytd_vol,mtd_vol,dd_vol FROM dwh dwh inner join branch on branch.unit = dwh.Ket WHERE buss_date = '".$ambil."' order by region"; 
 $total=mysql_fetch_array(mysql_query("SELECT sum(os) as tot_os, count(noa) as tot_noa, sum(ytd_os) as tot_ytd_os, sum(mtd_os) as tot_mtd_os, sum(dd_os) as tot_dd_os, count(ytd_noa) as tot_ytd_noa, count(mtd_noa) as tot_mtd_noa,
 count(dd_noa) as tot_dd_noa, sum(ytd_vol) as tot_ytd_vol, sum(mtd_vol) as tot_mtd_vol, sum(dd_vol) as tot_dd_vol FROM dwh WHERE buss_date = '".$ambil."'")); 
 }
 
$result = mysql_query($sql);
 echo "<html>";
    echo "<head>";
    echo "<title>dwh</title>";
    echo "<link href=\"tabel.css\" rel=\"stylesheet\" type=\"text/css\" />";
    echo "</head>";
    echo "<body>";
    
    
echo "<table border='1' class='table1'>
  <tr>
        <th rowspan='2' row>Keterangan </th>
        <th colspan='2' >Posisi</th>
        <th colspan='3' >Growth OS </th>
        <th colspan='3' >Growth NoA </th>
        <th colspan='3' >Disbursement</th>
        <th colspan='3' >NPL</th>
        </tr>
      <tr>
        <th >OS</th>
        <th >NOA</th>
        <th >YTD</th>
		<th >MTD</th>
		<th >DD</th>
        <th >YTD </th>
        <th >MTD</th>
		<th >DD</th>
        <th >YTD</th>
        <th >MTD</th>
		<th >DD</th>
        <th >YTD</th>
        <th >MTD</th>
		<th >DD</th>
        </tr>
</tr>";

while($row = mysql_fetch_array($result))
  {
  echo "<tr>";
  echo "<td>" . $row['ket'] . "</td>";
  echo "<td>" . number_format (compat_num($row['os']/1000000)) . "</td>";
  echo "<td>" . number_format (compat_num($row['noa'])) . "</td>";
  echo "<td>" . number_format (compat_num($row['ytd_os']/1000000 )). "</td>";
  echo "<td>" . number_format (compat_num($row['mtd_os']/1000000)) . "</td>";
  echo "<td>" . number_format (compat_num($row['dd_os']/1000000)) . "</td>";
  echo "<td>" . number_format (compat_num($row['ytd_noa'])) . "</td>";
  echo "<td>" . number_format (compat_num($row['mtd_noa'])) . "</td>";
  echo "<td>" . number_format (compat_num($row['dd_noa'])) . "</td>";
  echo "<td>" . number_format (compat_num($row['ytd_vol']/1000000)) . "</td>";
  echo "<td>" . number_format (compat_num($row['mtd_vol']/1000000)) . "</td>";
  echo "<td>" . number_format (compat_num($row['dd_vol']/1000000)) . "</td>";
  echo "</tr>";
   }
  echo "<tr>";
	echo "<td colspan='1' border ='1' align='center'>TOTAL</td>";
	echo	"<td>" . number_format (compat_num($total['tot_os']/1000000)). "</td>";
	echo 	"<td> ". number_format (compat_num($total['tot_noa']))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_ytd_os']/1000000))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_mtd_os']/1000000))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_dd_os']/1000000))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_ytd_noa']))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_mtd_noa']))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_dd_noa']))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_kredit']/1000000))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_mtd_vol']/1000000))."</td>";
	echo 	"<td> ".number_format (compat_num($total['tot_dd_vol']/1000000))."</td>";
echo "</tr>";
 
echo "</table>";

mysql_close($con);
echo "</body>";
    echo "</html>";

?>
