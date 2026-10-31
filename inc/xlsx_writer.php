<?php
/**
 * Penulis file .xlsx sederhana tanpa library tambahan (hanya ZipArchive), untuk export besar bertahap.
 *
 * Baris data ditulis ke file sementara sedikit demi sedikit (tulis_baris), sehingga memori tetap kecil
 * berapa pun jumlah barisnya; di akhir semua bagian dirakit menjadi satu .xlsx (rakit).
 * Teks ditulis sebagai inline string, angka sebagai angka; baris judul memakai gaya tebal putih di atas hijau.
 */

/** Huruf kolom Excel dari indeks 0 (0 -> A, 26 -> AA). */
function xlsx_kolom($i)
{
    $s = '';
    for ($i++; $i > 0; $i = intdiv($i - 1, 26)) {
        $s = chr(65 + ($i - 1) % 26) . $s;
    }
    return $s;
}

/** Teks aman untuk XML (karakter kontrol yang tidak sah di XML dibuang). */
function xlsx_teks($v)
{
    $v = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', (string) $v);
    return htmlspecialchars($v, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * XML satu baris. $sel: daftar array(tipe, nilai) dengan tipe 's' (teks) atau 'n' (angka).
 * $gaya: indeks gaya sel (0 = biasa, 1 = judul, 2 = baris total tebal).
 */
function xlsx_baris_xml($nomor_baris, array $sel, $gaya = 0)
{
    $xml = '<row r="' . $nomor_baris . '">';
    $s = $gaya ? ' s="' . (int) $gaya . '"' : '';
    foreach (array_values($sel) as $i => $c) {
        $ref = xlsx_kolom($i) . $nomor_baris;
        if ($c[0] === 'n') {
            $angka = is_numeric($c[1]) ? $c[1] + 0 : 0;
            $xml .= '<c r="' . $ref . '"' . $s . '><v>' . $angka . '</v></c>';
        } else {
            $xml .= '<c r="' . $ref . '"' . $s . ' t="inlineStr"><is><t xml:space="preserve">' . xlsx_teks($c[1]) . '</t></is></c>';
        }
    }
    return $xml . '</row>';
}

/** Tambahkan baris (XML) ke file bagian data. */
function xlsx_tulis_baris($berkas_bagian, $xml)
{
    return file_put_contents($berkas_bagian, $xml, FILE_APPEND | LOCK_EX) !== false;
}

/**
 * Rakit file .xlsx satu sheet dari judul kolom, lebar kolom, dan file bagian data.
 * $judul: array teks judul; $lebar: array lebar kolom (karakter); $nama_sheet: nama sheet.
 */
function xlsx_rakit($berkas_xlsx, $nama_sheet, array $judul, array $lebar, $berkas_bagian)
{
    return xlsx_rakit_banyak($berkas_xlsx, array(
        array('nama' => $nama_sheet, 'judul' => $judul, 'lebar' => $lebar, 'bagian' => $berkas_bagian),
    ));
}

/** Tulis XML satu worksheet (judul + isi file bagian) ke $berkas_sheet. */
function xlsx_tulis_sheet($berkas_sheet, array $judul, array $lebar, $berkas_bagian)
{
    $f = fopen($berkas_sheet, 'wb');
    fwrite($f, '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
        . '<sheetFormatPr defaultRowHeight="15"/><cols>');
    foreach (array_values($lebar) as $i => $w) {
        fwrite($f, '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . (float) $w . '" customWidth="1"/>');
    }
    $sel_judul = array();
    foreach ($judul as $j) {
        $sel_judul[] = array('s', $j);
    }
    fwrite($f, '</cols><sheetData>' . xlsx_baris_xml(1, $sel_judul, 1));
    if ($berkas_bagian && is_file($berkas_bagian)) {
        $g = fopen($berkas_bagian, 'rb');
        stream_copy_to_stream($g, $f);
        fclose($g);
    }
    fwrite($f, '</sheetData><autoFilter ref="A1:' . xlsx_kolom(max(0, count($judul) - 1)) . '1"/></worksheet>');
    fclose($f);
}

/**
 * Rakit file .xlsx dengan beberapa sheet.
 * $sheets: daftar array('nama' => ..., 'judul' => array, 'lebar' => array, 'bagian' => file bagian data).
 */
function xlsx_rakit_banyak($berkas_xlsx, array $sheets)
{
    $dir = dirname($berkas_xlsx);
    $zip = new ZipArchive();
    if ($zip->open($berkas_xlsx, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        return false;
    }
    $override = $daftar = $rels = $nama_filter = '';
    $sementara = array();
    foreach (array_values($sheets) as $i => $s) {
        $n = $i + 1;
        $nama = substr((string) $s['nama'], 0, 31);
        $berkas_sheet = $dir . '/sheet' . $n . '.xml';
        xlsx_tulis_sheet($berkas_sheet, $s['judul'], $s['lebar'], isset($s['bagian']) ? $s['bagian'] : null);
        $sementara[] = $berkas_sheet;
        $zip->addFile($berkas_sheet, 'xl/worksheets/sheet' . $n . '.xml');
        $override .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        $daftar .= '<sheet name="' . xlsx_teks($nama) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
        $rels .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        $kolom_akhir = xlsx_kolom(max(0, count($s['judul']) - 1));
        $nama_filter .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $i . '" hidden="1">'
            . xlsx_teks("'" . str_replace("'", "''", $nama) . "'") . '!$A$1:$' . $kolom_akhir . '$1</definedName>';
    }
    $id_gaya = count($sheets) + 1;
    $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
        . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
        . '<Default Extension="xml" ContentType="application/xml"/>'
        . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
        . $override
        . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
        . '</Types>');
    $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
        . '</Relationships>');
    $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
        . '<sheets>' . $daftar . '</sheets><definedNames>' . $nama_filter . '</definedNames></workbook>');
    $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
        . $rels
        . '<Relationship Id="rId' . $id_gaya . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
        . '</Relationships>');
    // gaya 0 = biasa; 1 = judul (tebal, putih, latar hijau, rata tengah) seperti export lama; 2 = baris total (tebal)
    $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
        . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
        . '<fonts count="3"><font><sz val="11"/><name val="Calibri"/></font><font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font><font><b/><sz val="11"/><name val="Calibri"/></font></fonts>'
        . '<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill>'
        . '<fill><patternFill patternType="solid"><fgColor rgb="FF2F6B4F"/><bgColor indexed="64"/></patternFill></fill></fills>'
        . '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
        . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
        . '<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>'
        . '<xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
        . '<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"/></cellXfs>'
        . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
        . '</styleSheet>');
    $ok = $zip->close();
    foreach ($sementara as $s) {
        @unlink($s);
    }
    return $ok;
}
