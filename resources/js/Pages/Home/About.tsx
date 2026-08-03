import { Head, useForm } from '@inertiajs/react';
import { Building2, Save, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';
import RichTextEditor from '../../Components/RichTextEditor';

const input = 'mt-2 min-h-12 w-full rounded-lg border border-[#dfe4e7] bg-[#fbfcfc] px-4 text-sm outline-none transition focus:border-[#1954b7] focus:bg-white focus:ring-4 focus:ring-[#1954b7]/8';

export default function About({ page }: { page: any }) {
    const section = page.sections || {};
    const form = useForm({
        about_title: section.about_title || '¿Quienes somos?',
        about_body: page.content || '',
        about_button_label: section.about_button_label || 'MAS INFO',
        about_button_url: section.about_button_url || '/nosotros',
        about_image: section.about_image || 'figma/about.png',
        about_image_file: null as File | null,
    });
    const [preview, setPreview] = useState(`/storage/${form.data.about_image}`);

    useEffect(() => {
        if (!form.data.about_image_file) return setPreview(`/storage/${form.data.about_image}`);
        const url = URL.createObjectURL(form.data.about_image_file);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.about_image_file, form.data.about_image]);

    return <AdminLayout title="Nosotros en Home">
        <Head title="Nosotros en Home | Fitinox"/>
        <form onSubmit={event => { event.preventDefault(); form.post('/admin/home/about', { forceFormData: true, preserveScroll: true }); }} className="mx-auto max-w-[1080px]">
            <header className="mb-7"><h2 className="text-[18px] font-semibold text-[#132b3b]">Bloque institucional</h2><p className="mt-1 text-[13px] leading-6 text-[#738087]">Este contenido corresponde directamente a la sección Nosotros de la página principal.</p></header>
            <div className="overflow-hidden rounded-2xl border border-[#e0e6e9] bg-white shadow-[0_8px_30px_rgba(19,43,59,.05)]">
                <div className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-10 place-items-center rounded-lg bg-[#edf3f7] text-[#132b3b]"><Building2 size={19}/></span><div><h2 className="text-[15px] font-semibold text-[#132b3b]">Nosotros en Home</h2><p className="mt-1 text-[11px] text-[#7b878e]">Imagen, título, descripción y botón</p></div></div>
                <div className="grid gap-0 lg:grid-cols-[.92fr_1.08fr]">
                    <div className="border-b border-[#edf0f2] p-6 lg:border-r lg:border-b-0">
                        <div className="aspect-[4/3] overflow-hidden rounded-xl bg-[#eef2f4]"><img src={preview} alt="Vista previa de Nosotros" className="h-full w-full object-cover"/></div>
                        <label className="mt-4 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-lg border border-dashed border-[#bfc9ce] text-[12px] font-semibold text-[#52616a] hover:border-[#1954b7] hover:text-[#1954b7]"><UploadCloud size={16}/> Reemplazar imagen<input type="file" accept="image/png,image/jpeg,image/webp" className="sr-only" onChange={event => form.setData('about_image_file', event.target.files?.[0] || null)}/></label>
                    </div>
                    <div className="space-y-5 p-6">
                        <label className="block text-[12px] font-semibold">Título<input className={input} value={form.data.about_title} onChange={event => form.setData('about_title', event.target.value)}/></label>
                        <RichTextEditor label="Contenido" value={form.data.about_body} onChange={value => form.setData('about_body', value)} error={form.errors.about_body}/>
                        <div className="grid gap-5 md:grid-cols-2"><label className="block text-[12px] font-semibold">Texto del botón<input className={input} value={form.data.about_button_label} onChange={event => form.setData('about_button_label', event.target.value)}/></label><label className="block text-[12px] font-semibold">Enlace del botón<input className={input} value={form.data.about_button_url} onChange={event => form.setData('about_button_url', event.target.value)} placeholder="/nosotros, #sección o https://…"/></label></div>
                    </div>
                </div>
            </div>
            <div className="mt-5 flex justify-end"><button disabled={form.processing || !form.isDirty} className="inline-flex min-h-11 cursor-pointer items-center gap-2 rounded-lg bg-[#1954b7] px-5 text-[12px] font-semibold text-white transition hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{form.processing ? 'Guardando…' : 'Guardar cambios'}</button></div>
        </form>
    </AdminLayout>;
}
