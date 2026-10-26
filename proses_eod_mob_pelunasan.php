<?php require_once __DIR__ . '/koneksi_db.php'; 

$con = db_ni();
if (mysqli_connect_errno()) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
    exit;
}
 
// Turn autocommit off
mysqli_autocommit($con,FALSE);
 
 
include "function.php";
$tanggal="2024-05-27";

 $cek= db_fetch_array(db_query($con,"select param_tgl from param_tgl WHERE nama_param_tgl ='EOD_MOB'"));
$tanggal = $cek['param_tgl']; 
//$tanggal="2026-01-10";         
$select = db_query ($con,"Select pelunasan.cif as cif,loan.angsuran,loan.cao,Cust_Short_name,loan.os as os,
no_anggota as norek ,code_kel,status_app,loan.pokok,run_tenor,loan.unit, loan.bulat as bulat,loan.ijaroh as ijaroh,nominal,pelunasan.ket as jenis_pelunasan
from pelunasan left join loan on loan.cif = pelunasan.cif where tgl_tagih ='$tanggal' order by loan.unit ");
while($data = db_fetch_array($select)){
 
        $norek=$data['norek'];
        $unit=$data ['unit'];
        $angsuran=$data ['angsuran'];
        $nominal=$data ['nominal'];
        $angsuran_pokok=$data ['pokok'];
        $ijaroh=$data ['ijaroh'];
        $code_kel=$data['code_kel'];
        $ke = $data['run_tenor'];
        $cao=$data['cao'];
        $ciff=$data['cif'];

        $os=$data['os'];
        $status_app=$data['status_app'];
        $run_tenor=$data['run_tenor'];
        $jenis_pelunasan=$data['jenis_pelunasan'];
        $pb=$run_tenor + 1;
        $next_tenor=$run_tenor + 2;
 
        $angsuran=$data['angsuran'];
        $bulat=$data['bulat'];
        $nama=mysqli_real_escape_string($con,($data['Cust_Short_name']));
        $kodetr=8;
        $kodetr=acakangkahuruf($kodetr);
        $kode_trans="BU/".$unit.$kodetr;      
        

        $ket1="Setoran Pelunasan An $nama";
if($nominal > 0 ){        
 $ket1="Setoran Pelunasan An $nama";
        $input ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel',0,'01','$nominal','$unit','$ket1','','$cao','1','$tanggal','$kode_trans')";
$inputtrnsk = db_query($con,$input);
}else{
        $ket1="Setoran Pelunasan An $nama";
}
        
        $cek_saldo_pokok= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as saldo from simpanan_pokok WHERE cif ='".$ciff."'"));
        $saldo_pokok=$cek_saldo_pokok['saldo']; 
        $cek_saldo_wajib= db_fetch_array(db_query($con,"select sum(KREDIT)- sum(DEBIT) as saldo from simpanan_wajib WHERE cif ='".$ciff."'"));
        $saldo_wajib=$cek_saldo_wajib['saldo']; 

        //var_dump($saldo_wajib);

       switch ($jenis_pelunasan) {
            case "pelunasanDO":
                $jurnal_umum=db_fetch_array(db_query($con,"SELECT max(nomor_jurnal) FROM jurnal_umum where unit ='$unit' ORDER BY tanggal_selesai DESC"));
                $nomor_jurnal=$jurnal_umum[0]+1;

                if($saldo_pokok > 0){
                    $ket2="PB An $nama dari Simpanan Pokok";
                    $input1 ="Insert into simpanan_pokok (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','$saldo_pokok','03',0,'$unit','$ket2','','$cao','1','$kode_trans')";
                    $input2 ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','0','03',$saldo_pokok,'$unit','$ket2','','$cao','1','$tanggal','$kode_trans')";
                    

                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','3102000','$tanggal','Bukti SYSTEM','$ket1','$saldo_pokok','$unit')");
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$saldo_pokok','$unit')");

                    $inputtr = db_query($con,$input1);
                    $inputtrnskk = db_query($con,$input2);

                }else{

                }
                
                if($saldo_wajib >0){
                    $ket2="PB An $nama dari Simpanan Wajib";
                    $input1 ="Insert into simpanan_wajib (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','$saldo_wajib','03',0,'$unit','$ket2','','$cao','1','$kode_trans')";
                    $input2 ="Insert into simpanan (BUSS_DATE,NOREK,UNIT,CIF,CODE_KEL,DEBIT,TYPE,KREDIT,USERID,KET,REFF,CAO,blok,tgl_input,kode_transaksi)VALUES('$tanggal','$norek','$unit','$ciff','$code_kel','0','03',$saldo_wajib,'$unit','$ket2','','$cao','1','$tanggal','$kode_trans')";

                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                    values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$saldo_wajib','$unit')");
                    db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                    values('$unit','$kode_trans','3202000','$tanggal','Bukti SYSTEM','$ket1','$saldo_wajib','$unit')");
              
              

                    $inputtr = db_query($con,$input1);
                    $inputtrnskk = db_query($con,$input2);
                }else{
                    
                }

              if($inputtrnsk){
            $ket="Setoran Pelunasan An $nama";       
        

        
                db_query($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
              
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }


            break;

            case "pelunasan19":
             if($inputtrnsk){
            $ket="Setoran Pelunasan An $nama";       
        

        
                db_query($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
              
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
                
                break;
            case "pelunasan":
             if($inputtrnsk){
            $ket="Setoran Pelunasan An $nama";       
        

        
                db_query($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
              
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
               
                break;

            default:
              if($inputtrnsk){
            $ket="Setoran Pelunasan An $nama";       
        

        
                db_query($con,"insert into jurnal_masuk(nomor_jurnal,kode_transaksi,tanggal_selesai,unit) values('$nomor_jurnal','$kode_trans','$tanggal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,debet,id_admin)
                values('$unit','$kode_trans','1120000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
                db_query($con,"insert  into tabel_transaksi(unit,kode_transaksi,kode_rekening,tanggal_transaksi, jenis_transaksi, keterangan_transaksi,kredit,id_admin)
                values('$unit','$kode_trans','2101000','$tanggal','Bukti SYSTEM','$ket1','$nominal','$unit')");
              
                
            }
            else{
            echo "<script>alert('Maaf,.Porses Input Gagal');</script>";
          
            }
               break;   


        }
       
        
        
      
         
    
    
    
    }

 
    if (!mysqli_commit($con)) {
        echo "Transaksi Gagal commit Silahkan ulangi lagi";
        exit();
      }
echo "<script>alert('Setoran pelunasan sukses di simpan');</script>";

     
    
 
?>
