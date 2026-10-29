/*
 * Menu samping dari data TREE_ITEMS (tree_items.js, format lama Tigra Tree Menu):
 *   [judul, link_atau_0, anak1, anak2, ...]
 * Link dibuka di frame "frameset" seperti sebelumnya.
 * Posisi menu yang sedang dibuka ditampilkan sebagai breadcrumb di bar atas.
 */
(function () {
    'use strict';

    var LAYAR_KECIL = window.matchMedia('(max-width: 991.98px)');
    var KUNCI_CIUT = 'ni-menu-ciut';
    var pohon = document.getElementById('menu-pohon');
    var jejak = document.getElementById('jejak-menu');
    var tombol = document.getElementById('tombol-menu');
    var tirai = document.getElementById('menu-tirai');
    var cari = document.getElementById('cari-menu');
    var frame = document.getElementById('frameset');
    var nomor = 0;
    var semuaLink = [];

    function judulBersih(t) {
        return String(t).replace(/\s+/g, ' ').trim();
    }

    /** Bangun <ul> untuk daftar node; induk = jalur judul dari akar. */
    function bangun(daftar, induk, kedalaman) {
        var ul = document.createElement('ul');
        daftar.forEach(function (node) {
            if (!Array.isArray(node) || node.length === 0) {
                return;
            }
            var judul = judulBersih(node[0]);
            var link = node[1];
            var anak = node.slice(2).filter(Array.isArray);
            var jalur = induk.concat([judul]);
            var li = document.createElement('li');

            if (anak.length && kedalaman === 0 && link && link !== 0 && link !== '0') {
                // akar seperti ['Home','welcome.php', ...]: Home jadi butir biasa, anaknya naik ke tingkat atas
                ul.appendChild(butirLink(judul, link, jalur));
                Array.prototype.slice.call(bangun(anak, jalur, kedalaman).children).forEach(function (x) {
                    ul.appendChild(x);
                });
                return;
            }
            if (anak.length) {
                var id = 'menu-grup-' + (++nomor);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.setAttribute('aria-expanded', 'false');
                btn.setAttribute('aria-controls', id);
                btn.innerHTML = '<span class="teks"></span>';
                btn.firstChild.textContent = judul;
                li.appendChild(btn);

                var sub = bangun(anak, jalur, kedalaman + 1);
                sub.id = id;
                sub.hidden = true;
                // judul grup yang juga punya link (mis. 'Home') jadi butir pertama grup
                if (link && link !== 0 && link !== '0') {
                    sub.insertBefore(butirLink(judul, link, jalur), sub.firstChild);
                }
                li.appendChild(sub);
                btn.addEventListener('click', function () {
                    var buka = btn.getAttribute('aria-expanded') !== 'true';
                    btn.setAttribute('aria-expanded', buka ? 'true' : 'false');
                    sub.hidden = !buka;
                });
            } else if (link && link !== 0 && link !== '0') {
                li = butirLink(judul, link, jalur);
            } else {
                return;
            }
            ul.appendChild(li);
        });
        return ul;
    }

    function butirLink(judul, link, jalur) {
        var li = document.createElement('li');
        var a = document.createElement('a');
        a.href = link;
        a.target = 'frameset';
        a.innerHTML = '<span class="titik"></span><span class="teks"></span>';
        a.lastChild.textContent = judul;
        a.dataset.jalur = JSON.stringify(jalur);
        a.addEventListener('click', function () {
            tandaiAktif(a);
            if (LAYAR_KECIL.matches) {
                aturMenu(false);
            }
        });
        li.appendChild(a);
        semuaLink.push(a);
        return li;
    }

    function tandaiAktif(a) {
        semuaLink.forEach(function (x) {
            x.classList.toggle('aktif', x === a);
        });
        tampilkanJejak(a ? JSON.parse(a.dataset.jalur) : ['Home']);
    }

    function tampilkanJejak(jalur) {
        jejak.innerHTML = '';
        jalur.forEach(function (judul, i) {
            var li = document.createElement('li');
            li.className = 'breadcrumb-item' + (i === jalur.length - 1 ? ' active' : '');
            li.textContent = judul;
            if (i === jalur.length - 1) {
                li.setAttribute('aria-current', 'page');
            }
            jejak.appendChild(li);
        });
        document.title = jalur[jalur.length - 1] + ' · KSPPS Nur Insani';
    }

    /** Buka/tutup menu. Layar lebar: ciutkan (diingat); layar kecil: laci. */
    function aturMenu(buka) {
        if (LAYAR_KECIL.matches) {
            document.body.classList.toggle('menu-terbuka', buka);
            tirai.hidden = !buka;
        } else {
            document.body.classList.toggle('menu-ciut', !buka);
            document.body.classList.remove('menu-terbuka');
            tirai.hidden = true;
            try { localStorage.setItem(KUNCI_CIUT, buka ? '0' : '1'); } catch (e) { /* abaikan */ }
        }
    }

    function menuTerbuka() {
        return LAYAR_KECIL.matches
            ? document.body.classList.contains('menu-terbuka')
            : !document.body.classList.contains('menu-ciut');
    }

    /** Cocokkan link yang sedang dibuka di frame dengan butir menu (untuk breadcrumb). */
    function sinkronDariFrame() {
        var lokasi;
        try {
            lokasi = frame.contentWindow.location.href;
        } catch (e) {
            return;
        }
        var cocok = null;
        semuaLink.forEach(function (a) {
            if (!cocok && a.href.split('#')[0] === lokasi.split('#')[0]) {
                cocok = a;
            }
        });
        if (!cocok) {
            var tanpaQuery = lokasi.split('?')[0];
            semuaLink.forEach(function (a) {
                if (!cocok && a.href.split('?')[0] === tanpaQuery) {
                    cocok = a;
                }
            });
        }
        if (cocok) {
            tandaiAktif(cocok);
            bukaInduk(cocok);
        }
    }

    function bukaInduk(el) {
        var ul = el.closest('ul');
        while (ul && ul !== pohon) {
            if (ul.hidden) {
                ul.hidden = false;
                var btn = ul.previousElementSibling;
                if (btn) {
                    btn.setAttribute('aria-expanded', 'true');
                }
            }
            ul = ul.parentElement.closest('ul');
        }
    }

    function saringMenu() {
        var kata = cari.value.trim().toLowerCase();
        pohon.querySelectorAll('li').forEach(function (li) {
            li.classList.remove('tidak-cocok');
        });
        pohon.querySelectorAll('mark').forEach(function (m) {
            m.replaceWith(document.createTextNode(m.textContent));
        });
        pohon.querySelectorAll('.teks').forEach(function (t) {
            t.normalize();
        });
        var kosong = pohon.querySelector('.menu-kosong');
        if (kosong) {
            kosong.remove();
        }
        if (!kata) {
            return;
        }
        var ada = 0;
        semuaLink.forEach(function (a) {
            var li = a.parentElement;
            var teks = a.querySelector('.teks');
            var jalur = JSON.parse(a.dataset.jalur).join(' ').toLowerCase();
            if (jalur.indexOf(kata) === -1) {
                li.classList.add('tidak-cocok');
                return;
            }
            ada++;
            bukaInduk(a);
            var posisi = teks.textContent.toLowerCase().indexOf(kata);
            if (posisi > -1) {
                var isi = teks.textContent;
                teks.textContent = '';
                teks.appendChild(document.createTextNode(isi.slice(0, posisi)));
                var m = document.createElement('mark');
                m.textContent = isi.slice(posisi, posisi + kata.length);
                teks.appendChild(m);
                teks.appendChild(document.createTextNode(isi.slice(posisi + kata.length)));
            }
        });
        // sembunyikan grup yang tidak punya link cocok
        pohon.querySelectorAll('li').forEach(function (li) {
            if (li.querySelector('ul')) {
                var terlihat = Array.prototype.some.call(li.querySelectorAll('a'), function (a) {
                    return !a.parentElement.classList.contains('tidak-cocok');
                });
                li.classList.toggle('tidak-cocok', !terlihat);
            }
        });
        if (!ada) {
            var p = document.createElement('div');
            p.className = 'menu-kosong';
            p.textContent = 'Menu tidak ditemukan.';
            pohon.appendChild(p);
        }
    }

    // ---- mulai ----
    var data = typeof TREE_ITEMS !== 'undefined' ? TREE_ITEMS : [];
    pohon.appendChild(bangun(data, [], 0));

    try {
        if (localStorage.getItem(KUNCI_CIUT) === '1' && !LAYAR_KECIL.matches) {
            document.body.classList.add('menu-ciut');
        }
    } catch (e) { /* abaikan */ }

    tombol.addEventListener('click', function () {
        aturMenu(!menuTerbuka());
    });
    tirai.addEventListener('click', function () {
        aturMenu(false);
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && LAYAR_KECIL.matches && menuTerbuka()) {
            aturMenu(false);
        }
    });
    LAYAR_KECIL.addEventListener('change', function () {
        document.body.classList.remove('menu-terbuka');
        tirai.hidden = true;
    });
    cari.addEventListener('input', saringMenu);
    frame.addEventListener('load', sinkronDariFrame);
    document.querySelectorAll('[data-menu-beranda]').forEach(function (a) {
        a.addEventListener('click', function () {
            tandaiAktif(null);
        });
    });
})();
