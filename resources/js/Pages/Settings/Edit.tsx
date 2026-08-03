import { Head, router, useForm } from '@inertiajs/react';
import { Eye, Image, KeyRound, Link2, MessageCircle, PanelTop, Pencil, Plus, Save, SearchCheck, ShieldCheck, UploadCloud, Users } from 'lucide-react';
import AdminLayout from '../../Components/AdminLayout';
import { useRef, useState } from 'react';

const field = 'mt-2 min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';

function Section({ id, title, description, icon: Icon, children }: any) {
    return <section id={id} className="scroll-mt-28 overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white shadow-[0_8px_30px_rgba(19,43,59,.04)]"><header className="flex items-center gap-4 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-11 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><Icon size={20}/></span><div><h2 className="text-[15px] font-semibold text-[#132b3b]">{title}</h2><p className="mt-1 text-[11px] text-[#758189]">{description}</p></div></header><div className="p-6">{children}</div></section>;
}

function Switch({ checked, onChange, label, description }: any) {
    return <label className="flex min-h-[68px] cursor-pointer items-center justify-between gap-4 rounded-xl border border-[#e5eaed] bg-[#f8fafb] px-4"><span><strong className="block text-[12px] text-[#263b45]">{label}</strong><small className="mt-1 block text-[10px] text-[#78858c]">{description}</small></span><span className={`relative h-6 w-11 shrink-0 rounded-full ${checked?'bg-[#1954b7]':'bg-[#c9d1d5]'}`}><input type="checkbox" checked={checked} onChange={event=>onChange(event.target.checked)} className="sr-only"/><span className={`absolute top-1 size-4 rounded-full bg-white transition-transform ${checked?'translate-x-6':'translate-x-1'}`}/></span></label>;
}

function PageSeoCard({ page, fallbackImageUrl }: any) {
    const [open, setOpen] = useState(false);
    const imageInputRef = useRef<HTMLInputElement | null>(null);
    const [imagePreview, setImagePreview] = useState<string | null>(null);
    const form = useForm<any>({
        seo_title: page.seoTitle || '',
        seo_description: page.seoDescription || '',
        seo_keywords: page.seoKeywords || '',
        seo_og_image_file: null,
        remove_seo_og_image: false,
    });
    const hasTitle = form.data.seo_title.trim().length > 0;
    const hasDescription = form.data.seo_description.trim().length > 0;
    const hasKeywords = form.data.seo_keywords.trim().length > 0;
    const hasPageImage = Boolean(imagePreview || (!form.data.remove_seo_og_image && page.ogImageUrl));
    const isReady = hasTitle && hasDescription && hasKeywords;
    const previewImage = imagePreview || (!form.data.remove_seo_og_image ? page.ogImageUrl : null) || fallbackImageUrl;

    const selectImage = (file: File | null) => {
        if (!file) return;
        if (imagePreview) URL.revokeObjectURL(imagePreview);
        setImagePreview(URL.createObjectURL(file));
        form.setData('seo_og_image_file', file);
        form.setData('remove_seo_og_image', false);
    };

    const clearImage = () => {
        if (imagePreview) URL.revokeObjectURL(imagePreview);
        setImagePreview(null);
        form.setData('seo_og_image_file', null);
        form.setData('remove_seo_og_image', true);
        if (imageInputRef.current) imageInputRef.current.value = '';
    };

    return <article className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white transition-shadow hover:shadow-[0_10px_26px_rgba(19,43,59,.06)]">
        <div className="flex min-h-[72px] items-center gap-3 px-5 transition hover:bg-[#f8fafb]">
        <button type="button" onClick={()=>setOpen(value=>!value)} aria-expanded={open} className="flex min-w-0 flex-1 items-center gap-3 text-left">
            <span className={`size-2 shrink-0 rounded-full ${isReady ? 'bg-emerald-500' : 'bg-amber-400'}`}/>
            <span className="min-w-0 flex-1"><span className="flex items-center gap-2"><strong className="truncate text-[13px] text-[#132b3b]">{page.label}</strong><small className={`rounded-full px-2 py-0.5 text-[9px] font-semibold ${isReady ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700'}`}>{isReady ? 'LISTO' : 'PENDIENTE'}</small></span><small className="mt-1 block truncate text-[10px] text-[#758189]">{hasTitle ? form.data.seo_title : 'Todavía no tiene título SEO propio'}</small></span>
            <span className="grid size-9 shrink-0 place-items-center rounded-lg bg-[#edf3f7] text-[#1954b7]">{open ? '−' : '+'}</span>
        </button><a href={page.publicUrl} target="_blank" rel="noreferrer" className="hidden min-h-9 shrink-0 items-center rounded-lg border border-[#dfe5e8] px-3 text-[10px] font-semibold text-[#1954b7] transition hover:bg-white md:inline-flex">VER PÁGINA</a></div>
        {open && <form onSubmit={event=>{event.preventDefault(); form.post(`/admin/settings/pages/${page.id}/seo`, {forceFormData:true, preserveScroll:true, onSuccess:()=>setOpen(false)});}} className="border-t border-[#edf0f2] bg-[#fbfcfd] p-5">
            <div className="grid gap-5 xl:grid-cols-[minmax(0,1fr)_300px]">
                <div className="grid gap-5 md:grid-cols-2">
                    <label className="text-[12px] font-semibold text-[#263b45]">Título de {page.label}<input className={field} maxLength={70} value={form.data.seo_title} onChange={event=>form.setData('seo_title',event.target.value)} placeholder={`${page.label} | Fitinox`}/><span className="mt-1.5 flex justify-between text-[10px] font-normal text-[#7b878e]"><span>Recomendado: 50–60 caracteres.</span><span className={form.data.seo_title.length > 60 ? 'text-amber-600' : ''}>{form.data.seo_title.length}/70</span></span>{form.errors.seo_title && <span className="mt-1 block text-[10px] font-normal text-red-600">{form.errors.seo_title}</span>}</label>
                    <label className="text-[12px] font-semibold text-[#263b45]">Palabras clave<input className={field} maxLength={255} value={form.data.seo_keywords} onChange={event=>form.setData('seo_keywords',event.target.value)} placeholder="industria, producto, Fitinox"/><span className="mt-1.5 flex justify-between text-[10px] font-normal text-[#7b878e]"><span>Separalas por comas.</span><span>{form.data.seo_keywords.length}/255</span></span>{form.errors.seo_keywords && <span className="mt-1 block text-[10px] font-normal text-red-600">{form.errors.seo_keywords}</span>}</label>
                    <label className="md:col-span-2 text-[12px] font-semibold text-[#263b45]">Descripción de {page.label}<textarea rows={4} className={`${field} py-3`} maxLength={170} value={form.data.seo_description} onChange={event=>form.setData('seo_description',event.target.value)} placeholder="Explicá de forma concreta qué encontrará una persona en esta sección."/><span className="mt-1.5 flex justify-between text-[10px] font-normal text-[#7b878e]"><span>Ideal: 140–160 caracteres.</span><span className={form.data.seo_description.length > 160 ? 'text-amber-600' : ''}>{form.data.seo_description.length}/170</span></span>{form.errors.seo_description && <span className="mt-1 block text-[10px] font-normal text-red-600">{form.errors.seo_description}</span>}</label>
                </div>
                <div className="rounded-xl border border-[#dfe5e8] bg-white p-3"><div className="overflow-hidden rounded-lg border border-[#e5eaed]"><div className="aspect-[1.91/1] bg-[#132b3b]">{previewImage && <img src={previewImage} alt={`Vista previa SEO de ${page.label}`} className="h-full w-full object-cover"/>}</div><div className="p-2.5"><p className="truncate text-[10px] font-semibold text-[#1954b7]">{form.data.seo_title || `${page.label} | Fitinox`}</p><p className="mt-1 line-clamp-2 text-[9px] leading-3.5 text-[#718087]">{form.data.seo_description || 'La descripción aparecerá aquí al compartir el enlace.'}</p></div></div><label className="mt-3 flex cursor-pointer items-center gap-2 rounded-lg border border-dashed border-[#b8c7cf] px-3 py-2.5 text-[10px] text-[#1954b7] transition hover:bg-[#f5f8fc]"><UploadCloud size={15}/><span className="truncate font-semibold">{form.data.seo_og_image_file?.name || (hasPageImage ? 'Reemplazar imagen' : 'Agregar imagen opcional')}</span><input ref={imageInputRef} type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={event=>selectImage(event.target.files?.[0] || null)}/></label><div className="mt-2 flex items-center justify-between gap-2"><small className="text-[9px] text-[#7b878e]">1200 × 630 px · JPG, PNG o WebP.</small>{hasPageImage && <button type="button" onClick={clearImage} className="text-[10px] font-semibold text-[#1954b7] hover:text-red-600">Quitar</button>}</div>{form.errors.seo_og_image_file && <p className="mt-1 text-[10px] text-red-600">{form.errors.seo_og_image_file}</p>}</div>
            </div>
            <div className="mt-5 flex items-center justify-between gap-3 border-t border-[#edf0f2] pt-4"><p className="text-[10px] text-[#758189]">La imagen es opcional: sin una propia, el enlace usa la imagen institucional configurada.</p><button disabled={form.processing} className="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#1954b7] px-4 text-[11px] font-semibold text-white transition hover:bg-[#132b3b] disabled:opacity-50"><Save size={14}/>{form.processing ? 'GUARDANDO…' : 'GUARDAR SEO'}</button></div>
        </form>}
    </article>;
}

export default function Edit({ settings, users, seo = {}, seoPages = [], canManageAdministrators = false }: any) {
    const seoImageInputRef = useRef<HTMLInputElement | null>(null);
    const [seoImagePreview, setSeoImagePreview] = useState<string | null>(null);
    const form = useForm<any>({
        seo_title: settings.seo_title || 'Fitinox | Componentes para sistemas de control de fluidos',
        seo_description: settings.seo_description || 'Componentes y soluciones Fitinox para conducción, instrumentación y control de fluidos.',
        seo_keywords: settings.seo_keywords || 'fitinox, conexiones, válvulas, acero inoxidable, control de fluidos',
        seo_og_image_file: null,
        remove_seo_og_image: false,
        downloads_enabled: settings.downloads_enabled !== '0',
        client_area_enabled: settings.client_area_enabled !== '0',
        whatsapp_enabled: settings.whatsapp_enabled !== '0',
        whatsapp_number: settings.whatsapp_number || '5491140732016',
        whatsapp_message: settings.whatsapp_message || 'Hola Fitinox, quisiera realizar una consulta.',
        whatsapp_position: settings.whatsapp_position || 'right',
        instagram_url: settings.instagram_url || '',
        facebook_url: settings.facebook_url || '',
        linkedin_url: settings.linkedin_url || '',
    });
    const userForm = useForm({name: '', email: '', password: '', role: 'editor'});
    const adminEditForm = useForm<any>({name:'', email:'', password:'', role:'admin', is_active:true});
    const [editingAdmin, setEditingAdmin] = useState<any>(null);
    const shareImagePreview = seoImagePreview || (!form.data.remove_seo_og_image ? seo.customImageUrl : null) || seo.fallbackImageUrl;
    const hasCustomShareImage = Boolean(seoImagePreview || (!form.data.remove_seo_og_image && seo.customImageUrl));

    const chooseSeoImage = (file: File | null) => {
        if (!file) return;
        if (seoImagePreview) URL.revokeObjectURL(seoImagePreview);
        setSeoImagePreview(URL.createObjectURL(file));
        form.setData('seo_og_image_file', file);
        form.setData('remove_seo_og_image', false);
    };

    const removeSeoImage = () => {
        if (seoImagePreview) URL.revokeObjectURL(seoImagePreview);
        setSeoImagePreview(null);
        form.setData('seo_og_image_file', null);
        form.setData('remove_seo_og_image', true);
        if (seoImageInputRef.current) seoImageInputRef.current.value = '';
    };

    return <AdminLayout title="Configuración"><Head title="Configuración | Fitinox"/>
        <form onSubmit={event=>{event.preventDefault(); form.post('/admin/settings',{forceFormData:true,preserveScroll:true});}} className="mx-auto max-w-[1120px] space-y-6">
            <Section id="seo" title="SEO y enlaces compartidos" description="Definí cómo aparece Fitinox en Google, WhatsApp, LinkedIn y redes sociales." icon={SearchCheck}>
                <div className="grid gap-7 xl:grid-cols-[minmax(0,1fr)_360px]">
                    <div className="space-y-5">
                        <div className="rounded-2xl border border-[#1954b7]/15 bg-[linear-gradient(135deg,rgba(25,84,183,.09),rgba(255,255,255,.95))] p-5">
                            <div className="flex items-start gap-3">
                                <span className="grid size-10 shrink-0 place-items-center rounded-xl bg-white text-[#1954b7] shadow-sm"><SearchCheck size={19}/></span>
                                <div><p className="text-[14px] font-semibold text-[#132b3b]">SEO global claro y consistente</p><p className="mt-1 text-[12px] leading-5 text-[#66757d]">Estos datos se usan como respaldo en toda la web. Cada página puede tener luego su propio título y descripción desde su edición.</p></div>
                            </div>
                            <div className="mt-4 grid gap-2 sm:grid-cols-3">
                                {[
                                    ['Título', form.data.seo_title.length > 0, `${form.data.seo_title.length}/70`],
                                    ['Descripción', form.data.seo_description.length > 0, `${form.data.seo_description.length}/170`],
                                    ['Palabras clave', form.data.seo_keywords.length > 0, form.data.seo_keywords ? 'Cargadas' : 'Pendiente'],
                                ].map(([label, ready, detail])=><div key={String(label)} className={`flex items-center gap-2 rounded-xl border px-3 py-2 text-[10px] ${ready ? 'border-emerald-100 bg-white text-emerald-700' : 'border-amber-100 bg-amber-50 text-amber-700'}`}><span className={`size-1.5 rounded-full ${ready ? 'bg-emerald-500' : 'bg-amber-500'}`}/><span className="font-semibold">{label}</span><span className="ml-auto text-[#7b878e]">{detail}</span></div>)}
                            </div>
                        </div>

                        <div className="grid gap-5 md:grid-cols-2">
                            <label className="text-[12px] font-semibold text-[#263b45]">Título SEO global<input className={field} maxLength={70} value={form.data.seo_title} onChange={event=>form.setData('seo_title',event.target.value)}/><span className="mt-1.5 flex justify-between gap-3 text-[10px] font-normal text-[#7b878e]"><span>Ideal: 50–60 caracteres. Es el título que suele mostrar Google.</span><span className={form.data.seo_title.length > 60 ? 'font-semibold text-amber-600' : ''}>{form.data.seo_title.length}/70</span></span></label>
                            <label className="text-[12px] font-semibold text-[#263b45]">Palabras clave<input className={field} value={form.data.seo_keywords} onChange={event=>form.setData('seo_keywords',event.target.value)}/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Separadas por comas. Usá términos reales de producto, industria y marca.</span></label>
                            <label className="md:col-span-2 text-[12px] font-semibold text-[#263b45]">Descripción SEO global<textarea rows={4} className={`${field} py-3`} maxLength={170} value={form.data.seo_description} onChange={event=>form.setData('seo_description',event.target.value)}/><span className="mt-1.5 flex justify-between gap-3 text-[10px] font-normal text-[#7b878e]"><span>Ideal: 140–160 caracteres. Explicá de forma directa qué encontrará la persona.</span><span className={form.data.seo_description.length > 160 ? 'font-semibold text-amber-600' : ''}>{form.data.seo_description.length}/170</span></span></label>
                        </div>
                    </div>

                    <aside className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-[#f8fafb]">
                        <div className="border-b border-[#e5eaed] bg-white px-5 py-4"><div className="flex items-center gap-2 text-[#132b3b]"><Image size={17} className="text-[#1954b7]"/><h3 className="text-[13px] font-semibold">Imagen al compartir</h3></div><p className="mt-1 text-[10px] leading-4 text-[#758189]">Opcional. Si no cargás una, se usa automáticamente el {seo.fallbackSource || 'logo institucional'}.</p></div>
                        <div className="p-5">
                            <div className="overflow-hidden rounded-xl border border-[#dfe5e8] bg-white shadow-[0_8px_18px_rgba(19,43,59,.05)]">
                                <div className="aspect-[1.91/1] bg-[#132b3b]">{shareImagePreview ? <img src={shareImagePreview} alt="Vista previa de la imagen para compartir" className="h-full w-full object-cover"/> : <div className="grid h-full place-items-center text-[11px] text-white/70">Sin imagen disponible</div>}</div>
                                <div className="p-3"><p className="truncate text-[11px] font-semibold text-[#1954b7]">{form.data.seo_title || 'Título SEO de Fitinox'}</p><p className="mt-1 line-clamp-2 text-[10px] leading-4 text-[#6f7d84]">{form.data.seo_description || 'Descripción que acompañará al enlace compartido.'}</p><p className="mt-2 text-[9px] font-medium text-[#8a969c]">fitinox.com.ar</p></div>
                            </div>
                            <label className="mt-4 flex min-h-[82px] cursor-pointer items-center gap-3 rounded-xl border border-dashed border-[#aebfc8] bg-white px-4 transition hover:border-[#1954b7] hover:bg-[#f5f8fc] focus-within:ring-4 focus-within:ring-[#1954b7]/10"><span className="grid size-10 shrink-0 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><UploadCloud size={18}/></span><span className="min-w-0"><strong className="block truncate text-[11px] text-[#263b45]">{form.data.seo_og_image_file?.name || (hasCustomShareImage ? 'Reemplazar imagen' : 'Cargar imagen opcional')}</strong><small className="mt-1 block text-[10px] leading-4 text-[#7a878e]">1200 × 630 px · JPG, PNG o WebP · máximo 5 MB.</small></span><input ref={seoImageInputRef} type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={event=>chooseSeoImage(event.target.files?.[0] || null)}/></label>
                            <div className="mt-3 flex min-h-5 items-center justify-between gap-3 text-[10px] text-[#718087]"><span className="flex items-center gap-1.5"><span className={`size-1.5 rounded-full ${hasCustomShareImage ? 'bg-emerald-500' : 'bg-[#1954b7]'}`}/>{hasCustomShareImage ? 'Imagen propia activa' : 'Usando respaldo automático'}</span>{hasCustomShareImage && <button type="button" onClick={removeSeoImage} className="font-semibold text-[#1954b7] transition hover:text-red-600">Quitar</button>}</div>
                        </div>
                    </aside>
                </div>
            </Section>

            <Section id="seo-pages" title="SEO por página" description="Cada sección puede tener su propio título, descripción e imagen para compartir." icon={SearchCheck}>
                <div className="rounded-xl border border-[#1954b7]/15 bg-[#edf3f7]/70 p-4"><div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between"><div><p className="text-[12px] font-semibold text-[#132b3b]">Control de visibilidad por sección</p><p className="mt-1 text-[10px] leading-4 text-[#718087]">Completá título, descripción y palabras clave para independizar una página del SEO global. La imagen siempre es opcional.</p></div><span className="inline-flex w-fit items-center gap-2 rounded-full bg-white px-3 py-1.5 text-[10px] font-semibold text-[#1954b7]"><span className="size-1.5 rounded-full bg-[#1954b7]"/>{seoPages.filter((page:any)=>page.seoTitle && page.seoDescription && page.seoKeywords).length} de {seoPages.length} completas</span></div></div>
                <div className="mt-4 space-y-3">{seoPages.map((page:any)=><PageSeoCard key={page.id} page={page} fallbackImageUrl={seo.fallbackImageUrl}/>)}</div>
            </Section>

            <Section id="visibilidad-publica" title="Visibilidad pública" description="Controlá qué accesos aparecen en la navegación, el menú móvil y el footer." icon={PanelTop}>
                <div className="grid gap-4 md:grid-cols-2">
                    <Switch checked={form.data.downloads_enabled} onChange={(value:boolean)=>form.setData('downloads_enabled',value)} label="Mostrar Descargas" description="Al desactivarlo se ocultan la sección, el encabezado, el menú móvil y el enlace del footer."/>
                    <Switch checked={form.data.client_area_enabled} onChange={(value:boolean)=>form.setData('client_area_enabled',value)} label="Mostrar Zona Clientes" description="Al desactivarlo se ocultan los accesos de Clientes en desktop y móvil."/>
                </div>
            </Section>

            <Section id="whatsapp" title="WhatsApp flotante" description="Configura el botón de contacto que aparece sobre las páginas públicas." icon={MessageCircle}>
                <div className="grid gap-5 md:grid-cols-2">
                    <div className="md:col-span-2"><Switch checked={form.data.whatsapp_enabled} onChange={(value:boolean)=>form.setData('whatsapp_enabled',value)} label="Mostrar WhatsApp flotante" description="El botón se elimina completamente del sitio cuando está desactivado."/></div>
                    <label className="text-[12px] font-semibold">Número internacional<input inputMode="numeric" className={field} value={form.data.whatsapp_number} onChange={event=>form.setData('whatsapp_number',event.target.value.replace(/\D/g,''))}/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Sólo números, con código de país. Ejemplo: 5491140732016.</span></label>
                    <label className="text-[12px] font-semibold">Posición<select className={field} value={form.data.whatsapp_position} onChange={event=>form.setData('whatsapp_position',event.target.value)}><option value="right">Inferior derecha</option><option value="left">Inferior izquierda</option></select></label>
                    <label className="md:col-span-2 text-[12px] font-semibold">Mensaje inicial<textarea rows={3} className={`${field} py-3`} value={form.data.whatsapp_message} onChange={event=>form.setData('whatsapp_message',event.target.value)}/></label>
                </div>
            </Section>

            <Section id="redes-sociales" title="Redes sociales" description="Los tres accesos se imprimen con sus SVG oficiales en el footer público y en Zona Clientes." icon={Link2}>
                <div className="grid gap-5 md:grid-cols-3">
                    <label className="text-[12px] font-semibold"><span className="flex items-center gap-2"><img src="/storage/figma/header-footer/instagram.svg" alt="" className="size-4"/>Instagram</span><input type="url" inputMode="url" placeholder="https://instagram.com/tu-cuenta" className={field} value={form.data.instagram_url} onChange={event=>form.setData('instagram_url',event.target.value)}/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Pegá el enlace completo del perfil.</span></label>
                    <label className="text-[12px] font-semibold"><span className="flex items-center gap-2"><img src="/storage/figma/header-footer/facebook.svg" alt="" className="size-4"/>Facebook</span><input type="url" inputMode="url" placeholder="https://facebook.com/tu-página" className={field} value={form.data.facebook_url} onChange={event=>form.setData('facebook_url',event.target.value)}/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Pegá el enlace completo de la página.</span></label>
                    <label className="text-[12px] font-semibold"><span className="flex items-center gap-2"><img src="/storage/figma/header-footer/linkedin.svg" alt="" className="size-4"/>LinkedIn</span><input type="url" inputMode="url" placeholder="https://linkedin.com/company/..." className={field} value={form.data.linkedin_url} onChange={event=>form.setData('linkedin_url',event.target.value)}/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Pegá el enlace completo de la empresa.</span></label>
                </div>
            </Section>

            <div className="flex justify-end"><button disabled={form.processing||!form.isDirty} className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#1954b7] px-7 text-[12px] font-semibold text-white hover:bg-[#132b3b] disabled:opacity-45"><Save size={16}/>{form.processing?'GUARDANDO…':'GUARDAR CONFIGURACIÓN'}</button></div>
            {Object.keys(form.errors).length>0&&<div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-[11px] text-red-700">{Object.values(form.errors).map((error:any)=><p key={error}>{error}</p>)}</div>}
        </form>

        <div id="users" className="mx-auto mt-6 max-w-[1120px] scroll-mt-28">
            <Section title="Usuarios administrativos" description="Solo cuentas internas con acceso al CMS. Los clientes se gestionan exclusivamente desde Zona Clientes." icon={Users}>
                {canManageAdministrators ? <form onSubmit={event=>{event.preventDefault(); userForm.post('/admin/settings/users',{preserveScroll:true,onSuccess:()=>userForm.reset()});}} className="grid gap-3 border-b border-[#edf0f2] pb-6 md:grid-cols-2 xl:grid-cols-[1fr_1fr_1fr_150px_auto]">
                    <input className={field} placeholder="Nombre" value={userForm.data.name} onChange={event=>userForm.setData('name',event.target.value)}/>
                    <input type="email" className={field} placeholder="Email" value={userForm.data.email} onChange={event=>userForm.setData('email',event.target.value)}/>
                    <input type="password" className={field} placeholder="Contraseña (mín. 8)" value={userForm.data.password} onChange={event=>userForm.setData('password',event.target.value)}/>
                    <select className={field} value={userForm.data.role} onChange={event=>userForm.setData('role',event.target.value)}><option value="editor">Editor</option><option value="admin">Administrador</option></select>
                    <button disabled={userForm.processing} className="mt-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#132b3b] px-5 text-[11px] font-semibold text-white"><Plus size={15}/> AGREGAR</button>
                </form> : <div className="mb-5 rounded-xl border border-amber-100 bg-amber-50 px-4 py-3 text-[11px] text-amber-800">Solo el Super Admin puede crear, editar o desactivar accesos administrativos.</div>}
                {Object.keys(userForm.errors).length>0&&<div className="mt-4 rounded-xl bg-red-50 p-4 text-[11px] text-red-700">{Object.values(userForm.errors).map((error:any)=><p key={error}>{error}</p>)}</div>}
                <div className="mt-5 overflow-hidden rounded-xl border border-[#e5eaed]"><table className="w-full text-left text-[12px]"><thead className="bg-[#132b3b] text-white"><tr><th className="px-5 py-4">Usuario</th><th>Rol</th><th>Estado</th><th className="pr-5 text-right">Acceso</th></tr></thead><tbody>{users.map((user:any)=><tr key={user.id} className="border-t border-[#edf0f2]"><td className="px-5 py-4"><strong className="block text-[#263b45]">{user.name}</strong><small className="text-[#7b878e]">{user.email}</small></td><td><span className="capitalize">{user.role === 'super_admin' ? 'Super Admin' : user.role}</span></td><td><span className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold ${user.is_active?'bg-emerald-50 text-emerald-700':'bg-[#f1f3f4] text-[#728087]'}`}><ShieldCheck size={12}/>{user.is_active?'Activo':'Desactivado'}</span></td><td className="pr-5 text-right">{canManageAdministrators ? <button type="button" onClick={()=>{setEditingAdmin(user);adminEditForm.setData({name:user.name,email:user.email,password:'',role:user.role,is_active:user.is_active});}} className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#dfe5e8] px-3 text-[11px] text-[#132b3b] transition hover:border-[#1954b7] hover:text-[#1954b7]"><Pencil size={14}/> EDITAR</button> : <span className="text-[#758189]">Sin permisos</span>}</td></tr>)}</tbody></table></div>
                {editingAdmin && <form onSubmit={event=>{event.preventDefault();adminEditForm.patch(`/admin/settings/users/${editingAdmin.id}`,{preserveScroll:true,onSuccess:()=>setEditingAdmin(null)});}} className="mt-5 rounded-xl border border-[#1954b7]/20 bg-[#f7fafc] p-5"><div className="flex items-center justify-between gap-3"><div><p className="text-[13px] font-semibold text-[#132b3b]">Editar acceso administrativo</p><p className="mt-1 text-[10px] text-[#758189]">{editingAdmin.role === 'super_admin' ? 'La cuenta Super Admin no se puede desactivar ni bajar de rol.' : 'Podés actualizar rol, datos y contraseña sin crear un usuario nuevo.'}</p></div><button type="button" onClick={()=>setEditingAdmin(null)} className="text-[11px] font-semibold text-[#65747c] hover:text-[#132b3b]">CERRAR</button></div><div className="mt-4 grid gap-3 md:grid-cols-2 xl:grid-cols-4"><input className={field} value={adminEditForm.data.name} onChange={event=>adminEditForm.setData('name',event.target.value)} aria-label="Nombre administrativo"/><input type="email" className={field} value={adminEditForm.data.email} onChange={event=>adminEditForm.setData('email',event.target.value)} aria-label="Email administrativo"/><label className="relative"><input type="password" className={field} value={adminEditForm.data.password} onChange={event=>adminEditForm.setData('password',event.target.value)} placeholder="Nueva contraseña (opcional)" aria-label="Nueva contraseña"/><KeyRound size={14} className="absolute right-3 top-[30px] text-[#758189]"/></label><select disabled={editingAdmin.role === 'super_admin'} className={field} value={adminEditForm.data.role} onChange={event=>adminEditForm.setData('role',event.target.value)}><option value="super_admin">Super Admin</option><option value="admin">Administrador</option><option value="editor">Editor</option></select></div><div className="mt-4 flex flex-wrap items-center justify-between gap-3"><label className="inline-flex items-center gap-2 text-[11px] font-semibold text-[#263b45]"><input type="checkbox" disabled={editingAdmin.role === 'super_admin'} checked={adminEditForm.data.is_active} onChange={event=>adminEditForm.setData('is_active',event.target.checked)} className="size-4 accent-[#1954b7]"/>Acceso activo</label><button disabled={adminEditForm.processing} className="inline-flex min-h-10 items-center gap-2 rounded-xl bg-[#1954b7] px-4 text-[11px] font-semibold text-white transition hover:bg-[#132b3b] disabled:opacity-45"><Save size={14}/>{adminEditForm.processing?'GUARDANDO…':'GUARDAR USUARIO'}</button></div>{Object.values(adminEditForm.errors).map((error:any)=><p key={error} className="mt-2 text-[11px] text-red-600">{error}</p>)}</form>}
            </Section>
        </div>
    </AdminLayout>;
}
