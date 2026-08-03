import { Link, usePage } from '@inertiajs/react';
import {
    Boxes,
    ChevronDown,
    ExternalLink,
    FileText,
    FileBadge,
    Flame,
    FolderTree,
    House,
    Images,
    LogOut,
    Menu,
    Package,
    Building2,
    CheckCircle2,
    CircleAlert,
    ShieldCheck,
    CirclePlay,
    MapPinned,
    ClipboardList,
    ShoppingCart,
    Download as DownloadIcon,
    FileSpreadsheet,
    UserRound,
    Settings,
    SearchCheck,
    Link2,
    PanelTop,
    MessageCircle,
    Mail,
    Users,
    X,
    ChartNoAxesCombined,
} from 'lucide-react';
import { ComponentType, PropsWithChildren, useEffect, useMemo, useRef, useState } from 'react';

type NavItem = {
    code: string;
    label: string;
    href: string;
    match?: string;
    icon: ComponentType<{ size?: number; strokeWidth?: number }>;
};

type NavSection = {
    key: string;
    code: string;
    label: string;
    description: string;
    icon: ComponentType<{ size?: number; strokeWidth?: number }>;
    href?: string;
    children?: NavItem[];
};

const sidebarScrollKey = 'fitinox-admin-sidebar-scroll';

const sections: NavSection[] = [
    {
        key: 'analytics',
        code: 'AN',
        label: 'Analítica web',
        description: 'Tráfico, conversiones y seguridad',
        icon: ChartNoAxesCombined,
        children: [
            { code: 'AN1', label: 'Rendimiento web', href: '/admin/analytics', match: '/admin/analytics', icon: ChartNoAxesCombined },
        ],
    },
    {
        key: 'home',
        code: 'B',
        label: 'Inicio',
        description: 'Contenido de la página principal',
        icon: House,
        children: [
            { code: 'BA', label: 'Slider', href: '/admin/home/sliders', match: '/admin/home/sliders', icon: Images },
            { code: 'BB', label: 'Nosotros en Home', href: '/admin/home/about', match: '/admin/home/about', icon: Building2 },
            { code: 'BC', label: 'Oil & Gas', href: '/admin/home/industry', match: '/admin/home/industry', icon: Flame },
        ],
    },
    {
        key: 'about-page',
        code: 'C',
        label: 'Nosotros',
        description: 'Página institucional',
        icon: Building2,
        children: [
            { code: 'DA', label: 'Contenido principal', href: '/admin/nosotros#contenido', match: '/admin/nosotros', icon: FileText },
            { code: 'DB', label: 'Por qué elegirnos', href: '/admin/nosotros#valores', match: '/admin/nosotros', icon: Building2 },
            { code: 'DC', label: 'Video institucional', href: '/admin/nosotros#video', match: '/admin/nosotros', icon: CirclePlay },
        ],
    },
    {
        key: 'catalog',
        code: 'D',
        label: 'Productos',
        description: 'Catálogo, fichas y variantes',
        icon: Boxes,
        children: [
            { code: 'DA', label: 'Categorías', href: '/admin/categories', match: '/admin/categories', icon: FolderTree },
            { code: 'DB', label: 'Productos', href: '/admin/products', match: '/admin/products', icon: Package },
            { code: 'DC', label: 'Importación Excel', href: '/admin/products#importar', match: '/admin/products', icon: FileSpreadsheet },
        ],
    },
    {
        key: 'downloads',
        code: 'E',
        label: 'Descargas',
        description: 'Catálogos y documentación',
        icon: DownloadIcon,
        children: [
            { code: 'EA', label: 'Portada y archivos', href: '/admin/downloads', match: '/admin/downloads', icon: FileSpreadsheet },
        ],
    },
    {
        key: 'quality',
        code: 'F',
        label: 'Calidad',
        description: 'Políticas y certificaciones',
        icon: ShieldCheck,
        children: [
            { code: 'FA', label: 'Contenido y documentos', href: '/admin/quality', match: '/admin/quality', icon: FileBadge },
        ],
    },
    {
        key: 'videos',
        code: 'G',
        label: 'Videos',
        description: 'Galería audiovisual',
        icon: CirclePlay,
        children: [
            { code: 'GA', label: 'Portada y videos', href: '/admin/videos', match: '/admin/videos', icon: CirclePlay },
        ],
    },
    {
        key: 'distributors',
        code: 'H',
        label: 'Distribuidores',
        description: 'Red comercial y mapa',
        icon: MapPinned,
        children: [
            { code: 'HA', label: 'Portada y ubicaciones', href: '/admin/distributors', match: '/admin/distributors', icon: MapPinned },
        ],
    },
    {
        key: 'quotes',
        code: 'I',
        label: 'Presupuesto',
        description: 'Solicitudes comerciales',
        icon: ClipboardList,
        children: [
            { code: 'IA', label: 'Solicitudes recibidas', href: '/admin/quotes', match: '/admin/quotes', icon: ClipboardList },
        ],
    },
    {
        key: 'contact',
        code: 'J',
        label: 'Contacto',
        description: 'Consultas y mensajes',
        icon: MessageCircle,
        children: [
            { code: 'JA', label: 'Mensajes recibidos', href: '/admin/contact', match: '/admin/contact', icon: MessageCircle },
        ],
    },
    {
        key: 'client-zone',
        code: 'K',
        label: 'Zona Clientes',
        description: 'Usuarios, precios y pedidos',
        icon: Users,
        children: [
            { code: 'KA', label: 'Usuarios', href: '/admin/zona-clientes#usuarios', match: '/admin/zona-clientes', icon: Users },
            { code: 'KB', label: 'Productos', href: '/admin/zona-clientes#productos', match: '/admin/zona-clientes', icon: Package },
            { code: 'KC', label: 'Carrito', href: '/admin/zona-clientes#carrito', match: '/admin/zona-clientes', icon: ShoppingCart },
            { code: 'KD', label: 'Lista de precios', href: '/admin/zona-clientes#lista-precios', match: '/admin/zona-clientes', icon: FileSpreadsheet },
            { code: 'KE', label: 'Histórico de pedidos', href: '/admin/zona-clientes#pedidos', match: '/admin/zona-clientes', icon: ClipboardList },
        ],
    },
    {
        key: 'newsletter',
        code: 'L',
        label: 'Newsletter',
        description: 'Suscriptores y campañas',
        icon: Mail,
        children: [
            { code: 'LA', label: 'Campañas y suscriptores', href: '/admin/newsletter', match: '/admin/newsletter', icon: Mail },
        ],
    },
    {
        key: 'settings',
        code: 'M',
        label: 'Configuración',
        description: 'SEO, visibilidad, redes y usuarios',
        icon: Settings,
        children: [
            { code: 'MA', label: 'SEO de la web', href: '/admin/settings#seo', match: '/admin/settings', icon: SearchCheck },
            { code: 'MB', label: 'Visibilidad pública', href: '/admin/settings#visibilidad-publica', match: '/admin/settings', icon: PanelTop },
            { code: 'MC', label: 'WhatsApp flotante', href: '/admin/settings#whatsapp', match: '/admin/settings', icon: MessageCircle },
            { code: 'MD', label: 'Redes sociales', href: '/admin/settings#redes-sociales', match: '/admin/settings', icon: Link2 },
            { code: 'ME', label: 'Usuarios', href: '/admin/settings#users', match: '/admin/settings', icon: Users },
        ],
    },
];

function pathOf(url: string) {
    return url.split('#')[0].split('?')[0].replace(/\/$/, '') || '/';
}

function isActive(href: string, currentUrl: string, match?: string) {
    const target = pathOf(match || href);
    const current = pathOf(currentUrl);
    const targetHash = href.includes('#') ? `#${href.split('#')[1]}` : '';
    const currentHash = currentUrl.includes('#') ? `#${currentUrl.split('#')[1]}` : '';
    if (targetHash) return current === target && (currentHash === targetHash || (!currentHash && targetHash === '#header'));
    return current === target || (target !== '/admin' && current.startsWith(`${target}/`));
}

function SidebarSection({
    section,
    currentUrl,
    open,
    onToggle,
    onNavigate,
}: {
    section: NavSection;
    currentUrl: string;
    open: boolean;
    onToggle: () => void;
    onNavigate: () => void;
}) {
    const active = section.href
        ? isActive(section.href, currentUrl)
        : section.children?.some(item => isActive(item.href, currentUrl, item.match));
    const Icon = section.icon;
    const headerClass = `group relative flex min-h-12 w-full cursor-pointer items-center gap-3 rounded-lg px-3 py-2.5 text-left transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/70 ${
        active
            ? 'bg-white text-[#132b3b] shadow-[0_5px_18px_rgba(0,0,0,.12)]'
            : 'text-white/72 hover:bg-white/8 hover:text-white'
    }`;

    const content = <>
        <span className={`grid size-10 shrink-0 place-items-center rounded-xl border ${active ? 'border-[#dce7ed] bg-[#edf3f7] text-[#132b3b]' : 'border-white/8 bg-white/[.04] text-white/65'}`}>
            <Icon size={17} strokeWidth={1.8}/>
        </span>
        <span className="min-w-0 grow">
            <span className="block text-[13px] font-medium">{section.label}</span>
            <span className={`mt-0.5 block truncate text-[10px] leading-4 ${active ? 'text-[#77828a]' : 'text-white/42'}`}>{section.description}</span>
        </span>
        {section.children && <span className={`grid size-8 shrink-0 place-items-center rounded-lg transition ${active ? 'bg-[#edf3f7] text-[#132b3b]' : 'bg-white/[.04] text-white/55'}`}><ChevronDown size={15} className={`transition-transform duration-200 motion-reduce:transition-none ${open ? 'rotate-180' : ''}`}/></span>}
    </>;

    return <div className="mb-1" data-sidebar-section>
        {section.href
            ? <Link href={section.href} preserveScroll onClick={onNavigate} className={headerClass}>{content}</Link>
            : <button type="button" onClick={onToggle} className={headerClass} aria-expanded={open}>{content}</button>}

        {section.children && <div className={`grid transition-[grid-template-rows] duration-300 ${open ? 'grid-rows-[1fr]' : 'grid-rows-[0fr]'}`}>
            <div className="overflow-hidden">
                <div className="relative my-2 ml-5 space-y-1 border-l border-white/10 pl-4">
                    {section.children.map(item => {
                        const itemActive = isActive(item.href, currentUrl, item.match);
                        const ItemIcon = item.icon;
                        const itemClass = `group flex min-h-12 cursor-pointer items-center gap-3 rounded-xl px-3 text-[12px] transition duration-200 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 ${
                                itemActive
                                    ? 'bg-[#244354] font-medium text-white shadow-[inset_0_0_0_1px_rgba(255,255,255,.08)]'
                                    : 'text-white/55 hover:bg-white/[.06] hover:text-white'
                            }`;
                        const itemContent = <>
                            <span className={`grid size-8 shrink-0 place-items-center rounded-lg ${itemActive ? 'bg-white text-[#132b3b]' : 'bg-white/[.05] text-white/50 group-hover:text-white'}`}><ItemIcon size={15} strokeWidth={1.8}/></span>
                            <span className="min-w-0 grow truncate">{item.label}</span>
                        </>;

                        return item.href.includes('#')
                            ? <a key={item.href} href={item.href} onClick={onNavigate} className={itemClass}>{itemContent}</a>
                            : <Link key={item.href} href={item.href} preserveScroll onClick={onNavigate} className={itemClass}>{itemContent}</Link>;
                    })}
                </div>
            </div>
        </div>}
    </div>;
}

export default function AdminLayout({ title, children }: PropsWithChildren<{ title: string }>) {
    const { url, props } = usePage();
    const user = (props as any).auth.user;
    const success = (props as any).flash?.success;
    const validationErrors = Object.values(((props as any).errors ?? {}) as Record<string, unknown>)
        .filter((message): message is string => typeof message === 'string' && message.trim().length > 0);
    const errorSignature = validationErrors.join('|');
    const firstValidationError = validationErrors[0] || null;
    const [successToast, setSuccessToast] = useState<string | null>(success || null);
    const [errorToast, setErrorToast] = useState<string | null>(firstValidationError);
    const incomingImportReport = (props as any).flash?.import_report;
    const [importReport, setImportReport] = useState<any>(incomingImportReport);
    const navRef = useRef<HTMLElement>(null);
    const [currentLocation, setCurrentLocation] = useState(url);
    const [mobileOpen, setMobileOpen] = useState(false);
    const activeSection = useMemo(() => sections.find(section =>
        section.href
            ? isActive(section.href, currentLocation)
            : section.children?.some(item => isActive(item.href, currentLocation, item.match))
    )?.key || 'panel', [currentLocation]);
    const [openSection, setOpenSection] = useState<string | null>(activeSection);

    useEffect(() => setOpenSection(activeSection), [activeSection]);
    useEffect(() => {
        if (!success) return;
        setSuccessToast(success);
        const timeout = window.setTimeout(() => setSuccessToast(null), 5200);
        return () => window.clearTimeout(timeout);
    }, [success]);
    useEffect(() => {
        if (!errorSignature) {
            setErrorToast(null);
            return;
        }

        setErrorToast(firstValidationError);
        const timeout = window.setTimeout(() => setErrorToast(null), 6200);
        return () => window.clearTimeout(timeout);
    }, [errorSignature, firstValidationError]);
    useEffect(() => {
        setImportReport(incomingImportReport);
        if (!incomingImportReport) return;
        const timeout = window.setTimeout(() => setImportReport(null), 12000);
        return () => window.clearTimeout(timeout);
    }, [incomingImportReport]);
    useEffect(() => {
        const syncLocation = () => setCurrentLocation(`${url.split('#')[0]}${window.location.hash}`);
        syncLocation();
        window.addEventListener('hashchange', syncLocation);
        return () => window.removeEventListener('hashchange', syncLocation);
    }, [url]);

    useEffect(() => {
        const nav = navRef.current;
        if (!nav) return;
        nav.scrollTop = Number(sessionStorage.getItem(sidebarScrollKey) || 0);
        const saveScroll = () => sessionStorage.setItem(sidebarScrollKey, String(nav.scrollTop));
        nav.addEventListener('scroll', saveScroll, { passive: true });
        return () => nav.removeEventListener('scroll', saveScroll);
    }, []);

    const preserveSidebar = () => {
        if (navRef.current) sessionStorage.setItem(sidebarScrollKey, String(navRef.current.scrollTop));
        setMobileOpen(false);
    };

    const logoutToPublicSite = () => {
        // Usamos un submit nativo, no una visita Inertia: así el navegador
        // desmonta por completo el panel antes de seguir el redirect público.
        const token = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content;
        if (!token) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/admin/logout';
        form.style.display = 'none';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = token;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    };

    return <div className="min-h-screen bg-[#f5f7f8] text-[#35363a]">
        <style>{`@keyframes fitinox-toast-progress{from{transform:scaleX(1)}to{transform:scaleX(0)}}@keyframes fitinox-toast-in{from{opacity:0;transform:translateY(-10px) scale(.98)}to{opacity:1;transform:translateY(0) scale(1)}}@media (prefers-reduced-motion:reduce){.fitinox-toast{animation:none!important}.fitinox-toast-progress{animation:none!important}}`}</style>
        {errorToast && <div role="alert" aria-live="assertive" className="fitinox-toast fixed top-5 right-5 z-[90] w-[min(390px,calc(100vw-40px))] overflow-hidden rounded-[20px] border border-[#f3d7d7] bg-white/95 shadow-[0_24px_70px_rgba(82,24,24,.2)] backdrop-blur-2xl [animation:fitinox-toast-in_220ms_cubic-bezier(.2,.8,.2,1)_both]">
            <div className="flex items-start gap-3 p-4.5">
                <span className="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#fff0f0] text-[#c33a3a] shadow-[inset_0_0_0_1px_rgba(195,58,58,.08)]"><CircleAlert size={22} strokeWidth={2}/></span>
                <div className="min-w-0 grow pt-0.5"><p className="text-[12px] font-semibold text-[#132b3b]">Revisá los campos marcados</p><p className="mt-1 text-[11px] leading-[17px] text-[#6d5c60]">{errorToast}</p></div>
                <button type="button" onClick={() => setErrorToast(null)} aria-label="Cerrar notificación" className="grid size-8 shrink-0 place-items-center rounded-full text-[#9d8186] transition duration-200 hover:bg-[#fff0f0] hover:text-[#a92e36] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#c33a3a]/35"><X size={15}/></button>
            </div>
            <div className="h-[3px] bg-[#f8e4e4]"><div className="fitinox-toast-progress h-full origin-left bg-gradient-to-r from-[#ef7676] to-[#c33a3a]" style={{animation:'fitinox-toast-progress 6.2s linear forwards'}}/></div>
        </div>}
        {successToast && <div role="status" aria-live="polite" className="fixed top-5 right-5 z-[80] w-[min(390px,calc(100vw-40px))] overflow-hidden rounded-[20px] border border-white/60 bg-white/90 shadow-[0_24px_70px_rgba(7,19,27,.22)] backdrop-blur-2xl">
            <div className="flex items-start gap-3 p-4.5">
                <span className="grid size-11 shrink-0 place-items-center rounded-[14px] bg-[#e9f8f0] text-[#17845c] shadow-[inset_0_0_0_1px_rgba(23,132,92,.08)]"><CheckCircle2 size={22} strokeWidth={2}/></span>
                <div className="min-w-0 grow pt-0.5"><p className="text-[12px] font-semibold text-[#132b3b]">Cambios guardados</p><p className="mt-1 text-[11px] leading-[17px] text-[#65747c]">{successToast}</p></div>
                <button type="button" onClick={() => setSuccessToast(null)} aria-label="Cerrar notificación" className="grid size-8 shrink-0 place-items-center rounded-full text-[#8b979d] transition hover:bg-[#eef2f4] hover:text-[#132b3b]"><X size={15}/></button>
            </div>
            <div className="h-[3px] bg-[#e8edef]"><div className="h-full origin-left bg-gradient-to-r from-[#34c98b] to-[#1954b7]" style={{animation:'fitinox-toast-progress 5.2s linear forwards'}}/></div>
        </div>}
        {importReport && <div role="status" aria-live="polite" className="fixed top-5 right-5 z-[70] w-[min(390px,calc(100vw-40px))] overflow-hidden rounded-[22px] border border-white/15 bg-[#111820]/95 text-white shadow-[0_24px_80px_rgba(7,19,27,.35)] backdrop-blur-xl">
            <div className="flex items-start gap-3 p-5">
                <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-emerald-400/15 text-emerald-300"><CheckCircle2 size={21}/></span>
                <div className="min-w-0 grow"><p className="text-[13px] font-semibold">Importación completada</p><p className="mt-1 truncate text-[11px] text-white/55">{importReport.file}</p></div>
                <button type="button" onClick={() => setImportReport(null)} aria-label="Cerrar notificación" className="grid size-8 place-items-center rounded-full text-white/45 hover:bg-white/10 hover:text-white"><X size={15}/></button>
            </div>
            <div className="grid grid-cols-3 border-t border-white/10 bg-white/[.035]">
                <div className="px-4 py-3"><strong className="block text-[18px] text-emerald-300">{importReport.created}</strong><span className="text-[9px] text-white/45">CREADOS</span></div>
                <div className="border-x border-white/10 px-4 py-3"><strong className="block text-[18px] text-sky-300">{importReport.updated}</strong><span className="text-[9px] text-white/45">ACTUALIZADOS</span></div>
                <div className="px-4 py-3"><strong className="block text-[18px] text-white/80">{importReport.unchanged}</strong><span className="text-[9px] text-white/45">SIN CAMBIOS</span></div>
            </div>
        </div>}
        {mobileOpen && <button aria-label="Cerrar navegación" onClick={() => setMobileOpen(false)} className="fixed inset-0 z-30 cursor-pointer bg-[#07131b]/50 backdrop-blur-sm lg:hidden"/>}
        <aside className={`fixed inset-y-0 left-0 z-40 flex w-[272px] flex-col bg-[#132b3b] text-white shadow-[16px_0_50px_rgba(7,19,27,.12)] transition-transform duration-200 motion-reduce:transition-none lg:translate-x-0 ${mobileOpen ? 'translate-x-0' : '-translate-x-full'}`}>
            <div className="px-5 pt-6 pb-5">
                <Link href="/admin" className="block">
                    <img src="/storage/figma/header-footer/footer-logo-mask.png" width="202" height="53" className="h-auto w-[202px] object-contain brightness-0 invert" alt="Fitinox S.R.L."/>
                </Link>
                <div className="mt-5 flex items-center justify-between border-t border-white/10 pt-4">
                    <span className="text-[10px] font-medium tracking-[.14em] text-white/45">PANEL ADMINISTRATIVO</span>
                    <button type="button" onClick={() => setMobileOpen(false)} aria-label="Cerrar menú" className="grid size-11 cursor-pointer place-items-center rounded-lg text-white/65 hover:bg-white/10 hover:text-white lg:hidden"><X size={18}/></button>
                </div>
            </div>

            <nav ref={navRef} aria-label="Navegación administrativa" className="grow overflow-y-auto px-3 py-2">
                <div className="px-2 pb-3 text-[10px] font-medium tracking-[.12em] text-white/35">SECCIONES DEL SITIO</div>
                <div>
                    {sections.map(section => <SidebarSection
                        key={section.key}
                        section={section}
                        currentUrl={currentLocation}
                        open={openSection === section.key}
                        onToggle={() => setOpenSection(current => current === section.key ? null : section.key)}
                        onNavigate={preserveSidebar}
                    />)}
                </div>
            </nav>

            <div className="border-t border-white/10 p-4">
                <a href="/" target="_blank" rel="noreferrer" className="mb-4 flex min-h-11 cursor-pointer items-center justify-between rounded-lg bg-white/7 px-3 text-[12px] font-medium text-white/75 transition hover:bg-white/12 hover:text-white">
                    Ver sitio público <ExternalLink size={14}/>
                </a>
                <div className="flex items-center gap-3">
                    <span className="grid size-10 shrink-0 place-items-center rounded-full bg-white/10 text-white"><UserRound size={16}/></span>
                    <div className="min-w-0 grow">
                        <p className="truncate text-[12px] font-medium text-white">{user.name}</p>
                        <p className="truncate text-[10px] text-white/42">{user.email}</p>
                    </div>
                    <button type="button" onClick={logoutToPublicSite} aria-label="Cerrar sesión" title="Cerrar sesión" className="grid size-11 cursor-pointer place-items-center rounded-lg text-white/50 transition hover:bg-white/10 hover:text-white">
                        <LogOut size={15}/>
                    </button>
                </div>
            </div>
        </aside>

        <main id="admin-content" className="min-h-screen lg:ml-[272px]">
            <header className="sticky top-0 z-20 flex min-h-[88px] items-center justify-between border-b border-[#e3e7e9] bg-white/95 px-5 backdrop-blur md:px-8 lg:px-10">
                <button type="button" onClick={() => setMobileOpen(true)} aria-label="Abrir menú" className="mr-4 grid size-11 cursor-pointer place-items-center rounded-lg border border-[#e1e6e8] text-[#132b3b] lg:hidden"><Menu size={19}/></button>
                <div>
                    <p className="mb-1 text-[12px] font-medium text-[#77838b]">Contenido del sitio</p>
                    <h1 className="text-[25px] leading-tight font-semibold tracking-[-.025em] text-[#132b3b]">{title}</h1>
                </div>
                <div className="hidden items-center gap-2 rounded-full bg-[#eef8f3] px-3 py-2 text-[11px] font-medium text-[#176b4a] sm:flex">
                    <span className="size-1.5 rounded-full bg-emerald-500"/><span>Sincronizado</span>
                </div>
            </header>
            <div className="p-5 pb-28 md:p-8 md:pb-28 lg:p-10 lg:pb-28">{children}</div>
        </main>
    </div>;
}
