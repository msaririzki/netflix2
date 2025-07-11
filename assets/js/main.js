document.addEventListener('DOMContentLoaded', function () {
    
    // Inisialisasi AOS (Animate on Scroll)
    AOS.init({
        duration: 800, // Durasi animasi
        once: true,    // Animasi hanya berjalan sekali
    });

    // Logika untuk mengubah background navbar saat scroll
    const navbar = document.getElementById('mainNavbar');
    if (navbar) {
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                navbar.classList.add('navbar-scrolled');
            } else {
                navbar.classList.remove('navbar-scrolled');
            }
        });
    }

});


// Inisialisasi Plyr.io Video Player
// Cek apakah elemen dengan id 'player' ada di halaman
const player = document.getElementById('player');
if (player) {
    new Plyr(player);
}