import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Eye, Home, Image as ImageIcon, Save, UploadCloud } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const field = 'min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] text-[#273942] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';
const label = 'mb-2 block text-[12px] font-semibold text-[#334851]';

function Switch({ checked, onChange, label, description, icon: Icon }: any) {
    return <label className="flex min-h-[72px] cursor-pointer items-center justify-between gap-4 rounded-xl border border-[#e5eaed] bg-[#f8fafb] px-4">
        <span className="flex items-center gap-3"><Icon size={17} className="text-[#1954b7]"/><span><strong className="block text-[12px] text-[#263b45]">{label}</strong><small className="mt-1 block text-[10px] text-[#78858c]">{description}</small></span></span>
        <span className={`relative h-6 w-11 shrink-0 rounded-full transition ${checked ? 'bg-[#1954b7]' : 'bg-[#c9d1d5]'}`}><input type="checkbox" checked={checked} onChange={event => onChange(event.target.checked)} className="sr-only"/><span className={`absolute top-1 size-4 rounded-full bg-white shadow-sm transition-transform ${checked ? 'translate-x-6' : 'translate-x-1'}`}/></span>
    </label>;
}

export default function Edit({ category, parentOptions }: any) {
    const form = useForm<any>({_method: 'put', parent_id: category.parent_id || '', name: category.name, description: category.description || '', image_file: null, is_featured: !!category.is_featured, is_active: !!category.is_active, sort_order: category.sort_order || 'A'});
    const [preview, setPreview] = useState('');
    const [dimensions, setDimensions] = useState('');
    const isMainCategory = !form.data.parent_id;
    const selectedParent = parentOptions.find((option: any) => option.id === Number(form.data.parent_id));
    const effectiveImagePath = category.image || selectedParent?.effective_image || '';
    const displayedPreview = preview || (effectiveImagePath ? `/storage/${effectiveImagePath}` : '');
    const isInheritedImage = !preview && !category.image && !!selectedParent?.effective_image;
    useEffect(() => {
        if (!form.data.image_file) return setPreview('');
        const url = URL.createObjectURL(form.data.image_file);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.image_file]);

    return <AdminLayout title={`Editar · ${category.name}`}><Head title={`${category.name} | Categorías`}/>
        <div className="mb-6"><Link href="/admin/categories" className="inline-flex min-h-11 items-center gap-2 text-[12px] font-medium text-[#64747d] hover:text-[#132b3b]"><ArrowLeft size={16}/> Volver a categorías</Link><h1 className="mt-1 text-[24px] font-semibold text-[#132b3b]">{category.name}</h1><p className="mt-1 text-[11px] text-[#78858c]">{category.products_count} productos asociados</p></div>
        <form onSubmit={event => {event.preventDefault(); form.post(`/admin/categories/${category.id}`, {forceFormData: true});}} className="grid gap-6 xl:grid-cols-[360px_1fr]">
            <section className="rounded-2xl border border-[#dfe5e8] bg-white p-5">
                <h2 className="text-[13px] font-semibold text-[#132b3b]">Imagen de categoría</h2>
                <div className="mt-4 flex aspect-square items-center justify-center overflow-hidden rounded-xl border border-[#e1e7ea] bg-[#f6f8f9]">{displayedPreview ? <img src={displayedPreview} className="h-full w-full object-cover" alt="Vista previa" onLoad={event=>setDimensions(`${event.currentTarget.naturalWidth} × ${event.currentTarget.naturalHeight} px`)}/> : <ImageIcon size={36} className="text-[#a6b2b8]"/>}</div>
                <label className="mt-4 flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border border-dashed border-[#b8c7cf] text-[12px] font-semibold text-[#52616a] hover:border-[#1954b7] hover:text-[#1954b7]"><UploadCloud size={17}/> {category.image ? 'Reemplazar imagen propia' : isMainCategory ? 'Seleccionar imagen' : 'Subir imagen propia'}<input type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={event => form.setData('image_file', event.target.files?.[0] || null)}/></label>
                <p className="mt-2 text-[10px] leading-4 text-[#7b878e]"><strong>{isInheritedImage ? 'Heredada' : 'Actual'}:</strong> {displayedPreview ? dimensions || 'leyendo dimensiones…' : 'sin imagen'}.<br/><strong>Recomendado:</strong> 1200 × 1200 px (1:1).<br/>JPG, PNG o WebP · máximo 5 MB.{!isMainCategory && <><br/>Si no subís una imagen propia, se utilizará la de la categoría superior.</>}</p>
                {form.errors.image_file && <p className="mt-2 text-[11px] text-red-600">{form.errors.image_file}</p>}
            </section>
            <div className="space-y-6">
                <section className="rounded-2xl border border-[#dfe5e8] bg-white p-6"><label><span className={label}>Ubicación en la jerarquía</span><select className={field} value={form.data.parent_id} onChange={event => {form.setData('parent_id', event.target.value ? Number(event.target.value) : ''); if (event.target.value) form.setData('is_featured', false);}}><option value="">Categoría principal</option>{parentOptions.map((option:any) => <option key={option.id} value={option.id}>{option.level === 2 ? 'Subcategoría: ' : 'Dentro de: '}{option.path}</option>)}</select><small className="mt-1.5 block text-[10px] text-[#7b878e]">Todos los niveles pueden mostrar una tarjeta con nombre e imagen. En niveles internos, la imagen propia es opcional.</small></label><div className="mt-5 grid gap-5 md:grid-cols-[1fr_140px]"><label><span className={label}>Nombre</span><input className={field} value={form.data.name} onChange={event => form.setData('name', event.target.value)} required/></label><label><span className={label}>Orden</span><input type="text" maxLength={3} className={`${field} uppercase`} value={form.data.sort_order} onChange={event => form.setData('sort_order', event.target.value.toUpperCase().replace(/[^A-Z]/g, ''))}/><small className="mt-1.5 block text-[10px] text-[#7b878e]">A, AA, AB… AZ, B…</small></label></div><label className="mt-5 block"><span className={label}>Descripción</span><textarea rows={5} className={`${field} py-3`} value={form.data.description} onChange={event => form.setData('description', event.target.value)}/></label></section>
                <section className={`grid gap-3 ${isMainCategory ? 'md:grid-cols-2' : ''}`}><Switch icon={Eye} checked={form.data.is_active} onChange={(value: boolean) => form.setData('is_active', value)} label="Visible en la web" description="Ocultarla también impide publicar sus productos."/>{isMainCategory && <Switch icon={Home} checked={form.data.is_featured} onChange={(value: boolean) => form.setData('is_featured', value)} label="Destacada en Home" description="La categoría aparece en la página principal."/>}</section>
                <div className="flex justify-end"><button disabled={form.processing} className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#1954b7] px-7 text-[12px] font-semibold text-white hover:bg-[#132b3b] disabled:opacity-45"><Save size={16}/>{form.processing ? 'GUARDANDO…' : 'GUARDAR CAMBIOS'}</button></div>
                {Object.keys(form.errors).length > 0 && <div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-[11px] text-red-700">{Object.values(form.errors).map((error: any) => <p key={error}>{error}</p>)}</div>}
            </div>
        </form>
    </AdminLayout>;
}
