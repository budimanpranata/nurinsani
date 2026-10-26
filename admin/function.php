<?php require_once __DIR__ . '/../php8_compat.php';

function sendsms($nohp, $pesan, $modem, $time)
{
	
	$pesan = str_replace("'", "\'", $pesan);
	
	if (strlen($pesan)<=160)
	{ 
		$query = "INSERT INTO outbox (DestinationNumber, TextDecoded, SendingDateTime, SenderID, CreatorID) 
		          VALUES ('$nohp', '$pesan', '$time', '$modem', 'Gammu')";		  
		$hasil = mysql_query($query);
	}
	else
	{
		$jmlSMS = ceil(compat_num(strlen($pesan)/153));
		$pecah  = str_split($pesan, 153);
		 
		$query = "SHOW TABLE STATUS LIKE 'outbox'";
		$hasil = mysql_query($query);
		$data  = mysql_fetch_array($hasil);
		$newID = $data['Auto_increment'];
	
		$random = rand(1, 255);
		$headerUDH = sprintf("%02s", strtoupper(dechex($random)));
	
		for ($i=1; $i<=$jmlSMS; $i++)
		{
		
			$udh = "050003".$headerUDH.sprintf("%02s", $jmlSMS).sprintf("%02s", $i);
			$msg = $pecah[$i-1];
	  
			if ($i == 1) 
			{	
				$query = "INSERT INTO outbox (DestinationNumber, UDH, TextDecoded, ID, MultiPart, SendingDateTime, SenderID, CreatorID)
						  VALUES ('$nohp', '$udh', '$msg', '$newID', 'true', '$time', '$modem', 'Gammu')";	  	  
			}						 
			else $query = "INSERT INTO outbox_multipart(UDH, TextDecoded, ID, SequencePosition)
						   VALUES ('$udh', '$msg', '$newID', '$i')";					  
			mysql_query($query); 	  
	  	  
		}
   }
   
   return 'SMS sedang dikirim...';
}

?>