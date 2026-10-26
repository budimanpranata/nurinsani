var xmlhttp = createRequestObject();
function createRequestObject() {
    var ro;
    var browser = navigator.appName;
    if(browser == "Microsoft Internet Explorer"){
        ro = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        ro = new XMLHttpRequest();
    }
    return ro;
}

function DinamisCountry(combobox)
{
    var kode = combobox.value;
    if (!kode) return;
    xmlhttp.open('get', 'template/get_provinsi-opr.php?kode='+kode, true);
    xmlhttp.onreadystatechange = function() {
        if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200))
        {
             document.getElementById("provinsi").innerHTML = xmlhttp.responseText;
        }
        return false;
    }
    xmlhttp.send(null);
}

function DinamisProvinsi(combobox)
{
    var kode = combobox.value;
    if (!kode) return;
    xmlhttp.open('get', 'template/get_wilayah-opr.php?kode='+kode, true);
    xmlhttp.onreadystatechange = function() {
        if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200))
        {
             document.getElementById("wilayah").innerHTML = xmlhttp.responseText;
        }
        return false;
    }
    xmlhttp.send(null);
}


function DinamisWilayah(combobox)
{
    var kode = combobox.value;
    if (!kode) return;
    xmlhttp.open('get', 'template/get_kecamatan-opr.php?kode='+kode, true);
    xmlhttp.onreadystatechange = function() {
        if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200))
        {
             document.getElementById("kecamatan").innerHTML = xmlhttp.responseText;
        }
        return false;
    }
    xmlhttp.send(null);
}







