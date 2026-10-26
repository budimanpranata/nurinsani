<?php require_once __DIR__ . '/../koneksi_db.php';
session_start();
ob_start();
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


                $query = $db->query(" SELECT calon_loan.UNIT,calon_loan.CIF,calon_loan.no_anggota as NOREK,calon_loan.CUST_SHORT_NAME,nama_kel FROM calon_loan
left join kelompok on kelompok.code_kel = calon_loan.CODE_KEL 
inner join branch on branch.kode_branch = calon_loan.unit
WHERE calon_loan.CIF LIKE '$queryString%' and code_area ='$userid' or calon_loan.CUST_SHORT_NAME LIKE '$queryString%' and code_area ='$userid'  ");
                   

    
    
               
               
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
