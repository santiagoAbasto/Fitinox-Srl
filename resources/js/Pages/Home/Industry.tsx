import { Head, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Flame, Link as LinkIcon, Save, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';
import RichTextEditor from '../../Components/RichTextEditor';

const input = 'mt-2 min-h-12 w-full rounded-lg border border-[#dfe4e7] bg-[#fbfcfc] px-4 text-sm outline-none transition focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/8';

export default function Industry({ page }: { page: any }) {
    const section = page.sections || {};
    const form = useForm({
        industry_title: section.industry_title || 'Oil & Gas',
        industry_text: section.industry_text || '',
        industry_button_label: section.industry_button_label || 'MAS INFO',
        industry_button_url: section.industry_button_url || '#',
        industry_image: section.industry_image || 'figma/oil-gas.png',
        industry_image_file: null as File | null,
        industry_is_active: section.industry_is_active ?? true,
    });
    const [preview, setPreview] = useState(`/storage/${form.data.industry_image}`);

    useEffect(() => {
        if (!form.data.industry_image_file) {
            setPreview(`/storage/${form.data.industry_image}`);
            return;
        }
        const url = URL.createObjectURL(form.data.industry_image_file);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.industry_image_file, form.data.industry_image]);

    return <AdminLayout title="Oil & Gas">
        <Head title="Oil & Gas | Fitinox"/>
        <form onSubmit={event => { event.preventDefault(); form.post('/admin/home/industry', { forceFormData: true, preserveScroll: true }); }} className="mx-auto max-w-[1120px]">
            <header className="mb-7 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
                <div><h2 className="text-[18px] font-semibold text-[#132b3b]">Banner Oil & Gas</h2><p className="mt-1 text-[13px] leading-6 text-[#738087]">Administrá el banner ubicado antes del footer de la página principal.</p></div>
                <label className="flex min-h-11 cursor-pointer items-center gap-3 rounded-full bg-white px-4 text-[11px] font-semibold text-[#526068] shadow-[0_1px_4px_rgba(19,43,59,.08)]">
                    {form.data.industry_is_active ? <Eye size={15} className="text-emerald-600"/> : <EyeOff size={15}/>}
                    {form.data.industry_is_active ? 'Visible en la web' : 'Oculto'}
                    <input type="checkbox" className="size-4 accent-[#1954b7]" checked={form.data.industry_is_active} onChange={event => form.setData('industry_is_active', event.target.checked)}/>
                </label>
            </header>

            <div className="overflow-hidden rounded-2xl border border-[#e0e6e9] bg-white shadow-[0_8px_30px_rgba(19,43,59,.05)]">
                <div className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-10 place-items-center rounded-lg bg-[#edf3f7] text-[#132b3b]"><Flame size={19}/></span><div><h2 className="text-[15px] font-semibold text-[#132b3b]">Contenido del banner</h2><p className="mt-1 text-[11px] text-[#7b878e]">Imagen, texto y enlace del llamado a la acción</p></div></div>

                <div className="p-6">
                    <div className="relative aspect-[16/5] overflow-hidden rounded-xl bg-[#132b3b]">
                        <img src={preview} alt="Vista previa del banner Oil & Gas" className="h-full w-full object-cover"/>
                        <div className="absolute inset-0 bg-gradient-to-t from-[#132b3b]/85 to-[#3475a1]/45"/>
                        <div className="absolute inset-0 grid place-items-center px-6 text-center text-white">
                            <div className="max-w-[680px]"><h3 className="text-[30px] font-semibold">{form.data.industry_title || 'Oil & Gas'}</h3><div className="mt-2 line-clamp-2 text-[14px] text-white/80">{form.data.industry_text.replace(/<[^>]*>/g, '')}</div>{form.data.industry_button_label && <span className="mt-5 inline-flex rounded-lg bg-white px-4 py-2 text-[10px] font-semibold text-[#132b3b]">{form.data.industry_button_label}</span>}</div>
                        </div>
                    </div>
                    <label className="mt-4 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-[#bfc9ce] text-[12px] font-semibold text-[#52616a] transition hover:border-[#1954b7] hover:text-[#1954b7]"><UploadCloud size={16}/> Reemplazar imagen del banner<input type="file" accept="image/png,image/jpeg,image/webp" className="sr-only" onChange={event => form.setData('industry_image_file', event.target.files?.[0] || null)}/></label>
                    <p className="mt-2 text-[10px] leading-4 text-[#7b878e]"><strong>Recomendado:</strong> 1920 × 460 px · JPG, PNG o WebP · máximo 8 MB.</p>

                    <div className="mt-7 grid gap-6 lg:grid-cols-2">
                        <div className="space-y-5">
                            <label className="block text-[12px] font-semibold">Título<input className={input} value={form.data.industry_title} onChange={event => form.setData('industry_title', event.target.value)}/></label>
                            <RichTextEditor label="Descripción" value={form.data.industry_text} onChange={value => form.setData('industry_text', value)} error={form.errors.industry_text}/>
                        </div>
                        <div className="space-y-5">
                            <label className="block text-[12px] font-semibold">Texto del botón<input className={input} value={form.data.industry_button_label} onChange={event => form.setData('industry_button_label', event.target.value)}/></label>
                            <label className="block text-[12px] font-semibold">Enlace del botón
                                <div className="relative"><LinkIcon size={16} className="pointer-events-none absolute top-[26px] left-4 text-[#849097]"/><input className={`${input} pl-11`} value={form.data.industry_button_url} onChange={event => form.setData('industry_button_url', event.target.value)} placeholder="/productos, #contacto o https://…"/></div>
                                <span className="mt-1.5 block text-[10px] font-normal leading-4 text-[#7b878e]">Acepta una ruta del sitio, una sección con # o una URL externa completa.</span>
                                {form.errors.industry_button_url && <span className="mt-1 block text-[11px] text-red-600">{form.errors.industry_button_url}</span>}
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div className="mt-5 flex justify-end"><button disabled={form.processing || !form.isDirty} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-[#1954b7] px-5 text-[12px] font-semibold text-white transition hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{form.processing ? 'Guardando…' : 'Guardar cambios'}</button></div>
        </form>
    </AdminLayout>;
}
