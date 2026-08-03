import { Head, useForm } from '@inertiajs/react';
import { Building2, CirclePlay, Image, Save, ShieldCheck, Target, Telescope, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';
import RichTextEditor from '../../Components/RichTextEditor';

const input = 'mt-2 min-h-12 w-full rounded-lg border border-[#dfe4e7] bg-[#fbfcfc] px-4 text-sm outline-none transition focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/8';

function Media({ label, path, file, onChange, ratio = 'aspect-[16/5]', recommendation = '1920 × 600 px' }: { label: string, path: string, file: File | null, onChange: (file: File | null) => void, ratio?: string, recommendation?: string }) {
    const [preview, setPreview] = useState(`/storage/${path}`);
    const [dimensions, setDimensions] = useState('');
    useEffect(() => {
        if (!file) return setPreview(`/storage/${path}`);
        const url = URL.createObjectURL(file);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [file, path]);
    return <div><p className="mb-2 text-[12px] font-semibold">{label}</p><div className={`${ratio} overflow-hidden rounded-xl bg-[#eef2f4]`}><img src={preview} alt={`Vista previa de ${label}`} className="h-full w-full object-cover" onLoad={event=>setDimensions(`${event.currentTarget.naturalWidth} × ${event.currentTarget.naturalHeight} px`)}/></div><label className="mt-3 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-[#bfc9ce] text-[12px] font-semibold text-[#52616a] hover:border-[#1954b7] hover:text-[#1954b7]"><UploadCloud size={16}/> Reemplazar imagen<input type="file" accept="image/png,image/jpeg,image/webp" className="sr-only" onChange={event => onChange(event.target.files?.[0] || null)}/></label><p className="mt-2 text-[10px] leading-4 text-[#7b878e]"><strong>Actual:</strong> {dimensions || 'leyendo…'} · <strong>Recomendado:</strong> {recommendation}.<br/>JPG, PNG o WebP · máximo 8 MB.</p></div>;
}

function Section({ id, title, description, icon: Icon, children }: any) {
    return <section id={id} className="scroll-mt-28 overflow-hidden rounded-2xl border border-[#e0e6e9] bg-white shadow-[0_8px_30px_rgba(19,43,59,.04)]"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-10 place-items-center rounded-lg bg-[#edf3f7] text-[#132b3b]"><Icon size={19}/></span><div><h2 className="text-[15px] font-semibold text-[#132b3b]">{title}</h2><p className="mt-1 text-[11px] text-[#7b878e]">{description}</p></div></header><div className="p-6">{children}</div></section>;
}

export default function Edit({ page }: { page: any }) {
    const section = page.sections || {};
    const form = useForm({
        hero_title: page.hero_title || 'Nosotros', hero_image: page.hero_image || 'figma/nosotros/hero.png', hero_image_file: null as File | null,
        content_title: section.content_title || '¿Quienes somos?', content: page.content || '', content_image: section.content_image || 'figma/nosotros/about.png', content_image_file: null as File | null,
        values_title: section.values_title || '¿Por qué elegirnos?',
        mission_title: section.mission_title || 'Misión', mission_text: section.mission_text || '',
        vision_title: section.vision_title || 'Visión', vision_text: section.vision_text || '',
        quality_title: section.quality_title || 'Calidad', quality_text: section.quality_text || '',
        video_cover: section.video_cover || 'figma/nosotros/video-cover.png', video_cover_file: null as File | null, video_url: section.video_url || '',
    });

    return <AdminLayout title="Nosotros"><Head title="Nosotros | Fitinox"/>
        <form onSubmit={event => { event.preventDefault(); form.post('/admin/nosotros', { forceFormData: true, preserveScroll: true }); }} className="mx-auto max-w-[1120px] space-y-6">
            <header className="mb-7"><h2 className="text-[18px] font-semibold text-[#132b3b]">Página institucional</h2><p className="mt-1 text-[13px] leading-6 text-[#738087]">Contenido completo de la página pública Nosotros.</p></header>

            <Section id="portada" title="Portada" description="Breadcrumb, título e imagen superior." icon={Image}>
                <div className="grid gap-6"><Media label="Imagen de portada" path={form.data.hero_image} file={form.data.hero_image_file} onChange={file => form.setData('hero_image_file', file)}/><label className="block text-[12px] font-semibold">Título de página<input className={input} value={form.data.hero_title} onChange={event => form.setData('hero_title', event.target.value)}/></label></div>
            </Section>

            <Section id="contenido" title="Contenido principal" description="Imagen y presentación institucional." icon={Building2}>
                <div className="grid gap-6 lg:grid-cols-[.85fr_1.15fr]"><Media label="Imagen principal" path={form.data.content_image} file={form.data.content_image_file} ratio="aspect-square" recommendation="1200 × 1200 px (1:1)" onChange={file => form.setData('content_image_file', file)}/><div className="space-y-5"><label className="block text-[12px] font-semibold">Título<input className={input} value={form.data.content_title} onChange={event => form.setData('content_title', event.target.value)}/></label><RichTextEditor label="Contenido" value={form.data.content} onChange={value => form.setData('content', value)} error={form.errors.content}/></div></div>
            </Section>

            <Section id="valores" title="¿Por qué elegirnos?" description="Misión, visión y compromiso con la calidad." icon={Target}>
                <label className="mb-6 block text-[12px] font-semibold">Título del bloque<input className={input} value={form.data.values_title} onChange={event => form.setData('values_title', event.target.value)}/></label>
                <div className="grid gap-4 lg:grid-cols-3">
                    {[
                        ['mission', Target, 'Misión'], ['vision', Telescope, 'Visión'], ['quality', ShieldCheck, 'Calidad'],
                    ].map(([key, Icon, fallback]: any) => <div key={key} className="rounded-xl border border-[#e4e9eb] bg-[#fbfcfc] p-5"><span className="grid size-10 place-items-center rounded-lg bg-[#edf3f7] text-[#1954b7]"><Icon size={18}/></span><label className="mt-4 block text-[12px] font-semibold">Título<input className={input} value={(form.data as any)[`${key}_title`]} onChange={event => form.setData(`${key}_title` as any, event.target.value)}/></label><label className="mt-4 block text-[12px] font-semibold">Descripción<textarea rows={8} className={`${input} py-3 leading-6`} value={(form.data as any)[`${key}_text`]} onChange={event => form.setData(`${key}_text` as any, event.target.value)}/></label></div>)}
                </div>
            </Section>

            <Section id="video" title="Video institucional" description="Portada y enlace de reproducción." icon={CirclePlay}>
                <div className="grid gap-6"><Media label="Imagen de portada del video" path={form.data.video_cover} file={form.data.video_cover_file} recommendation="1920 × 1080 px (16:9)" onChange={file => form.setData('video_cover_file', file)}/><label className="block text-[12px] font-semibold">Enlace del video<input type="url" className={input} value={form.data.video_url} onChange={event => form.setData('video_url', event.target.value)} placeholder="https://youtube.com/watch?v=…"/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Formatos admitidos: YouTube o Vimeo mediante URL. Si queda vacío, se mantiene la portada sin enlace.</span></label></div>
            </Section>

            {Object.keys(form.errors).length > 0 && <div role="alert" className="rounded-lg border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-800">Revisá los campos indicados antes de guardar.</div>}
            <div className="sticky bottom-5 z-20 flex items-center justify-between rounded-xl border border-[#dfe5e8] bg-white/95 px-5 py-3.5 shadow-[0_14px_45px_rgba(19,43,59,.14)] backdrop-blur"><span className="text-[11px] text-[#6e7981]">{form.isDirty ? 'Hay cambios sin guardar' : 'Todo está guardado'}</span><button disabled={form.processing || !form.isDirty} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-[#1954b7] px-5 text-[12px] font-semibold text-white hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{form.processing ? 'Guardando…' : 'Guardar cambios'}</button></div>
        </form>
    </AdminLayout>;
}
