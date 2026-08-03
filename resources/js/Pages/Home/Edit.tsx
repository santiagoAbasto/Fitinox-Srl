import { Head, useForm } from '@inertiajs/react';
import { Building2, CheckCircle2, Flame, Footprints, Image as ImageIcon, LayoutTemplate, PackageSearch, PanelTop, Save, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';
import RichTextEditor from '../../Components/RichTextEditor';

const input = 'mt-2 min-h-12 w-full rounded-lg border border-[#dfe4e7] bg-[#fbfcfc] px-4 py-3 text-sm outline-none transition duration-200 hover:border-[#c7d0d5] focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/8';

function Field({ label, value, onChange, textarea = false }: { label: string, value: string, onChange: (value: string) => void, textarea?: boolean }) {
    return <label className="block text-[12px] font-semibold text-[#35363a]">{label}{textarea
        ? <textarea rows={5} className={input} value={value} onChange={e => onChange(e.target.value)}/>
        : <input className={input} value={value} onChange={e => onChange(e.target.value)}/>}</label>;
}

function MediaField({ label, path, file, onChange, error, contain = false, recommendation = '1920 × 840 px' }: { label: string, path: string, file: File | null, onChange: (file: File | null) => void, error?: string, contain?: boolean, recommendation?: string }) {
    const [preview, setPreview] = useState(path ? `/storage/${path.replace(/^\/?storage\//, '')}` : '');
    const [dimensions, setDimensions] = useState('');

    useEffect(() => {
        if (!file) {
            setPreview(path ? `/storage/${path.replace(/^\/?storage\//, '')}` : '');
            return;
        }
        const objectUrl = URL.createObjectURL(file);
        setPreview(objectUrl);
        return () => URL.revokeObjectURL(objectUrl);
    }, [file, path]);

    return <div>
        <div className="mb-2 flex items-center justify-between">
            <span className="text-[12px] font-semibold text-[#35363a]">{label}</span>
            <span className="text-[10px] text-[#7b878e]">{file ? 'Nueva imagen seleccionada' : 'Imagen actual'}</span>
        </div>
        <div className={`group relative overflow-hidden rounded-xl border bg-[#eef2f4] ${error ? 'border-red-400' : 'border-[#dfe4e7]'}`}>
            <div className="aspect-[16/7] w-full">
                {preview
                    ? <img src={preview} alt={`Vista previa de ${label}`} className={`h-full w-full ${contain ? 'object-contain p-8' : 'object-cover'}`} onLoad={event => setDimensions(`${event.currentTarget.naturalWidth} × ${event.currentTarget.naturalHeight} px`)}/>
                    : <div className="grid h-full place-items-center text-[#839098]"><ImageIcon size={28}/></div>}
            </div>
            <div className="absolute inset-x-0 bottom-0 flex items-center justify-between bg-[#132b3b]/92 px-4 py-3 text-white backdrop-blur">
                <div className="min-w-0">
                    <p className="truncate text-[11px] font-medium">{file?.name || path.split('/').pop() || 'Sin imagen'}</p>
                    <p className="mt-0.5 text-[9px] text-white/55">Actual: {dimensions || 'leyendo…'} · JPG, PNG o WebP</p>
                </div>
                <label className="ml-4 inline-flex min-h-11 shrink-0 cursor-pointer items-center gap-2 rounded-lg bg-white px-4 text-[11px] font-semibold text-[#132b3b] transition hover:bg-[#edf3f7] focus-within:ring-2 focus-within:ring-white">
                    <UploadCloud size={15}/> Reemplazar
                    <input type="file" accept="image/png,image/jpeg,image/webp" className="sr-only" onChange={event => onChange(event.target.files?.[0] || null)}/>
                </label>
            </div>
        </div>
        <p className="mt-2 text-[10px] text-[#7b878e]"><strong>Recomendado:</strong> {recommendation} · JPG, PNG o WebP · máximo 8 MB.</p>
        {error && <p className="mt-1 text-[11px] text-red-600">{error}</p>}
    </div>;
}

function Section({ id, title, description, icon: Icon, children }: any) {
    return <section id={id} className="scroll-mt-28 overflow-hidden rounded-xl border border-[#e2e7e9] bg-white shadow-[0_1px_2px_rgba(19,43,59,.025)]">
        <header className="flex items-center gap-4 px-6 pt-6 pb-5">
            <div className="grid size-10 shrink-0 place-items-center rounded-lg bg-[#edf3f7] text-[#132b3b]"><Icon size={19} strokeWidth={1.7}/></div>
            <div className="grow"><h2 className="text-[17px] font-semibold tracking-[-.01em] text-[#132b3b]">{title}</h2><p className="mt-1 text-[12px] leading-5 text-[#738087]">{description}</p></div>
            <CheckCircle2 size={17} className="text-emerald-600" aria-label="Sección editable"/>
        </header>
        <div className="border-t border-[#edf0f2] p-6">{children}</div>
    </section>;
}

export default function Edit({ page, settings }: { page: any, settings: Record<string, string> }) {
    const s = page.sections || {};
    const form = useForm({
        hero_title: page.hero_title || '', hero_image: page.hero_image || 'figma/about.png', hero_image_file: null as File | null, hero_button_label: s.hero_button_label || 'VER PRODUCTOS',
        header_logo: s.header_logo || 'figma/header-footer/header-logo.png', header_logo_file: null as File | null,
        nav_about: s.nav_about || 'Nosotros', nav_products: s.nav_products || 'Productos', nav_downloads: s.nav_downloads || 'Descargas',
        nav_quality: s.nav_quality || 'Calidad', nav_videos: s.nav_videos || 'Videos', nav_distributors: s.nav_distributors || 'Distribuidores',
        nav_quote: s.nav_quote || 'Presupuesto', nav_contact: s.nav_contact || 'Contacto',
        products_title: s.products_title || 'Productos', products_button_label: s.products_button_label || 'VER TODOS', products_button_url: s.products_button_url || '/productos',
        about_title: s.about_title || '¿Quienes somos?', about_image: s.about_image || 'figma/about.png', about_image_file: null as File | null, about_body: page.content || '', about_button_label: s.about_button_label || 'MAS INFO', about_button_url: s.about_button_url || '/nosotros',
        featured_title: s.featured_title || 'Productos destacados',
        industry_title: s.industry_title || 'Oil & Gas', industry_image: s.industry_image || 'figma/oil-gas.png', industry_image_file: null as File | null, industry_text: s.industry_text || '', industry_button_label: s.industry_button_label || 'MAS INFO',
        newsletter_title: s.newsletter_title || 'Suscribite al Newsletter', social_title: s.social_title || 'Seguinos en',
        client_label: s.client_label || 'CLIENTES', copyright: settings.copyright || '© Copyright 2025 Fitinox S.R.L. Todos los derechos reservados',
        address: settings.address || '', email_sales: settings.email_sales || '', email_quality: settings.email_quality || 'calidad@fittinox.com.ar',
        email_warehouse: settings.email_warehouse || 'deposito@fittinox.com.ar', email_admin: settings.email_admin || 'administracion@fittinox.com.ar',
        phone: settings.phone || '', whatsapp: settings.whatsapp || '',
    });
    const set = (key: keyof typeof form.data) => (value: string) => form.setData(key, value);
    return <AdminLayout title="Página de inicio"><Head title="Página de inicio | Fitinox"/>
        <form onSubmit={e => {e.preventDefault(); form.post('/admin/home', {preserveScroll: true, forceFormData: true})}} className="mx-auto max-w-[1180px] space-y-6">
            <div className="flex flex-col justify-between gap-4 px-1 pb-2 sm:flex-row sm:items-end">
                <div>
                    <h2 className="text-[18px] font-semibold text-[#132b3b]">Contenido público</h2>
                    <p className="mt-1 max-w-2xl text-[13px] leading-6 text-[#738087]">Editá los textos y medios de la página principal. Los cambios se publican al guardar.</p>
                </div>
                <div className="flex shrink-0 items-center gap-2 text-[12px] font-medium text-[#526068]">
                    <span className="size-2 rounded-full bg-emerald-500"/><span>Página visible</span>
                </div>
            </div>
            <Section id="header" title="Header" description="Logo, accesos principales y área de clientes." icon={PanelTop}><div className="grid gap-6"><MediaField label="Logo del Header" path={form.data.header_logo} file={form.data.header_logo_file} contain recommendation="520 × 94 px, fondo transparente" onChange={file => form.setData('header_logo_file', file)} error={form.errors.header_logo_file}/><div className="grid gap-5 md:grid-cols-2 xl:grid-cols-3"><Field label="Nosotros" value={form.data.nav_about} onChange={set('nav_about')}/><Field label="Productos" value={form.data.nav_products} onChange={set('nav_products')}/><Field label="Descargas" value={form.data.nav_downloads} onChange={set('nav_downloads')}/><Field label="Calidad" value={form.data.nav_quality} onChange={set('nav_quality')}/><Field label="Videos" value={form.data.nav_videos} onChange={set('nav_videos')}/><Field label="Distribuidores" value={form.data.nav_distributors} onChange={set('nav_distributors')}/><Field label="Presupuesto" value={form.data.nav_quote} onChange={set('nav_quote')}/><Field label="Contacto" value={form.data.nav_contact} onChange={set('nav_contact')}/><Field label="Botón de clientes" value={form.data.client_label} onChange={set('client_label')}/></div></div></Section>
            <Section id="hero" title="Hero principal" description="Imagen, mensaje de apertura y llamada a la acción." icon={ImageIcon}><div className="grid gap-6"><MediaField label="Imagen principal" path={form.data.hero_image} file={form.data.hero_image_file} recommendation="1920 × 840 px (16:7)" onChange={file => form.setData('hero_image_file', file)} error={form.errors.hero_image_file}/><Field label="Título principal" value={form.data.hero_title} onChange={set('hero_title')} textarea/><Field label="Texto del botón" value={form.data.hero_button_label} onChange={set('hero_button_label')}/></div></Section>
            <Section id="productos" title="Productos" description="Encabezado del bloque de categorías. Las tarjetas se administran en Categorías." icon={PackageSearch}><div className="grid gap-5 md:grid-cols-3"><Field label="Título" value={form.data.products_title} onChange={set('products_title')}/><Field label="Texto del botón" value={form.data.products_button_label} onChange={set('products_button_label')}/><Field label="Enlace del botón" value={form.data.products_button_url} onChange={set('products_button_url')}/></div></Section>
            <Section id="nosotros" title="Nosotros" description="Presentación institucional incluida en la Home." icon={Building2}><div className="grid gap-6"><MediaField label="Imagen de la sección" path={form.data.about_image} file={form.data.about_image_file} recommendation="1200 × 1200 px (1:1)" onChange={file => form.setData('about_image_file', file)} error={form.errors.about_image_file}/><div className="grid gap-5 md:grid-cols-3"><Field label="Título" value={form.data.about_title} onChange={set('about_title')}/><Field label="Texto del botón" value={form.data.about_button_label} onChange={set('about_button_label')}/><Field label="Enlace del botón" value={form.data.about_button_url} onChange={set('about_button_url')}/></div><RichTextEditor label="Contenido" value={form.data.about_body} onChange={set('about_body')} error={form.errors.about_body}/></div></Section>
            <Section id="destacados" title="Productos destacados" description="Título de la selección; los productos se marcan desde Catálogo." icon={LayoutTemplate}><Field label="Título" value={form.data.featured_title} onChange={set('featured_title')}/></Section>
            <Section id="oil-gas" title="Oil & Gas" description="Bloque de industria y llamada a la acción." icon={Flame}><div className="grid gap-6"><MediaField label="Imagen de fondo" path={form.data.industry_image} file={form.data.industry_image_file} recommendation="1920 × 460 px (banner panorámico)" onChange={file => form.setData('industry_image_file', file)} error={form.errors.industry_image_file}/><div className="grid gap-5 md:grid-cols-2"><Field label="Título" value={form.data.industry_title} onChange={set('industry_title')}/><Field label="Texto del botón" value={form.data.industry_button_label} onChange={set('industry_button_label')}/></div><RichTextEditor label="Descripción" value={form.data.industry_text} onChange={set('industry_text')} error={form.errors.industry_text}/></div></Section>
            <Section id="footer" title="Footer" description="Newsletter, contacto y texto legal." icon={Footprints}><div className="grid gap-5 md:grid-cols-2"><Field label="Título del newsletter" value={form.data.newsletter_title} onChange={set('newsletter_title')}/><Field label="Título de redes" value={form.data.social_title} onChange={set('social_title')}/><Field label="Dirección" value={form.data.address} onChange={set('address')} textarea/><div className="grid gap-5"><Field label="Email ventas" value={form.data.email_sales} onChange={set('email_sales')}/><Field label="Email calidad" value={form.data.email_quality} onChange={set('email_quality')}/><Field label="Email depósito" value={form.data.email_warehouse} onChange={set('email_warehouse')}/><Field label="Email administración" value={form.data.email_admin} onChange={set('email_admin')}/></div><Field label="Teléfonos" value={form.data.phone} onChange={set('phone')}/><Field label="WhatsApp" value={form.data.whatsapp} onChange={set('whatsapp')}/><div className="md:col-span-2"><Field label="Copyright" value={form.data.copyright} onChange={set('copyright')}/></div></div></Section>
            {Object.keys(form.errors).length > 0 && <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">Revisá los campos indicados antes de guardar.</div>}
            <div className="fixed right-5 bottom-5 left-5 z-20 flex items-center justify-between rounded-xl border border-[#dfe5e8] bg-white/96 px-5 py-3.5 shadow-[0_14px_45px_rgba(19,43,59,.14)] backdrop-blur md:right-8 md:left-8 lg:right-10 lg:left-[312px]">
                <span className="text-[11px] text-[#6e7981]">{form.isDirty ? 'Hay cambios sin guardar' : 'Todo está guardado'}</span>
                <button disabled={form.processing || !form.isDirty} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-[#1954b7] px-5 text-[12px] font-semibold text-white transition duration-200 hover:bg-[#132b3b] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/25 disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{form.processing ? 'Guardando…' : 'Guardar cambios'}</button>
            </div>
        </form>
    </AdminLayout>;
}
