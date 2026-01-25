<!-- BOTTOM NAVIGATION MENU -->
<div class="appBottomMenu">
    <a href="/home" class="item {{ request()->is('home') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="home-outline" role="img" class="md hydrated" aria-label="file tray full outline"></ion-icon>
            <strong>Home</strong>
        </div>
    </a>
    <a href="/absensi/histori" class="item {{ request()->is('absensi/histori') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="document-text-outline" role="img" class="md hydrated"
                aria-label="document text outline"></ion-icon>
            <strong>Histori</strong>
        </div>
    </a>
    <a href="/absensi/selfie" class="item {{ request()->is('absensi/selfie') ? 'active' : '' }}">
        <div class="col">
            <div class="action-button large">
                <ion-icon name="camera" role="img" class="md hydrated" aria-label="add outline"></ion-icon>
            </div>
        </div>
    </a>
    <a href="/absensi/izin" class="item {{ request()->is('absensi/izin') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="calendar-outline"></ion-icon>
            <strong>Izin</strong>
        </div>
    </a>
    <a href="/profil" class="item {{ request()->is('profil') ? 'active' : '' }}">
        <div class="col">
            <ion-icon name="people-outline" role="img" class="md hydrated" aria-label="people outline"></ion-icon>
            <strong>Profil</strong>
        </div>
    </a>
</div>
