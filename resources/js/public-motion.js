import { animate, inView, stagger } from 'motion';
import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import posthog from 'posthog-js';

const posthogKey = document.querySelector('meta[name="posthog-key"]')?.content;
if (posthogKey && !window.location.pathname.startsWith('/admin') && !window.location.pathname.startsWith('/clientes')) {
    posthog.init(posthogKey, {
        api_host: document.querySelector('meta[name="posthog-host"]')?.content || 'https://us.i.posthog.com',
        autocapture: { dom_event_allowlist: ['click'], element_allowlist: ['a','button'] },
        capture_pageview: false,
        disable_session_recording: document.querySelector('meta[name="posthog-recordings"]')?.content !== '1',
        mask_all_text: true,
        mask_all_element_attributes: true,
        session_recording: { maskAllInputs: true },
    });
}

const analyticsEnabled = document.querySelector('meta[name="analytics-enabled"]')?.content === '1';
const analyticsQueue = [];
const analyticsId = () => crypto.randomUUID();
const getAnonymousId = (key) => {
    let value = sessionStorage.getItem(key);
    if (!value) { value = analyticsId(); sessionStorage.setItem(key, value); }
    return value;
};
const analyticsSession = getAnonymousId('fitinox.analytics.session');
let analyticsVisitor = localStorage.getItem('fitinox.analytics.visitor');
if (!analyticsVisitor) { analyticsVisitor = analyticsId(); localStorage.setItem('fitinox.analytics.visitor', analyticsVisitor); }
const capture = (name, properties = {}, path = window.location.pathname) => {
    if (!analyticsEnabled) return;
    analyticsQueue.push({ id: analyticsId(), name, path, timestamp: new Date().toISOString(), properties });
    if (posthogKey) posthog.capture(name, { ...properties, path });
};
const flushAnalytics = () => {
    if (!analyticsQueue.length) return;
    const events = analyticsQueue.splice(0, 20);
    fetch('/analytics/events', { method:'POST', credentials:'same-origin', keepalive:true, headers:{
        'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]')?.content || '',
    }, body:JSON.stringify({ session_id:analyticsSession, visitor_id:analyticsVisitor, events }) }).catch(() => analyticsQueue.unshift(...events));
};

capture('page_view', { title: document.title, referrer_path: document.referrer ? new URL(document.referrer).pathname : null });
window.setInterval(flushAnalytics, 5000);
window.addEventListener('pagehide', flushAnalytics);

const viewedSections = new Set(JSON.parse(sessionStorage.getItem('fitinox.analytics.sections') || '[]'));
let previousSection = null;
document.querySelectorAll('[data-analytics-section], main > section, footer').forEach((section, index) => {
    const key = section.dataset.analyticsSection || section.id || `section-${index + 1}`;
    if (!section.dataset.analyticsSection) section.dataset.analyticsSection = key;
    let timer; let visibleSince = 0; let maxVisibility = 0;
    const observer = new IntersectionObserver(([entry]) => {
        maxVisibility = Math.max(maxVisibility, Math.round(entry.intersectionRatio * 100));
        if (entry.intersectionRatio >= .5 && !viewedSections.has(key)) {
            visibleSince = performance.now();
            timer = window.setTimeout(() => {
                viewedSections.add(key); sessionStorage.setItem('fitinox.analytics.sections', JSON.stringify([...viewedSections]));
                capture('section_view', { section_key:key, section_order:index + 1, visible_ms:Math.round(performance.now()-visibleSince), max_visibility:maxVisibility, previous_section:previousSection });
                previousSection = key;
            }, 1500);
        } else if (entry.intersectionRatio < .5) window.clearTimeout(timer);
    }, { threshold:[0,.25,.5,.75,1] });
    observer.observe(section);
});

const reachedDepths = new Set();
window.addEventListener('scroll', () => {
    const max = document.documentElement.scrollHeight - innerHeight;
    const depth = max > 0 ? Math.round(scrollY / max * 100) : 100;
    [25,50,75,90,100].forEach(mark => { if (depth >= mark && !reachedDepths.has(mark)) { reachedDepths.add(mark); capture('scroll_depth',{scroll_depth:mark}); } });
}, { passive:true });

document.addEventListener('click', (event) => {
    const target = event.target.closest('a,button'); if (!target) return;
    const href = target.getAttribute('href') || '';
    const name = href.includes('wa.me') ? 'whatsapp_click' : href.startsWith('tel:') ? 'phone_click' : href.startsWith('mailto:') ? 'email_click' : 'cta_click';
    capture(name, { target: href.slice(0, 300), label: (target.textContent || target.getAttribute('aria-label') || '').trim().slice(0, 120) });
});

const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
const isEnglish = document.documentElement.lang.toLowerCase().startsWith('en');
const uiText = isEnglish ? {
    consult: (product, code, quantity) => `Enquiry for ${product} · Code ${code} · Quantity ${quantity}`,
    products: 'FITINOX PRODUCTS',
    searchError: "We couldn't search right now. Please try again in a few seconds.",
    directions: 'GET DIRECTIONS',
    officialDistributor: 'OFFICIAL DISTRIBUTOR',
    browserLocation: 'Your browser cannot get your location.',
    locating: 'Finding your location…',
    noLocations: 'No distributors with available coordinates.',
    yourLocation: 'Your location',
    nearest: (name, distance) => `Nearest: ${name} · approximately ${distance}.`,
    locationPermission: 'We need your location permission to suggest the nearest distributor.',
    locationError: "We couldn't determine your location. Check your GPS and try again.",
} : {
    consult: (product, code, quantity) => `Consulta por ${product} · Código ${code} · Cantidad ${quantity}`,
    products: 'PRODUCTOS FITINOX',
    searchError: 'No pudimos buscar ahora. Intentá nuevamente en unos segundos.',
    directions: 'CÓMO LLEGAR',
    officialDistributor: 'DISTRIBUIDOR OFICIAL',
    browserLocation: 'Tu navegador no permite obtener la ubicación.',
    locating: 'Buscando tu ubicación…',
    noLocations: 'No hay distribuidores con coordenadas disponibles.',
    yourLocation: 'Tu ubicación',
    nearest: (name, distance) => `Más cercano: ${name} · aproximadamente ${distance}.`,
    locationPermission: 'Necesitamos permiso de ubicación para sugerirte el distribuidor más cercano.',
    locationError: 'No pudimos detectar tu ubicación. Revisá el GPS o intentá nuevamente.',
};

document.querySelectorAll('[data-mobile-nav-root]').forEach((root) => {
    if (root.dataset.mobileNavInitialized === 'true') return;
    root.dataset.mobileNavInitialized = 'true';

    const trigger = root.querySelector('[data-mobile-nav-trigger]');
    const panel = root.querySelector('[data-mobile-nav]');
    const overlay = root.querySelector('[data-mobile-nav-overlay]');
    const closeButton = root.querySelector('[data-mobile-nav-close]');
    const desktopMedia = window.matchMedia('(min-width: 1224px)');
    const menuLabels = isEnglish ? { open: 'Open menu', close: 'Close menu' } : { open: 'Abrir menú', close: 'Cerrar menú' };
    let returnFocus = null;

    if (!trigger || !panel || !overlay || !closeButton) return;

    const focusableElements = () => [...panel.querySelectorAll('a[href], button:not([disabled]), [tabindex]:not([tabindex="-1"])')]
        .filter((element) => !element.hasAttribute('hidden'));
    const setOpen = (open, restoreFocus = true) => {
        root.classList.toggle('is-open', open);
        document.body.classList.toggle('mobile-nav-open', open);
        trigger.setAttribute('aria-expanded', String(open));
        trigger.setAttribute('aria-label', open ? menuLabels.close : menuLabels.open);
        panel.setAttribute('aria-hidden', String(!open));

        if (open) {
            returnFocus = document.activeElement instanceof HTMLElement ? document.activeElement : trigger;
            window.requestAnimationFrame(() => closeButton.focus());
        } else if (restoreFocus && returnFocus instanceof HTMLElement) {
            returnFocus.focus();
        }
    };

    trigger.addEventListener('click', () => setOpen(!root.classList.contains('is-open')));
    closeButton.addEventListener('click', () => setOpen(false));
    overlay.addEventListener('click', () => setOpen(false));
    panel.querySelectorAll('a[href], [data-catalog-search-trigger], [data-client-login-trigger]').forEach((element) => {
        element.addEventListener('click', () => setOpen(false));
    });
    document.addEventListener('keydown', (event) => {
        if (!root.classList.contains('is-open')) return;
        if (event.key === 'Escape') {
            event.preventDefault();
            setOpen(false);
            return;
        }
        if (event.key !== 'Tab') return;
        const focusable = focusableElements();
        const first = focusable[0];
        const last = focusable.at(-1);
        if (!first || !last) return;
        if (event.shiftKey && document.activeElement === first) {
            event.preventDefault();
            last.focus();
        } else if (!event.shiftKey && document.activeElement === last) {
            event.preventDefault();
            first.focus();
        }
    });
    desktopMedia.addEventListener('change', (event) => {
        if (event.matches) setOpen(false, false);
    });
});

if (!reduceMotion) {
    const header = document.querySelector('[data-public-header]');
    const heroContents = document.querySelectorAll('[data-hero-content]');

    if (header) {
        animate(header, { opacity: [0, 1], y: [-10, 0] }, {
            duration: 0.55,
            ease: [0.22, 1, 0.36, 1],
        });

        let elevated = false;
        const updateHeader = () => {
            const next = window.scrollY > 16;
            if (next === elevated) return;
            elevated = next;
            header.classList.toggle('public-header--scrolled', next);
        };

        updateHeader();
        window.addEventListener('scroll', updateHeader, { passive: true });
    }

    heroContents.forEach((content) => {
        animate(content.children, {
            opacity: [0, 1],
            y: [28, 0],
        }, {
            delay: stagger(0.1, { startDelay: 0.15 }),
            duration: 0.75,
            ease: [0.22, 1, 0.36, 1],
        });
    });

    document.querySelectorAll('[data-reveal]').forEach((section) => {
        const items = section.querySelectorAll('[data-reveal-item]');

        inView(section, () => {
            animate(section, {
                opacity: [0, 1],
                y: [34, 0],
            }, {
                duration: 0.7,
                ease: [0.22, 1, 0.36, 1],
            });

            if (items.length) {
                animate(items, {
                    opacity: [0, 1],
                    y: [24, 0],
                    scale: [0.985, 1],
                }, {
                    delay: stagger(0.08, { startDelay: 0.1 }),
                    duration: 0.62,
                    ease: [0.22, 1, 0.36, 1],
                });
            }
        }, { margin: '0px 0px -12% 0px', amount: 0.12 });
    });
}

document.querySelectorAll('[data-locale-menu]').forEach((menu) => {
    const trigger = menu.querySelector('[data-locale-menu-trigger]');
    const choices = menu.querySelectorAll('[data-locale-choice]');
    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        trigger?.setAttribute('aria-expanded', String(open));
    };

    trigger?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        document.querySelectorAll('[data-locale-menu]').forEach((otherMenu) => {
            if (otherMenu !== menu) otherMenu.classList.remove('is-open');
        });
        setOpen(!menu.classList.contains('is-open'));
    });

    choices.forEach((choice) => {
        choice.addEventListener('click', (event) => {
            if (choice.getAttribute('aria-current') === 'true' || reduceMotion || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            event.preventDefault();
            menu.classList.add('is-switching');
            setOpen(false);
            document.querySelector('[data-public-header]')?.classList.add('locale-switching');
            window.setTimeout(() => window.location.assign(choice.href), 220);
        });
    });

    document.addEventListener('click', (event) => {
        if (!(event.target instanceof Node) || !menu.contains(event.target)) setOpen(false);
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') setOpen(false);
    });
});

document.querySelectorAll('[data-consult-button]').forEach((button) => {
    button.addEventListener('click', () => {
        const input = document.querySelector(`[data-consult-quantity="${button.dataset.quantityId}"]`);
        const quantity = input?.value || '1';
        const message = uiText.consult(button.dataset.product, button.dataset.code, quantity);
        window.open(`https://wa.me/5491140732016?text=${encodeURIComponent(message)}`, '_blank', 'noopener,noreferrer');
    });
});

const catalogSearchDialog = document.querySelector('[data-catalog-search-dialog]');

if (catalogSearchDialog) {
    const searchInput = catalogSearchDialog.querySelector('[data-catalog-search-input]');
    const results = catalogSearchDialog.querySelector('[data-catalog-search-results]');
    const response = catalogSearchDialog.querySelector('[data-catalog-search-response]');
    const emptyState = catalogSearchDialog.querySelector('[data-catalog-search-empty]');
    const spinner = catalogSearchDialog.querySelector('[data-catalog-search-spinner]');
    const endpoint = catalogSearchDialog.dataset.searchUrl;
    const searchMode = catalogSearchDialog.dataset.searchMode || 'suggest';
    let requestController = null;
    let searchTimer = null;

    const resetSearch = () => {
        requestController?.abort();
        window.clearTimeout(searchTimer);
        searchInput.value = '';
        results.replaceChildren();
        response.textContent = '';
        response.classList.add('hidden');
        emptyState.classList.remove('hidden');
        spinner.classList.add('hidden');
    };

    const openSearch = () => {
        if (!catalogSearchDialog.open) catalogSearchDialog.showModal();
        window.setTimeout(() => searchInput.focus(), 80);
    };

    const productCard = (product) => {
        const link = document.createElement('a');
        link.href = product.url;
        link.setAttribute('role', 'option');
        link.className = 'group flex min-h-[84px] items-center gap-4 rounded-[15px] border border-[#e0e7eb] bg-white p-3 transition duration-200 hover:border-[#1954b7] hover:bg-[#f4f8fc] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/20';

        const imageWrap = document.createElement('span');
        imageWrap.className = 'grid size-[58px] shrink-0 place-items-center overflow-hidden rounded-[11px] border border-[#edf0f2] bg-white';
        const image = document.createElement('img');
        image.src = product.image;
        image.alt = '';
        image.className = 'h-full w-full object-contain p-1.5';
        imageWrap.append(image);

        const text = document.createElement('span');
        text.className = 'min-w-0 grow';
        const category = document.createElement('span');
        category.className = 'block truncate text-[10px] font-semibold tracking-[.08em] text-[#1954b7]';
        category.textContent = product.category || uiText.products;
        const name = document.createElement('strong');
        name.className = 'mt-1 block truncate text-[14px] font-semibold text-[#132b3b]';
        name.textContent = product.name;
        const match = document.createElement('span');
        match.className = 'mt-1 block truncate text-[11px] text-[#65747c]';
        match.textContent = product.match;
        text.append(category, name, match);

        const arrow = document.createElement('span');
        arrow.className = 'grid size-9 shrink-0 place-items-center rounded-full text-[#1954b7] transition group-hover:bg-white';
        arrow.innerHTML = '<svg class="size-5" viewBox="0 0 24 24" fill="none" aria-hidden="true"><path d="m9 18 6-6-6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        link.append(imageWrap, text, arrow);
        return link;
    };

    const runSearch = async () => {
        if (searchMode === 'redirect') return;
        const query = searchInput.value.trim();
        if (query.length < 2) {
            results.replaceChildren();
            response.classList.add('hidden');
            emptyState.classList.remove('hidden');
            spinner.classList.add('hidden');
            return;
        }

        requestController?.abort();
        requestController = new AbortController();
        spinner.classList.remove('hidden');
        emptyState.classList.add('hidden');

        try {
            const request = new URL(endpoint, window.location.origin);
            request.searchParams.set('q', query);
            const result = await fetch(request, {
                headers: { Accept: 'application/json' },
                signal: requestController.signal,
            });
            if (!result.ok) throw new Error('search-failed');
            const payload = await result.json();
            results.replaceChildren(...(payload.results || []).map(productCard));
            response.textContent = payload.answer;
            response.classList.remove('hidden');
        } catch (error) {
            if (error.name === 'AbortError') return;
            results.replaceChildren();
            response.textContent = uiText.searchError;
            response.classList.remove('hidden');
        } finally {
            spinner.classList.add('hidden');
        }
    };

    document.querySelectorAll('[data-catalog-search-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', openSearch);
    });
    catalogSearchDialog.querySelectorAll('[data-catalog-search-close]').forEach((button) => {
        button.addEventListener('click', () => catalogSearchDialog.close());
    });
    catalogSearchDialog.addEventListener('close', resetSearch);
    searchInput.addEventListener('input', () => {
        window.clearTimeout(searchTimer);
        searchTimer = window.setTimeout(runSearch, 220);
    });
    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') return;
        const firstResult = results.querySelector('a');
        if (!firstResult) return;
        event.preventDefault();
        firstResult.click();
    });
    document.addEventListener('keydown', (event) => {
        if ((event.metaKey || event.ctrlKey) && event.key.toLowerCase() === 'k') {
            event.preventDefault();
            openSearch();
        }
    });
}

const clientLoginDialog = document.querySelector('[data-client-login-dialog]');

if (clientLoginDialog) {
    const openClientLogin = () => clientLoginDialog.showModal();
    document.querySelectorAll('[data-client-login-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', openClientLogin);
    });
    clientLoginDialog.querySelectorAll('[data-client-login-close]').forEach((button) => {
        button.addEventListener('click', () => clientLoginDialog.close());
    });
    clientLoginDialog.addEventListener('click', (event) => {
        if (event.target === clientLoginDialog) clientLoginDialog.close();
    });

    const clientRegisterDialog = document.querySelector('[data-client-register-dialog]');
    document.querySelectorAll('[data-client-register-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            clientLoginDialog.close();
            clientRegisterDialog?.showModal();
        });
    });
    clientRegisterDialog?.querySelectorAll('[data-client-register-close]').forEach((button) => {
        button.addEventListener('click', () => clientRegisterDialog.close());
    });
    clientRegisterDialog?.addEventListener('click', (event) => {
        if (event.target === clientRegisterDialog) clientRegisterDialog.close();
    });
}

const distributorMapElement = document.getElementById('distributor-map');
const contactMapElement = document.getElementById('contact-map');

const escapeMapHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, (character) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#039;', '"': '&quot;',
}[character]));

if (contactMapElement && window.FitinoxContactLocation) {
    const location = window.FitinoxContactLocation;
    const latitude = Number(location.latitude);
    const longitude = Number(location.longitude);

    if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
        const map = L.map(contactMapElement, {
            center: [latitude, longitude],
            zoom: 15,
            zoomControl: true,
            scrollWheelZoom: true,
            attributionControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap',
        }).addTo(map);

        const icon = L.icon({
            iconUrl: '/storage/figma/distributors/pin.svg',
            iconSize: [37, 48],
            iconAnchor: [18.5, 48],
            popupAnchor: [0, -45],
        });
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${latitude},${longitude}`;
        const marker = L.marker([latitude, longitude], { icon, title: location.name || 'Fitinox S.R.L.' })
            .addTo(map)
            .bindPopup(`<div class="fitinox-map-popup"><p class="fitinox-map-eyebrow">FITINOX S.R.L.</p><h3>${escapeMapHtml(location.name || 'Fitinox S.R.L.')}</h3><p>${escapeMapHtml(location.address)}</p><a href="${mapsUrl}" target="_blank" rel="noreferrer" class="fitinox-map-directions">${uiText.directions}</a></div>`, { maxWidth: 310, minWidth: 260, closeButton: true });

        marker.openPopup();
        window.addEventListener('resize', () => map.invalidateSize(), { passive: true });
        window.setTimeout(() => map.invalidateSize(), 120);
    }
}

if (distributorMapElement && window.FitinoxDistributorLocations) {
    const locations = window.FitinoxDistributorLocations.filter(
        (location) => location.latitude !== null && location.longitude !== null,
    );
    const map = L.map(distributorMapElement, {
        center: [-34.92, -60.8],
        zoom: 6,
        zoomControl: true,
        scrollWheelZoom: true,
        attributionControl: true,
    });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    const icon = L.icon({
        iconUrl: '/storage/figma/distributors/pin.svg',
        iconSize: [37, 48],
        iconAnchor: [18.5, 48],
        popupAnchor: [0, -45],
    });
    const markers = new Map();
    let userMarker = null;

    const popup = (location) => {
        const phone = location.phone
            ? `<a href="tel:${location.phone.split('\n')[0].replace(/[^0-9+]/g, '')}" class="fitinox-map-contact">${location.phone.replace(/\n/g, '<br>')}</a>`
            : '';
        const email = location.email
            ? `<a href="mailto:${location.email}" class="fitinox-map-contact">${location.email}</a>`
            : '';
        const mapsUrl = `https://www.google.com/maps/search/?api=1&query=${location.latitude},${location.longitude}`;
        return `<div class="fitinox-map-popup">
            <p class="fitinox-map-eyebrow">${uiText.officialDistributor}</p>
            <h3>${location.name}</h3>
            <p>${location.address}</p>
            ${phone}${email}
            <a href="${mapsUrl}" target="_blank" rel="noreferrer" class="fitinox-map-directions">${uiText.directions}</a>
        </div>`;
    };

    locations.forEach((location) => {
        const marker = L.marker([location.latitude, location.longitude], { icon, title: location.name })
            .addTo(map)
            .bindPopup(popup(location), { maxWidth: 310, minWidth: 260, closeButton: true });
        markers.set(Number(location.id), marker);
    });

    if (locations.length) {
        const bounds = L.latLngBounds(locations.map((location) => [location.latitude, location.longitude]));
        map.fitBounds(bounds, { padding: [65, 65], maxZoom: 7 });
    }

    window.addEventListener('fitinox:select-distributor', (event) => {
        const marker = markers.get(Number(event.detail.id));
        if (!marker) return;
        const target = marker.getLatLng();
        map.flyTo(target, Math.max(map.getZoom(), 12), { duration: 0.9 });
        window.setTimeout(() => marker.openPopup(), 700);
    });

    const locationButton = document.getElementById('use-my-location');
    const locationStatus = document.getElementById('nearest-location-status');
    const radians = (degrees) => degrees * (Math.PI / 180);
    const distanceInKm = (fromLat, fromLng, toLat, toLng) => {
        const earthRadius = 6371;
        const deltaLat = radians(toLat - fromLat);
        const deltaLng = radians(toLng - fromLng);
        const value = Math.sin(deltaLat / 2) ** 2
            + Math.cos(radians(fromLat)) * Math.cos(radians(toLat)) * Math.sin(deltaLng / 2) ** 2;
        return earthRadius * 2 * Math.atan2(Math.sqrt(value), Math.sqrt(1 - value));
    };
    const showLocationStatus = (message, error = false) => {
        locationStatus.textContent = message;
        locationStatus.classList.remove('hidden', 'text-red-600', 'text-[#52636c]');
        locationStatus.classList.add(error ? 'text-red-600' : 'text-[#52636c]');
    };

    locationButton?.addEventListener('click', () => {
        if (!navigator.geolocation) {
            showLocationStatus(uiText.browserLocation, true);
            return;
        }
        locationButton.disabled = true;
        locationButton.classList.add('opacity-60');
        showLocationStatus(uiText.locating);
        navigator.geolocation.getCurrentPosition(({ coords }) => {
            const nearest = locations
                .map((location) => ({
                    ...location,
                    distance: distanceInKm(coords.latitude, coords.longitude, location.latitude, location.longitude),
                }))
                .sort((a, b) => a.distance - b.distance)[0];

            if (!nearest) {
                showLocationStatus(uiText.noLocations, true);
                locationButton.disabled = false;
                locationButton.classList.remove('opacity-60');
                return;
            }

            if (userMarker) userMarker.remove();
            userMarker = L.circleMarker([coords.latitude, coords.longitude], {
                radius: 8,
                color: '#ffffff',
                weight: 3,
                fillColor: '#1954b7',
                fillOpacity: 1,
            }).addTo(map).bindTooltip(uiText.yourLocation, { permanent: false, direction: 'top' });

            const nearestMarker = markers.get(Number(nearest.id));
            const bounds = L.latLngBounds([
                [coords.latitude, coords.longitude],
                [nearest.latitude, nearest.longitude],
            ]);
            map.flyToBounds(bounds, { padding: [110, 110], maxZoom: 12, duration: 1 });
            window.setTimeout(() => nearestMarker?.openPopup(), 900);

            const row = document.querySelector(`[data-select-distributor="${nearest.id}"]`)?.closest('[data-distributor]');
            document.querySelectorAll('[data-distributor]').forEach((item) => {
                item.classList.remove('bg-[#edf3f7]', 'border-l-4', 'border-l-[#1954b7]');
            });
            if (row) {
                row.classList.remove('hidden');
                row.classList.add('bg-[#edf3f7]', 'border-l-4', 'border-l-[#1954b7]');
                const list = document.getElementById('distributor-list');
                list?.scrollTo({
                    top: row.offsetTop - (list.clientHeight / 2) + (row.offsetHeight / 2),
                    behavior: 'smooth',
                });
            }

            const distance = nearest.distance < 10
                ? `${nearest.distance.toFixed(1)} km`
                : `${Math.round(nearest.distance)} km`;
            showLocationStatus(uiText.nearest(nearest.name, distance));
            locationButton.disabled = false;
            locationButton.classList.remove('opacity-60');
        }, (error) => {
            const message = error.code === 1
                ? uiText.locationPermission
                : uiText.locationError;
            showLocationStatus(message, true);
            locationButton.disabled = false;
            locationButton.classList.remove('opacity-60');
        }, {
            enableHighAccuracy: true,
            timeout: 12000,
            maximumAge: 300000,
        });
    });

    window.addEventListener('resize', () => map.invalidateSize(), { passive: true });
    window.setTimeout(() => map.invalidateSize(), 120);
}

const floatingWhatsapp = document.querySelector('[data-floating-whatsapp]');
const copyrightBoundary = document.querySelector('[data-footer-copyright]');

if (floatingWhatsapp && copyrightBoundary) {
    let currentLift = 0;
    let frameRequested = false;

    const positionFloatingWhatsapp = () => {
        frameRequested = false;
        const buttonRect = floatingWhatsapp.getBoundingClientRect();
        const boundaryTop = copyrightBoundary.getBoundingClientRect().top;
        const baseBottom = buttonRect.bottom - currentLift;
        const safetyGap = window.innerWidth >= 1224 ? 20 : 16;
        currentLift = Math.min(0, boundaryTop - safetyGap - baseBottom);
        floatingWhatsapp.style.transform = `translate3d(0, ${currentLift}px, 0)`;
    };

    const requestWhatsappPosition = () => {
        if (frameRequested) return;
        frameRequested = true;
        window.requestAnimationFrame(positionFloatingWhatsapp);
    };

    window.addEventListener('scroll', requestWhatsappPosition, { passive: true });
    window.addEventListener('resize', requestWhatsappPosition, { passive: true });
    positionFloatingWhatsapp();
}
