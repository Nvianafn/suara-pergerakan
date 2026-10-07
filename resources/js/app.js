import './bootstrap';

document.querySelectorAll('[data-gallery-upload]').forEach((input) => {
    const captions = input.parentElement.querySelector('[data-gallery-captions]');
    let previews = [];
    const clearPreviews = () => {
        previews.forEach((url) => URL.revokeObjectURL(url));
        previews = [];
    };
    input.addEventListener('change', () => {
        clearPreviews();
        captions.replaceChildren();
        Array.from(input.files).forEach((file, index) => {
            const row = document.createElement('div');
            row.className = 'field';
            const label = document.createElement('label');
            label.htmlFor = `gallery-caption-${index}`;
            label.textContent = `Keterangan foto ${index + 1}: ${file.name}`;
            const caption = document.createElement('input');
            caption.id = label.htmlFor;
            caption.name = `caption[${index}]`;
            caption.maxLength = 255;
            caption.className = 'input';
            caption.placeholder = 'Keterangan foto (opsional)';
            row.append(label);
            if (['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
                const image = document.createElement('img');
                const url = URL.createObjectURL(file);
                previews.push(url);
                image.src = url;
                image.alt = `Preview foto ${index + 1}`;
                image.className = 'img-preview';
                row.append(image);
            }
            row.append(caption);
            captions.append(row);
        });
    });
    window.addEventListener('pagehide', clearPreviews);
});

if (document.querySelector('[data-rich-text]')) {
    import('./rich-text').then(({ initializeEditors }) => initializeEditors());
}

// Sticky navbar shadow on scroll
const hdr = document.getElementById('hdr');
if (hdr) {
    const onScroll = () => hdr.classList.toggle('scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
}

// Reveal-on-scroll animation
const io = new IntersectionObserver(
    (entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                io.unobserve(e.target);
            }
        });
    },
    { threshold: 0.1 }
);
document.querySelectorAll('.reveal').forEach((el) => io.observe(el));

// Mobile menu toggle
const menuBtn = document.querySelector('.menu-btn');
const mobileNav = document.getElementById('mobile-nav');
if (menuBtn && mobileNav) {
    menuBtn.addEventListener('click', () => mobileNav.classList.toggle('open'));
}

// Simple accordion (kepengurusan per-biro)
document.querySelectorAll('[data-accordion]').forEach((head) => {
    head.addEventListener('click', () => {
        const item = head.closest('.acc-item');
        item?.classList.toggle('open');
    });
});
