import './bootstrap';

const adminSide = document.getElementById('adminSide');
const sideToggle = document.getElementById('sideToggle');
if (adminSide && sideToggle) {
    const closeMenu = () => {
        adminSide.classList.remove('open');
        sideToggle.setAttribute('aria-expanded', 'false');
    };
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && adminSide.classList.contains('open')) {
            closeMenu();
            sideToggle.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!adminSide.contains(event.target) && !sideToggle.contains(event.target)) closeMenu();
    });
}

document.querySelectorAll('[data-image-preview]').forEach((input) => {
    const image = input.parentElement.querySelector('[data-selected-preview]');
    let url;
    input.addEventListener('change', () => {
        if (url) URL.revokeObjectURL(url);
        const file = input.files[0];
        image.hidden = !file;
        if (file) {
            url = URL.createObjectURL(file);
            image.src = url;
        } else image.removeAttribute('src');
    });
    window.addEventListener('pagehide', () => { if (url) URL.revokeObjectURL(url); });
});

const settingsTabs = document.querySelector('.settings-tabs');
if (settingsTabs) {
    const links = Array.from(settingsTabs.querySelectorAll('a'));
    const panels = Array.from(document.querySelectorAll('[data-settings-panel]'));
    settingsTabs.setAttribute('role', 'tablist');
    const activate = (id) => {
        panels.forEach((panel) => { panel.hidden = panel.dataset.settingsPanel !== id; });
        links.forEach((link) => {
            const active = link.hash === `#${id}`;
            link.setAttribute('aria-selected', String(active));
            link.tabIndex = active ? 0 : -1;
            link.style.fontWeight = active ? '700' : '400';
        });
    };
    links.forEach((link, index) => {
        link.setAttribute('role', 'tab');
        link.id = `tab-${link.hash.slice(1)}`;
        link.addEventListener('click', (event) => { event.preventDefault(); activate(link.hash.slice(1)); });
        link.addEventListener('keydown', (event) => {
            let target;
            if (event.key === 'ArrowRight') target = (index + 1) % links.length;
            if (event.key === 'ArrowLeft') target = (index + links.length - 1) % links.length;
            if (event.key === 'Home') target = 0;
            if (event.key === 'End') target = links.length - 1;
            if (target !== undefined) { event.preventDefault(); links[target].click(); links[target].focus(); }
        });
    });
    panels.forEach((panel) => {
        panel.setAttribute('role', 'tabpanel');
        panel.setAttribute('aria-labelledby', `tab-${panel.dataset.settingsPanel}`);
    });
    const errorPanel = document.querySelector('.settings-wrap .err')?.closest('[data-settings-panel]');
    activate(errorPanel?.dataset.settingsPanel || 'settings-general');
    document.querySelector('.settings-wrap').addEventListener('invalid', (event) => {
        const panel = event.target.closest('[data-settings-panel]');
        if (panel) activate(panel.dataset.settingsPanel);
    }, true);
}

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
const revealSelectors = '.movement-copy, .movement-visual, .intro-grid > div, .rayon-documentation, .movement-section-head, .editorial-card, .movement-event-card, .movement-bureau, .community-panel, .movement-contact .wrap, .bph-card, .structure-role, .structure-bureau, .structure-page .person, .karya-card, .keg-card';
const revealMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
const io = 'IntersectionObserver' in window ? new IntersectionObserver(
    (entries) => {
        entries.forEach((e) => {
            if (e.isIntersecting) {
                e.target.classList.add('in');
                io.unobserve(e.target);
            }
        });
    },
    { threshold: 0.08 }
) : null;
const initializeReveals = () => {
    if (!document.body.classList.contains('public-body')) return;
    document.querySelectorAll(revealSelectors).forEach((el) => el.classList.add('reveal'));
    document.querySelectorAll('.reveal').forEach((el) => {
        if (el.dataset.revealReady) return;
        el.dataset.revealReady = 'true';
        if (!io || revealMotion.matches) {
            el.classList.add('in');
            return;
        }
        const siblings = Array.from(el.parentElement.children).filter((child) => child.classList.contains('reveal'));
        el.style.setProperty('--reveal-delay', `${Math.min(siblings.indexOf(el) % 4, 3) * 70}ms`);
        el.classList.add('reveal-ready');
        io.observe(el);
    });
};
initializeReveals();
document.addEventListener('livewire:navigated', initializeReveals);
document.addEventListener('livewire:init', () => {
    window.Livewire.hook('morphed', initializeReveals);
});
revealMotion.addEventListener('change', (event) => {
    if (event.matches) document.querySelectorAll('.reveal').forEach((el) => {
        el.classList.add('in');
        io?.unobserve(el);
    });
});

// Mobile menu toggle
const menuBtn = document.querySelector('.menu-btn');
const mobileNav = document.getElementById('mobile-nav');
if (menuBtn && mobileNav) {
    const setMenuOpen = (open) => {
        mobileNav.classList.toggle('open', open);
        menuBtn.setAttribute('aria-expanded', String(open));
        menuBtn.setAttribute('aria-label', open ? 'Tutup menu navigasi' : 'Buka menu navigasi');
    };
    menuBtn.addEventListener('click', () => setMenuOpen(!mobileNav.classList.contains('open')));
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && mobileNav.classList.contains('open')) {
            setMenuOpen(false);
            menuBtn.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!mobileNav.contains(event.target) && !menuBtn.contains(event.target)) setMenuOpen(false);
    });
    window.matchMedia('(min-width: 961px)').addEventListener('change', (event) => {
        if (event.matches) setMenuOpen(false);
    });
}

// Simple accordion (kepengurusan per-biro)
document.querySelectorAll('[data-accordion]').forEach((head) => {
    head.addEventListener('click', () => {
        const item = head.closest('.acc-item');
        item?.classList.toggle('open');
    });
});
