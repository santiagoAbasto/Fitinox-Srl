import { Head, router, useForm } from '@inertiajs/react';
import { CirclePlay, Eye, EyeOff, GripVertical, Image, Plus, Save, Trash2, UploadCloud, Video } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const input = 'mt-2 min-h-12 w-full rounded-lg border border-[#dfe4e7] bg-[#fbfcfc] px-4 text-sm outline-none transition focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/8';

function youtubeId(url: string) {
    return url.match(/(?:youtu\.be\/|youtube\.com\/(?:watch\?v=|embed\/|shorts\/))([^?&/]+)/)?.[1] || '';
}

function SlideEditor({ slide }: { slide: any }) {
    const form = useForm({
        title: slide.title || '',
        button_label: slide.button_label || '',
        button_url: slide.button_url || '',
        media_type: slide.media_type || 'image',
        youtube_url: slide.youtube_url || '',
        media_file: null as File | null,
        is_active: Boolean(slide.is_active),
        sort_order: slide.sort_order || 'A',
    });
    const [localPreview, setLocalPreview] = useState('');

    useEffect(() => {
        if (!form.data.media_file) {
            setLocalPreview('');
            return;
        }
        const url = URL.createObjectURL(form.data.media_file);
        setLocalPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.media_file]);

    const currentMedia = localPreview || (slide.media_path ? `/storage/${slide.media_path}` : '');
    const videoMedia = form.data.media_type === 'video';
    const Icon = form.data.media_type === 'youtube' ? CirclePlay : videoMedia ? Video : Image;

    return <article id={`slide-${slide.id}`} className="scroll-mt-28 overflow-hidden rounded-2xl border border-[#e0e6e9] bg-white shadow-[0_8px_30px_rgba(19,43,59,.05)]">
        <div className="flex items-center gap-3 border-b border-[#edf0f2] px-5 py-4">
            <GripVertical size={17} className="text-[#a1abb1]"/>
            <span className="grid size-9 place-items-center rounded-lg bg-[#edf3f7] text-[#132b3b]"><Icon size={18}/></span>
            <div className="min-w-0 grow">
                <h2 className="truncate text-[15px] font-semibold text-[#132b3b]">{form.data.title || 'Slide sin título'}</h2>
                <p className="mt-0.5 text-[11px] text-[#7b878e]">Orden {form.data.sort_order}</p>
            </div>
            <span className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold ${form.data.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-[#f1f3f4] text-[#667279]'}`}>
                {form.data.is_active ? <Eye size={13}/> : <EyeOff size={13}/>} {form.data.is_active ? 'Visible' : 'Oculto'}
            </span>
        </div>

        <form onSubmit={event => {
            event.preventDefault();
            form.post(`/admin/home/sliders/${slide.id}`, { forceFormData: true, preserveScroll: true });
        }} className="grid gap-0 xl:grid-cols-[1.08fr_.92fr]">
            <div className="border-b border-[#edf0f2] p-6 xl:border-r xl:border-b-0">
                <div className="relative aspect-video overflow-hidden rounded-xl bg-[#132b3b]">
                    {form.data.media_type === 'youtube' && youtubeId(form.data.youtube_url)
                        ? <iframe title="Vista previa de YouTube" src={`https://www.youtube-nocookie.com/embed/${youtubeId(form.data.youtube_url)}`} className="h-full w-full" allowFullScreen/>
                        : currentMedia
                            ? videoMedia
                                ? <video src={currentMedia} className="h-full w-full object-cover" controls muted/>
                                : <img src={currentMedia} alt="" className="h-full w-full object-cover"/>
                            : <div className="grid h-full place-items-center text-white/45"><Icon size={36}/></div>}
                    <div className="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-[#07131b]/85 to-transparent px-6 pt-16 pb-5 text-white">
                        <p className="max-w-[520px] text-[24px] leading-tight font-semibold">{form.data.title || 'Título del slide'}</p>
                        {form.data.button_label && <span className="mt-4 inline-flex rounded-md bg-white px-4 py-2 text-[10px] font-semibold text-[#132b3b]">{form.data.button_label}</span>}
                    </div>
                </div>

                {form.data.media_type !== 'youtube' && <label className="mt-4 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-[#bfc9ce] text-[12px] font-semibold text-[#52616a] transition hover:border-[#1954b7] hover:text-[#1954b7]">
                    <UploadCloud size={16}/> {slide.media_path ? 'Reemplazar archivo' : 'Seleccionar archivo'}
                    <input type="file" className="sr-only" accept={videoMedia ? 'video/mp4,video/webm,video/quicktime' : 'image/png,image/jpeg,image/webp'} onChange={event => form.setData('media_file', event.target.files?.[0] || null)}/>
                </label>}
                {form.data.media_type !== 'youtube' && <p className="mt-2 text-[10px] leading-4 text-[#7b878e]">{videoMedia ? <><strong>Recomendado:</strong> 1920 × 1080 px (16:9) · MP4, WebM o MOV · máximo 50 MB.</> : <><strong>Recomendado:</strong> 1920 × 840 px (16:7) · JPG, PNG o WebP · máximo 50 MB.</>}</p>}
                {form.errors.media_file && <p className="mt-2 text-[11px] text-red-600">{form.errors.media_file}</p>}
            </div>

            <div className="space-y-5 p-6">
                <label className="block text-[12px] font-semibold">Tipo de contenido
                    <div className="mt-2 grid grid-cols-3 gap-2">
                        {[['image', Image, 'Imagen'], ['video', Video, 'Video'], ['youtube', CirclePlay, 'YouTube']].map(([value, MediaIcon, label]: any) =>
                            <button key={value} type="button" onClick={() => form.setData('media_type', value)} className={`flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border text-[11px] transition ${form.data.media_type === value ? 'border-[#1954b7] bg-[#eef4ff] font-semibold text-[#1954b7]' : 'border-[#dfe4e7] text-[#67737a] hover:border-[#bac5ca]'}`}><MediaIcon size={15}/>{label}</button>
                        )}
                    </div>
                </label>
                {form.data.media_type === 'youtube' && <label className="block text-[12px] font-semibold">URL de YouTube<input type="url" className={input} value={form.data.youtube_url} onChange={event => form.setData('youtube_url', event.target.value)} placeholder="https://www.youtube.com/watch?v=…"/><span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Admite enlaces watch, youtu.be, Shorts o embed. Recomendado: video 16:9 en Full HD.</span>{form.errors.youtube_url && <span className="mt-1 block text-[11px] text-red-600">{form.errors.youtube_url}</span>}</label>}
                <label className="block text-[12px] font-semibold">Título<input className={input} value={form.data.title} onChange={event => form.setData('title', event.target.value)}/></label>
                <div className="grid gap-4 sm:grid-cols-2">
                    <label className="block text-[12px] font-semibold">Texto del botón<input className={input} value={form.data.button_label} onChange={event => form.setData('button_label', event.target.value)}/></label>
                    <label className="block text-[12px] font-semibold">Enlace del botón<input className={input} value={form.data.button_url} onChange={event => form.setData('button_url', event.target.value)}/></label>
                </div>
                <div className="grid gap-4 sm:grid-cols-2">
                    <label className="block text-[12px] font-semibold">Orden
                        <input
                            type="text"
                            inputMode="text"
                            maxLength={3}
                            className={`${input} uppercase`}
                            value={form.data.sort_order}
                            onChange={event => form.setData('sort_order', event.target.value.toUpperCase().replace(/[^A-Z]/g, ''))}
                            placeholder="A"
                        />
                        <span className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Secuencia: A, AA, AB… AZ, B, BA…</span>
                        {form.errors.sort_order && <span className="mt-1 block text-[11px] text-red-600">{form.errors.sort_order}</span>}
                    </label>
                    <label className="mt-6 flex min-h-12 cursor-pointer items-center justify-between rounded-lg border border-[#dfe4e7] px-4 text-[12px] font-medium">
                        Mostrar en la web
                        <input type="checkbox" checked={form.data.is_active} onChange={event => form.setData('is_active', event.target.checked)} className="size-4 accent-[#1954b7]"/>
                    </label>
                </div>
                <div className="flex items-center justify-between border-t border-[#edf0f2] pt-5">
                    <button type="button" onClick={() => confirm('¿Eliminar este slide?') && router.delete(`/admin/home/sliders/${slide.id}`, { preserveScroll: true })} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg px-3 text-[11px] font-semibold text-red-600 transition hover:bg-red-50"><Trash2 size={15}/> Eliminar</button>
                    <button disabled={form.processing || !form.isDirty} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-[#1954b7] px-5 text-[11px] font-semibold text-white transition hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{form.processing ? 'Guardando…' : 'Guardar slide'}</button>
                </div>
            </div>
        </form>
    </article>;
}

export default function Sliders({ slides }: { slides: any[] }) {
    return <AdminLayout title="Slider de inicio">
        <Head title="Slider de inicio | Fitinox"/>
        <div className="mx-auto max-w-[1180px]">
            <header className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div><h2 className="text-[18px] font-semibold text-[#132b3b]">Slides publicados</h2><p className="mt-1 text-[13px] leading-6 text-[#738087]">Administrá el texto, botón y medio de cada slide de la portada.</p></div>
                <button onClick={() => router.post('/admin/home/sliders')} className="inline-flex min-h-11 cursor-pointer items-center justify-center gap-2 rounded-lg bg-[#132b3b] px-5 text-[12px] font-semibold text-white transition hover:bg-[#1954b7]"><Plus size={16}/> Nuevo slide</button>
            </header>
            <div className="space-y-6">
                {slides.length ? slides.map(slide => <SlideEditor key={slide.id} slide={slide}/>) : <div className="rounded-xl border border-dashed border-[#cbd3d7] bg-white p-12 text-center"><p className="text-sm font-semibold text-[#132b3b]">Todavía no hay slides</p><p className="mt-2 text-[12px] text-[#738087]">Creá el primero para configurar la portada.</p></div>}
            </div>
        </div>
    </AdminLayout>;
}
