<!-- BOTTOM NAVIGATION MENU -->
<div class="appBottomMenu" id="onboarding-pegawai-bottom-nav">
    <a href="/home" id="onboarding-pegawai-nav-home" class="item {{ request()->is('home') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="home-outline" role="img" class="md hydrated" aria-label="file tray full outline"></ion-icon>
            <strong>Home</strong>
        </div>
    </a>
    <a href="/absensi/histori" id="onboarding-pegawai-nav-histori" class="item {{ request()->is('absensi/histori') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="document-text-outline" role="img" class="md hydrated"
                aria-label="document text outline"></ion-icon>
            <strong>Histori</strong>
        </div>
    </a>
    <a href="/absensi/selfie" id="onboarding-pegawai-nav-selfie" class="item {{ request()->is('absensi/selfie') ? 'active' : '' }}">
        <div class="col">
            <div class="action-button large">
                <ion-icon name="camera" role="img" class="md hydrated" aria-label="add outline"></ion-icon>
            </div>
        </div>
    </a>
    <a href="/absensi/izin" id="onboarding-pegawai-nav-izin" class="item {{ request()->is('absensi/izin') ? 'active' : '' }}" style="position:relative;">
        <div class="col">
            <ion-icon name="calendar-outline"></ion-icon>
            <strong>Izin</strong>
            <span
                data-offline-queue-badge
                class="badge bg-warning"
                style="position:absolute; top:6px; right:18px; display:none; min-width:22px; text-align:center;"
                title="Menunggu sinkronisasi"
            >0</span>
        </div>
    </a>
    <a href="/profil" id="onboarding-pegawai-nav-profil" class="item {{ request()->is('profil') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="people-outline" role="img" class="md hydrated" aria-label="people outline"></ion-icon>
            <strong>Profil</strong>
        </div>
    </a>
</div>
