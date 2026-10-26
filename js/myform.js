function valcust()
{
		
		if(!valid_required(document.frmcust.text_company.value))
		{
			alert("Company customer is empty!!");
   			return false
		}
	    
		if(!valid_required(document.frmcust.wilayah.value))
		{
			alert("Region is empty!!");
   			return false
		}
		if(!valid_required(document.frmcust.text_telp.value))
		{
			alert("Customer phone is empty!!");
   			return false
		}
    return true
}

function valsearch_cust()
{
	    if(!valid_required(document.frmcust.text_kategori.value))
		{
			alert("Select category searching!!");
   			return false
		}
		if(!valid_required(document.frmcust.text_search.value))
		{
			alert("Insert data!!");
   			return false
		}
    return true
}

function valsearch()
{
	    if(!valid_required(document.frmcust.kategori.value))
		{
			alert("Select category searching!!");
   			return false
		}
		if(!valid_required(document.frmcust.key.value))
		{
			alert("Insert data!!");
   			return false
		}
    return true
}

function valtranf()
{
	    if(!valid_required(document.frmtranf.text_cust.value))
		{
			alert("Customer data is empty!!");
   			return false
		}
    return true
}

function valschedule()
{
	    if(!valid_required(document.frmschedule.text_cust.value))
		{
			alert("Customer is empty!!");
   			return false
		}
		if(!valid_required(document.frmschedule.text_sector.value))
		{
			alert("Inflight sector is empty!!");
   			return false
		}
		if(!valid_required(document.frmschedule.text_waktu.value))
		{
			alert("Inflight time is empty!!");
   			return false
		}
	    if(!valid_required(document.frmschedule.text_aircraft.value))
		{
			alert("Aircraft type is empty!!");
   			return false
		}
		if(!valid_required(document.frmschedule.text_rute1.value))
		{
			alert("Route inflight is empty !!");
   			return false
		}
    return true
}

function valaktif()
{
	    if(!valid_required(document.frmaktif.text_cust.value))
		{
			alert("Customer data is empty!!");
   			return false
		}
		if(!valid_required(document.frmaktif.date.value))
		{
			alert("Start date is empty!!");
   			return false
		}
		if(!valid_required(document.frmaktif.date2.value))
		{
			alert("End date is empty!!");
   			return false
		}
    return true
}

function valsector()
{
	    if(!valid_required(document.frmsector.text_market.value))
		{
			alert("Market data is empty!!");
   			return false
		}
		if(!valid_required(document.frmsector.tujuan.value))
		{
			alert("Sector tujuan belum terisi!!");
   			return false
		}
    return true
}

function valrotasi()
{
	    if(!valid_required(document.frmrotasi.rotasi.value))
		{
			alert("Rotation categori is empty!!");
   			return false
		}
		if(!valid_required(document.frmrotasi.text_rotasi.value))
		{
			alert("Rotation menu is empty!!");
   			return false
		}
    return true
}

function valreporthari()
{
	    if(!valid_required(document.frmhari.date.value))
		{
			alert("Start Date is empty!!");
   			return false
		}
	    if(!valid_required(document.frmhari.date2.value))
		{
			alert("End Date is empty!!");
   			return false
		}
		if(!valid_required(document.frmhari.text_cust.value))
		{
			alert("Customer Data is empty!!");
   			return false
		}
		if(!valid_required(document.frmhari.text_key.value))
		{
			alert("Sector Data is empty!!");
   			return false
		}
    return true
}

function valformestimasi()
{
	    if(!valid_required(document.frmestimasi.date.value))
		{
			alert("Start date is empty!!");
   			return false
		}
		if(!valid_required(document.frmestimasi.date2.value))
		{
			alert("End date is empty!!");
   			return false
		}
    return true
}

function valorder2()
{
	if(!valid_required(document.frmorder.text_cust.value))
		{
			alert("Customer data is empty!!");
   			return false
		}
	if(!valid_required(document.frmorder.date.value))
		{
			alert("Date is empty!!");
   			return false
		}
    return true
}