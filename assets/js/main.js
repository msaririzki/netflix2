document.addEventListener('DOMContentLoaded', function () {

    // Inisialisasi AOS (Animate on Scroll) jika library-nya ada
    if (typeof AOS !== 'undefined') {
        AOS.init({
            duration: 800,
            once: true,
        });
    }

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