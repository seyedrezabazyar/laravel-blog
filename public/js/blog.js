document.addEventListener('DOMContentLoaded', () => {
    if ('IntersectionObserver' in window) {
        const observer = new IntersectionObserver(entries => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                    }
                    img.classList.remove('lazyload');
                    observer.unobserve(img);
                }
            });
        });
        document.querySelectorAll('img.lazyload').forEach(img => observer.observe(img));
    }
    document.querySelectorAll('img').forEach(img => {
        img.addEventListener('error', () => {
            img.src = '/images/default-book.png';
        });
    });
});
