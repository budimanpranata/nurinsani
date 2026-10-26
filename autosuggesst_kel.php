<?php require_once __DIR__ . '/koneksi_db.php';
session_start();
$userid=$_SESSION['userid'];
   $db = db_ni();
   
    if(!$db) {
        echo 'Could not connect to the database.';
    } else {
        $dunit = $db->query("select kode_branch from user where userid='$userid'");
        while ($hasil = $dunit ->fetch_object()) {   
            $unit=$hasil->kode_branch;
            }
   
        if(isset($_POST['queryString'])) {
            $queryString = $db->real_escape_string($_POST['queryString']);
           
            if(strlen($queryString) >0) {

                $query = $db->query("SELECT anggota.UNIT,anggota.CIF,anggota.NOREK,anggota.CUST_SHORT_NAME,nama_kel FROM anggota
left join kelompok on kelompok.code_kel = anggota.CODE_KEL WHERE anggota.CIF LIKE '$queryString%' and anggota.unit='$unit' or anggota.CUST_SHORT_NAME LIKE '$queryString%' ");
               
                if(db_num_rows($query) == 0){
                $query = $db->query(" SELECT loan.UNIT,loan.CIF,loan.no_anggota as NOREK,loan.CUST_SHORT_NAME,nama_kel FROM loan
left join kelompok on kelompok.code_kel = loan.CODE_KEL WHERE loan.CIF LIKE '$queryString%' and loan.unit='$unit'or loan.CUST_SHORT_NAME LIKE '$queryString%' and loan.unit='$unit'");
                   

    }
    if(db_num_rows($query) == 0){
                $query = $db->query(" SELECT anggota.code_kel as CIF ,nama_kel as CUST_SHORT_NAME FROM anggota
left join kelompok on kelompok.code_kel = anggota.CODE_KEL WHERE anggota.code_kel LIKE '$queryString%' and anggota.unit='$unit' or kelompok.nama_kel LIKE '$queryString%' and anggota.unit='$unit' group by anggota.code_kel ");
                   

    }
               
               
                if($query) {
                echo '<ul>';
                    while ($result = $query ->fetch_object()) {
                         echo '<li onClick="fill(\''.addslashes($result->CUST_SHORT_NAME).'\'); fill2(\''.addslashes($result->CIF).'\');fill3(\''.addslashes($result->nama_kel).'\');">'.$result->CIF.'&nbsp;&nbsp;'.$result->CUST_SHORT_NAME.'&nbsp;&nbsp;'.$result->UNIT.'&nbsp;&nbsp;'.$result->nama_kel.'</li>';
                     }
                echo '</ul>';
                   
                } else {
                    echo 'OOPS we had a problem :(';
                }
            } else {
                // do nothing
            }
        } else {
            echo 'There should be no direct access to this script!';
        }
    }
?>
