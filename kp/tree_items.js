var TREE_ITEMS = [
   
    ['Home','welcome.php',
       
            ['Transaksi ',0,
	    ['Simpanan', 'view/master_trans.php'],

             ['Jurnal Keluar', './transaksi/kas_keluar.php'],
            ['Jurnal Masuk', './transaksi/kas_masuk.php'],
            ['jurnal Umum ', './transaksi/umum.php'],
            ['Posting jurnal ', './transaksi/posting.php'],
	    ['Posting Deposito ', './transaksi/posting_dep.php'],

	    ['Setor/Penarikan ', './view/view_stor.php'],
          ['List Jurnal ', './laporan/list_gl.php'],
	 ['Upload Jurnal Toko ', './laporan/list_jurnal_toko.php'],
           
        ],

       
        ['Monitoring ',0,
            ['Daily Deposito', 'view/view_bayar_dep.php'],
            ['Approval', 'view/view_approval.php'],
         ],
         ['Report ',0,
        ['Nomonative ',0,
        ['Daily Disbursement', '../view_disburse.php'],
             ['Disbursement', '../disburse.php'],
            ['Nominative Pembiayaan', '../report/Kr_kp.php'],
            ['Nominative Tabungan', '../report/Kr.php'],
            ['PPAP ', '../report/ppap.php'],
	    
        ],

           
            ['Akuntasi ',0,
                ['Neraca ',0,
                ['Neraca ', './laporan/new_laporan_neraca.php'],

            ['TB', './laporan/new_neraca.php'],
            ['Rugi-Laba', './laporan/new_laba.php'],
                ['Neraca Unit', './laporan/neraca_unit.php'],
['Buku Besar KP', './laporan/buku_besar.php'],
['Buku Besar Unit', './laporan/buku_besar_unit.php'],
['Buku Besar Konsol', './laporan/buku_besar_konsol.php'],

         ],
                ['Konsolidasi ',0,
		['Upload Jateng', 'upload_jateng.php'],
['Upload Gajip', 'upload_gaji.php'],
                ['Konsolidasi', './laporan/konsolidasi.php'],


        ],
       
                ['Setup Parameter ',0,
                ['Tambah Coa', './setup/perkiraan.php'],
        ],
         ],
        ],
       ['Koreksi','view_koreksi.php'],
['Proses Konsolidasi','./laporan/proses_konsol.php'],
	['Posting Ulang','posting_neraca.php'],
 	['Hitung PPAP','./laporan/proses_ppap_konsol.php'],


        ['EOM','./laporan/eom.php'],
 	['Hitung SHU','./laporan/hitung_shu.php'],
            ['Ganti Password','Gantipass.php'],
['Proses Konsolidasi Harian','./laporan/proses_konsol_hari.php'],

    ]
];
