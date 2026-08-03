import { Head, Link, router, useForm } from '@inertiajs/react';
import { Eye, EyeOff, Home, Image as ImageIcon, Pencil, Plus, Trash2, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const field = 'min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] text-[#273942] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';
const label = 'mb-2 block text-[12px] font-semibold text-[#334851]';

function Switch({ checked, onChange, label, description }: { checked: boolean; onChange: (value: boolean) => void; label: string; description: string }) {
    return <label className="flex min-h-[68px] cursor-pointer items-center justify-between gap-4 rounded-xl border border-[#e5eaed] bg-[#f8fafb] px-4">
        <span><strong className="block text-[12px] text-[#263b45]">{label}</strong><small className="mt-1 block text-[10px] text-[#78858c]">{description}</small></span>
        <span className={`relative h-6 w-11 shrink-0 rounded-full transition ${checked ? 'bg-[#1954b7]' : 'bg-[#c9d1d5]'}`}>
            <input type="checkbox" checked={checked} onChange={event => onChange(event.target.checked)} className="sr-only"/>
            <span className={`absolute top-1 size-4 rounded-full bg-white shadow-sm transition-transform ${checked ? 'translate-x-6' : 'translate-x-1'}`}/>
        </span>
    </label>;
}

export default function Index({ categories, nextOrder }: { categories: any[]; nextOrder: string }) {
    const form = useForm<any>({ name: '', description: '', image_file: null, is_featured: false, is_active: true, sort_order: nextOrder });
    const [preview, setPreview] = useState('');
    const [dimensions, setDimensions] = useState('');

    useEffect(() => {
        if (!form.data.image_file) return setPreview('');
        const url = URL.createObjectURL(form.data.image_file);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.image_file]);

    return <AdminLayout title="Categorías"><Head title="Categorías | Fitinox"/>
        <form onSubmit={event => {
            event.preventDefault();
            form.post('/admin/categories', {
                forceFormData: true,
                onSuccess: () => {
                    form.reset();
                    form.setData('sort_order', nextOrder);
                    setPreview('');
                },
            });
        }} className="mb-7 overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white shadow-[0_10px_35px_rgba(19,43,59,.05)]">
            <header className="flex items-center gap-4 border-b border-[#edf0f2] px-6 py-5">
                <span className="grid size-11 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><Plus size={20}/></span>
                <div><h2 className="text-[15px] font-semibold text-[#132b3b]">Nueva categoría</h2><p className="mt-1 text-[11px] text-[#758189]">Crea la tarjeta pública y define si también aparece en la Home.</p></div>
            </header>
            <div className="grid gap-6 p-6 lg:grid-cols-[280px_1fr]">
                <div>
                    <div className="flex aspect-square items-center justify-center overflow-hidden rounded-xl border border-[#e1e7ea] bg-[#f6f8f9]">
                        {preview ? <img src={preview} alt="Vista previa de la categoría" className="h-full w-full object-cover" onLoad={event=>setDimensions(`${event.currentTarget.naturalWidth} × ${event.currentTarget.naturalHeight} px`)}/> : <ImageIcon size={36} className="text-[#a6b2b8]"/>}
                    </div>
                    <label className="mt-3 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-[#b8c7cf] text-[12px] font-semibold text-[#52616a] transition hover:border-[#1954b7] hover:text-[#1954b7]">
                        <UploadCloud size={17}/> {form.data.image_file?.name || 'Seleccionar imagen'}
                        <input type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={event => form.setData('image_file', event.target.files?.[0] || null)}/>
                    </label>
                    <p className="mt-2 text-[10px] leading-4 text-[#7b878e]">{preview && <><strong>Seleccionada:</strong> {dimensions || 'leyendo dimensiones…'}.<br/></>}<strong>Recomendado:</strong> 1200 × 1200 px (1:1).<br/>JPG, PNG o WebP · máximo 5 MB.</p>
                    {form.errors.image_file && <p className="mt-2 text-[11px] text-red-600">{form.errors.image_file}</p>}
                </div>
                <div className="grid content-start gap-5">
                    <div className="grid gap-5 md:grid-cols-[1fr_140px]">
                        <label><span className={label}>Nombre de la categoría</span><input className={field} value={form.data.name} onChange={event => form.setData('name', event.target.value)} required/></label>
                        <label><span className={label}>Orden</span><input type="text" maxLength={3} className={`${field} uppercase`} value={form.data.sort_order} onChange={event => form.setData('sort_order', event.target.value.toUpperCase().replace(/[^A-Z]/g, ''))}/><small className="mt-1.5 block text-[10px] text-[#7b878e]">A, AA, AB… AZ, B, BA…</small></label>
                    </div>
                    <label><span className={label}>Descripción</span><textarea rows={4} className={`${field} py-3`} value={form.data.description} onChange={event => form.setData('description', event.target.value)} placeholder="Descripción breve para administración y SEO."/></label>
                    <div className="grid gap-3 md:grid-cols-2">
                        <Switch checked={form.data.is_active} onChange={value => form.setData('is_active', value)} label="Visible en la web" description="Si se desactiva, la categoría y sus productos dejan de publicarse."/>
                        <Switch checked={form.data.is_featured} onChange={value => form.setData('is_featured', value)} label="Destacada en Home" description="Muestra esta categoría dentro del bloque de productos de la página principal."/>
                    </div>
                    <div className="flex justify-end"><button disabled={form.processing} className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#1954b7] px-6 text-[12px] font-semibold text-white transition hover:bg-[#132b3b] disabled:opacity-45"><Plus size={16}/>{form.processing ? 'CREANDO…' : 'CREAR CATEGORÍA'}</button></div>
                    {Object.keys(form.errors).length > 0 && <div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-[11px] text-red-700">{Object.values(form.errors).map((error: any) => <p key={error}>{error}</p>)}</div>}
                </div>
            </div>
        </form>

        <div className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
            <table className="w-full text-left text-[12px]">
                <thead className="bg-[#132b3b] text-white"><tr><th className="px-6 py-4">Categoría</th><th>Productos</th><th>Home</th><th>Publicación</th><th className="pr-6 text-right">Acciones</th></tr></thead>
                <tbody>{categories.map(category => <tr key={category.id} className="border-t border-[#edf0f2]">
                    <td className="px-6 py-4"><div className="flex items-center gap-4"><div className="grid size-14 shrink-0 place-items-center overflow-hidden rounded-xl border border-[#e5eaed] bg-[#f5f7f8]">{category.image ? <img src={`/storage/${category.image}`} className="h-full w-full object-cover" alt=""/> : <ImageIcon size={20} className="text-[#9ba8ae]"/>}</div><div><strong className="block text-[13px] text-[#263b45]">{category.name}</strong><small className="mt-1 block text-[#829097]">Orden {category.sort_order}</small></div></div></td>
                    <td>{category.products_count}</td>
                    <td><span className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold ${category.is_featured ? 'bg-blue-50 text-[#1954b7]' : 'bg-[#f1f3f4] text-[#728087]'}`}><Home size={12}/>{category.is_featured ? 'Destacada' : 'No destacada'}</span></td>
                    <td><span className={`inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-[10px] font-semibold ${category.is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-[#f1f3f4] text-[#728087]'}`}>{category.is_active ? <Eye size={12}/> : <EyeOff size={12}/>} {category.is_active ? 'Visible' : 'Oculta'}</span></td>
                    <td className="pr-6 text-right"><div className="inline-flex gap-2"><Link aria-label={`Editar ${category.name}`} href={`/admin/categories/${category.id}/edit`} className="grid size-10 place-items-center rounded-lg border border-[#dfe5e8] text-[#132b3b] hover:bg-[#edf3f7]"><Pencil size={16}/></Link><button aria-label={`Eliminar ${category.name}`} onClick={() => confirm('¿Eliminar categoría?') && router.delete(`/admin/categories/${category.id}`)} className="grid size-10 place-items-center rounded-lg border border-red-100 text-red-600 hover:bg-red-50"><Trash2 size={16}/></button></div></td>
                </tr>)}</tbody>
            </table>
        </div>
    </AdminLayout>;
}
