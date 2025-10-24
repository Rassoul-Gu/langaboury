
// Animation parallaxe sur l'image
document.addEventListener('mousemove', (e) => {
    if (window.innerWidth > 968) {
        const heroImage = document.querySelector('.hero-image');
        const x = (e.clientX / window.innerWidth - 0.5) * 30;
        const y = (e.clientY / window.innerHeight - 0.5) * 30;
        heroImage.style.transform = `translate(${x}px, ${y}px) rotate(${x/5}deg)`;
    }
});

// Effet au survol des boutons
document.querySelectorAll('.btn').forEach(btn => {
    btn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-3px) scale(1.02)';
    });
    btn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Animation des statistiques au chargement
window.addEventListener('load', () => {
    const stats = document.querySelectorAll('.stat-number');
    stats.forEach((stat, index) => {
        setTimeout(() => {
            stat.style.animation = 'pulse 1s ease';
        }, index * 200);
    });
});
