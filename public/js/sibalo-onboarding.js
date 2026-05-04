/**
 * SIBALO onboarding (Driver.js). Globals: window.driver.js.driver, window.SibaloOnboarding
 */
(function () {
    'use strict';

    function storageKey(role, version) {
        return 'sibalo_onboarding_' + role + '_v' + version;
    }

    function getConfig() {
        var el = document.getElementById('sibalo-onboarding-config');
        if (!el) {
            return { role: 'pegawai', version: '1' };
        }
        return {
            role: el.getAttribute('data-role') || 'pegawai',
            version: el.getAttribute('data-version') || '1',
        };
    }

    function isDone(role, version) {
        try {
            return localStorage.getItem(storageKey(role, version)) === '1';
        } catch (e) {
            return false;
        }
    }

    function markDone(role, version) {
        try {
            localStorage.setItem(storageKey(role, version), '1');
        } catch (e) {
            /* ignore */
        }
    }

    function wantsForceTutorial() {
        var q = new URLSearchParams(window.location.search);
        return q.get('tutorial') === '1' || q.get('onboarding') === '1';
    }

    function filterSteps(steps) {
        return steps.filter(function (step) {
            var el = step.element;
            if (!el) {
                return true;
            }
            if (typeof el === 'string') {
                return !!document.querySelector(el);
            }
            return el instanceof Element;
        });
    }

    function getDriverFactory() {
        if (!window.driver || !window.driver.js || typeof window.driver.js.driver !== 'function') {
            console.warn('SibaloOnboarding: driver.js not loaded');
            return null;
        }
        return window.driver.js.driver;
    }

    function commonPopover() {
        return {
            side: 'bottom',
            align: 'start',
        };
    }

    function pegawaiSteps() {
        var p = commonPopover();
        return [
            {
                element: '#user-section',
                popover: Object.assign(
                    {
                        title: 'Selamat datang',
                        description:
                            'Ringkasan ini membantu Anda memahami menu absensi, izin/sakit, histori, dan profil. Gunakan tombol di bawah untuk lanjut.',
                    },
                    p
                ),
            },
            {
                element: '#presence-section',
                popover: Object.assign(
                    {
                        title: 'Absensi hari ini',
                        description:
                            'Di sini terlihat jam masuk dan pulang hari ini. Absen melalui ikon kamera di menu bawah.',
                    },
                    p
                ),
            },
            {
                element: '#menu-section',
                popover: Object.assign(
                    {
                        title: 'Menu cepat',
                        description:
                            'Akses Profil, Izin/Sakit, dan Histori absensi dari kartu ini.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-pegawai-bottom-nav',
                popover: Object.assign(
                    {
                        title: 'Menu bawah',
                        description:
                            'Dari sini Anda pindah ke Beranda, Histori, kamera absensi, Izin/Sakit, dan Profil.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-pegawai-nav-selfie',
                popover: Object.assign(
                    {
                        title: 'Absen masuk & pulang',
                        description:
                            'Buka kamera, pastikan lokasi dalam radius, lalu ambil foto. Setelah absen masuk, tombol akan berubah menjadi absen pulang.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-pegawai-nav-histori',
                popover: Object.assign(
                    {
                        title: 'Histori absensi',
                        description: 'Lihat riwayat absensi Anda per bulan.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-pegawai-nav-izin',
                popover: Object.assign(
                    {
                        title: 'Izin & sakit',
                        description:
                            'Ajukan izin atau sakit di sini. Saat offline, pengajuan bisa mengantri dan terkirim otomatis saat online.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-pegawai-nav-profil',
                popover: Object.assign(
                    {
                        title: 'Profil',
                        description:
                            'Ubah data pribadi seperti nomor HP, kata sandi, atau foto profil.',
                    },
                    p
                ),
            },
        ];
    }

    function adminSteps() {
        var p = commonPopover();
        return [
            {
                element: '#onboarding-admin-dashboard-intro',
                popover: Object.assign(
                    {
                        title: 'Panel admin',
                        description:
                            'Dari sini Anda mengelola pegawai, memantau absensi, menyetujui izin/sakit, laporan, dan konfigurasi sistem.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-home',
                popover: Object.assign(
                    {
                        title: 'Beranda',
                        description: 'Kembali ke ringkasan dashboard.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-master',
                popover: Object.assign(
                    {
                        title: 'Data master',
                        description:
                            'Kelola data pegawai dan admin: tambah, ubah, hapus, serta reset kata sandi.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-monitoring',
                popover: Object.assign(
                    {
                        title: 'Monitoring absensi',
                        description: 'Pantau kehadiran pegawai dan data absensi harian.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-anomali',
                popover: Object.assign(
                    {
                        title: 'Audit anomali',
                        description: 'Tinjau indikasi ketidakwajaran pada data absensi.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-izin',
                popover: Object.assign(
                    {
                        title: 'Persetujuan izin/sakit',
                        description: 'Setujui atau tolak pengajuan izin dan sakit dari pegawai.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-laporan',
                popover: Object.assign(
                    {
                        title: 'Laporan & rekap',
                        description:
                            'Cetak atau unduh laporan absensi dan rekap kehadiran pegawai.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-lokasi',
                popover: Object.assign(
                    {
                        title: 'Konfigurasi lokasi',
                        description:
                            'Atur titik lokasi sekolah/kantor dan radius absensi di peta.',
                    },
                    p
                ),
            },
            {
                element: '#onboarding-admin-nav-jam',
                popover: Object.assign(
                    {
                        title: 'Konfigurasi jam absensi',
                        description:
                            'Atur jam mulai absen masuk dan jam mulai absen pulang yang berlaku untuk pegawai.',
                    },
                    p
                ),
            },
        ];
    }

    function runTour(role, version) {
        var factory = getDriverFactory();
        if (!factory) {
            return;
        }
        var steps = filterSteps(role === 'admin' ? adminSteps() : pegawaiSteps());
        if (!steps.length) {
            console.warn('SibaloOnboarding: no steps for this page');
            return;
        }
        var driver = factory({
            showProgress: true,
            animate: true,
            nextBtnText: 'Berikutnya →',
            prevBtnText: '← Kembali',
            doneBtnText: 'Selesai',
            steps: steps,
            onDestroyed: function () {
                markDone(role, version);
            },
        });
        driver.drive();
    }

    function maybeAutoStart() {
        var cfg = getConfig();
        var path = window.location.pathname.replace(/\/$/, '') || '/';
        if (cfg.role === 'pegawai') {
            if (path !== '/home') {
                return;
            }
        } else if (cfg.role === 'admin') {
            if (path !== '/dashboard') {
                return;
            }
        } else {
            return;
        }
        if (!wantsForceTutorial() && isDone(cfg.role, cfg.version)) {
            return;
        }
        runTour(cfg.role, cfg.version);
    }

    function wireLaunchButton() {
        var btn = document.getElementById('sibalo-onboarding-launch');
        if (!btn) {
            return;
        }
        btn.addEventListener('click', function () {
            var cfg = getConfig();
            runTour(cfg.role, cfg.version);
        });
    }

    function init() {
        wireLaunchButton();
        maybeAutoStart();
    }

    window.SibaloOnboarding = {
        start: function (role) {
            var cfg = getConfig();
            var r = role || cfg.role || 'pegawai';
            runTour(r, cfg.version);
        },
        reset: function (which) {
            var cfg = getConfig();
            var v = cfg.version;
            try {
                if (!which || which === 'pegawai') {
                    localStorage.removeItem(storageKey('pegawai', v));
                }
                if (!which || which === 'admin') {
                    localStorage.removeItem(storageKey('admin', v));
                }
            } catch (e) {
                /* ignore */
            }
        },
        isDone: function (role) {
            var cfg = getConfig();
            return isDone(role || cfg.role, cfg.version);
        },
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
