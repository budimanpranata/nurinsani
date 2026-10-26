// variable validation teller
function FormLogbook_onsubmit(){
	if (document.FormLogbook.kode_cabang.value ==""){
		alert("Maaf, kode cabang belum di isi.")
		document.FormLogbook.kode_cabang.focus();
	return false;
	}
	if (document.FormLogbook.cif.value ==""){
	alert("Maaf, cif belum di isi.")
		document.FormLogbook.cif.focus();
	return false;
	}
	if (document.FormLogbook.nama.value ==""){
		alert("Maaf, nama belum di isi.")
		document.FormLogbook.nama.focus();
	return false;
	}
	if (document.FormLogbook.jenis_mutasi.value ==""){
		alert("Maaf, data mutasi belum di isi.")
		document.FormLogbook.jenis_mutasi.focus();
	return false;
	}
	if (document.FormLogbook.jenis.value.==""){
		alert("Sorry, jenis tidak boleh kosong.")
		document.FormLogbook.jenis.focus();
	return false;
	}
	if (document.FormLogbook.no_skep.value.==""){
		alert("Sorry, nomor skep tidak boleh kosong.")
		document.FormLogbook.no_skep.focus();
	return false;
	}
if (document.FormLogbook.penerbit.value.==""){
		alert("Sorry, penerbit tidak boleh kosong.")
		document.FormLogbook.penerbit.focus();
	return false;
	}
}


// variable validation member
function formb_onsubmit(){
	if (document.formb.code.value ==""||document.formb.name.value ==""||document.formb.address.value ==""||document.formb.no_phone.value ==""){
		alert("Sorry, anda belum melengkapi data.")
		document.formb.code.focus();
	return false;
	}
	if (document.formb.code.value.length < 8){
		alert("Sorry, code terlalu pendek.")
		document.formb.code.focus();
	return false;
	}
}
function pertanyaan()
{
 if(confirm(‘Anda yakin yang ingin anda posting ini sudah benar?’))
{
return true;
}
else
{
return false;
}
}

