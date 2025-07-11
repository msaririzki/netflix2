document.addEventListener('DOMContentLoaded', function() {

    // Preloader Logic
    const loader = document.getElementById('loader');
    window.addEventListener('load', () => {
        loader.style.opacity = '0';
        setTimeout(() => {
            loader.style.display = 'none';
        }, 500);
    });

    // Animate On Scroll (AOS) Initialization
    AOS.init({
        duration: 800, // Durasi animasi dalam ms
        once: true, // Animasi hanya berjalan sekali
        offset: 50, // Memicu animasi sebelum elemen terlihat
    });

    // Header Shadow on Scroll
    const header = document.querySelector('header');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 50) {
            header.classList.add('scrolled');
        } else {
            header.classList.remove('scrolled');
        }
    });

    // Portfolio Filter Logic
    const filterButtons = document.querySelectorAll('.filter-btn');
    const portfolioItems = document.querySelectorAll('.portfolio-item');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Remove active class from all buttons and add to the clicked one
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            const filter = button.getAttribute('data-filter');

            portfolioItems.forEach(item => {
                // Hide all items first
                item.style.display = 'none';
                // Use a fade-out/fade-in effect for transition
                item.style.animation = 'fadeOut 0.5s';

                if (item.getAttribute('data-category') === filter || filter === 'all') {
                    // Show matched items
                    setTimeout(() => {
                        item.style.display = 'block';
                        item.style.animation = 'fadeIn 0.5s';
                    }, 250);
                }
            });
        });
    });

    // Add keyframes for fade in/out to the head for the portfolio filter
    const styleSheet = document.createElement("style");
    styleSheet.type = "text/css";
    styleSheet.innerText = `
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
        @keyframes fadeOut { from { opacity: 1; transform: scale(1); } to { opacity: 0; transform: scale(0.9); } }
    `;
    document.head.appendChild(styleSheet);

});