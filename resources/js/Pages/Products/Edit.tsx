import { Head, Link, router, useForm } from '@inertiajs/react';
import { ArrowLeft, CheckCircle2, ChevronDown, FileText, Image, Package, Play, Plus, Save, Trash2, UploadCloud } from 'lucide-react';
import { useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const field = 'min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] text-[#273942] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';
const label = 'mb-2 block text-[12px] font-semibold text-[#334851]';
const smallField = 'min-h-11 w-full rounded-xl border border-[#dfe5e8] bg-white px-3 text-[12px] text-[#273942] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';

function UploadField({ title, hint, accept, multiple=false, onChange, name }: any) {
    return <label className="flex min-h-[94px] cursor-pointer items-center gap-4 rounded-xl border border-dashed border-[#b8c7cf] bg-[#f8fafb] px-5 transition hover:border-[#1954b7] hover:bg-[#f3f7ff]">
        <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-white text-[#1954b7] shadow-sm"><UploadCloud size={20}/></span>
        <span className="min-w-0 grow"><strong className="block text-[12px] text-[#263b45]">{name || title}</strong><small className="mt-1 block text-[10px] text-[#7a878e]">{hint}</small></span>
        <input type="file" accept={accept} multiple={multiple} className="hidden" onChange={onChange}/>
    </label>;
}

const emptyVariant = (sortOrder = 0) => ({
    code: '', detail: '', tube: '', thread: '', quality: '', list_price: '', discount_percent: 0,
    final_price: '', physical_stock: '', transit_stock: '', sort_order: sortOrder, is_active: true,
});

function VariantFields({ data, setData, errors }: any) {
    const inputs = [
        ['code', 'Código *', 'AL-FSS-200-1-2'], ['detail', 'Detalle', 'Recto Macho'],
        ['tube', 'Tubo', '1/8"'], ['thread', 'Rosca', '1/8"'], ['quality', 'Calidad', 'AISI 316'],
        ['list_price', 'Precio lista U$S', '0.00'], ['discount_percent', 'Descuento %', '0'],
        ['final_price', 'Precio final U$S', '0.00'], ['physical_stock', 'Stock físico', '0'],
        ['transit_stock', 'Stock en tránsito', '0'], ['sort_order', 'Orden interno', '0'],
    ];
    return <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
        {inputs.map(([name, text, placeholder]) => <label key={name}>
            <span className={label}>{text}</span>
            <input
                type={['list_price','discount_percent','final_price','physical_stock','transit_stock','sort_order'].includes(name) ? 'number' : 'text'}
                step={['list_price','discount_percent','final_price'].includes(name) ? '0.01' : undefined}
                min={['discount_percent','physical_stock','transit_stock','sort_order'].includes(name) ? 0 : undefined}
                max={name === 'discount_percent' ? 100 : undefined}
                className={smallField}
                placeholder={placeholder}
                value={data[name] ?? ''}
                onChange={event => setData(name, event.target.value)}
            />
            {errors[name] && <small className="mt-1 block text-[10px] text-red-600">{errors[name]}</small>}
        </label>)}
        <label className="flex min-h-11 cursor-pointer items-center justify-between self-end rounded-xl bg-[#f4f8fa] px-4 text-[12px]">
            <span><strong className="block text-[#263b45]">Visible públicamente</strong><small className="text-[10px] text-[#7a878e]">Aparece en la ficha y PDF.</small></span>
            <span className={`relative h-6 w-11 rounded-full ${data.is_active ? 'bg-[#1954b7]' : 'bg-[#c9d1d5]'}`}>
                <input type="checkbox" checked={!!data.is_active} onChange={event => setData('is_active', event.target.checked)} className="sr-only"/>
                <span className={`absolute top-1 size-4 rounded-full bg-white transition-transform ${data.is_active ? 'translate-x-6' : 'translate-x-1'}`}/>
            </span>
        </label>
    </div>;
}

function NewVariant({ productId, nextSortOrder }: any) {
    const form = useForm<any>(emptyVariant(nextSortOrder));
    const submit = () => form.post(`/admin/products/${productId}/variants`, {
        preserveScroll: true,
        onSuccess: () => form.setData(emptyVariant(nextSortOrder + 1)),
    });
    return <details className="group rounded-xl border border-dashed border-[#9fb4c0] bg-[#f8fafb]">
        <summary className="flex min-h-14 cursor-pointer list-none items-center justify-between gap-3 px-5">
            <span className="flex items-center gap-3 text-[12px] font-semibold text-[#1954b7]"><Plus size={17}/> Agregar un código</span>
            <ChevronDown size={17} className="text-[#6f7f87] transition group-open:rotate-180"/>
        </summary>
        <div className="border-t border-[#e4eaed] p-5">
            <VariantFields data={form.data} setData={form.setData} errors={form.errors}/>
            <div className="mt-5 flex justify-end"><button type="button" disabled={form.processing} onClick={submit} className="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#132b3b] px-5 text-[11px] font-semibold text-white disabled:opacity-50"><Plus size={15}/> CREAR CÓDIGO</button></div>
        </div>
    </details>;
}

function VariantEditor({ variant, productId }: any) {
    const form = useForm<any>({
        code: variant.code || '', detail: variant.detail || '', tube: variant.tube || '', thread: variant.thread || '',
        quality: variant.quality || '', list_price: variant.list_price ?? '', discount_percent: variant.discount_percent ?? 0,
        final_price: variant.final_price ?? '', physical_stock: variant.physical_stock ?? '',
        transit_stock: variant.transit_stock ?? '', sort_order: variant.sort_order ?? 0, is_active: !!variant.is_active,
    });
    const save = () => form.put(`/admin/products/${productId}/variants/${variant.id}`, { preserveScroll: true });
    const remove = () => {
        if (window.confirm(`¿Eliminar definitivamente el código ${variant.code}?`)) {
            router.delete(`/admin/products/${productId}/variants/${variant.id}`, { preserveScroll: true });
        }
    };
    return <details className="group overflow-hidden rounded-xl border border-[#dfe5e8] bg-white">
        <summary className="grid min-h-16 cursor-pointer list-none grid-cols-[minmax(0,1fr)_auto] items-center gap-4 px-5 hover:bg-[#f8fafb]">
            <span className="min-w-0"><strong className="block truncate text-[13px] text-[#132b3b]">{variant.code}</strong><small className="mt-1 block truncate text-[10px] text-[#7a878e]">{variant.detail || 'Sin detalle'} · Tubo {variant.tube || '—'} · Rosca {variant.thread || '—'}</small></span>
            <span className="flex items-center gap-3"><span className={`rounded-full px-3 py-1 text-[9px] font-semibold ${variant.is_active ? 'bg-[#edf8f3] text-[#23835f]' : 'bg-[#f1f3f4] text-[#68767d]'}`}>{variant.is_active ? 'PUBLICADO' : 'OCULTO'}</span><ChevronDown size={17} className="text-[#6f7f87] transition group-open:rotate-180"/></span>
        </summary>
        <div className="border-t border-[#edf0f2] bg-[#fbfcfc] p-5">
            <VariantFields data={form.data} setData={form.setData} errors={form.errors}/>
            <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e5eaed] pt-5">
                <button type="button" onClick={remove} className="inline-flex min-h-11 items-center gap-2 rounded-xl px-4 text-[11px] font-semibold text-red-600 hover:bg-red-50"><Trash2 size={15}/> Eliminar código</button>
                <button type="button" disabled={form.processing || !form.isDirty} onClick={save} className="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#1954b7] px-5 text-[11px] font-semibold text-white disabled:opacity-40"><Save size={15}/>{form.processing ? 'GUARDANDO…' : 'GUARDAR CÓDIGO'}</button>
            </div>
        </div>
    </details>;
}

function RelatedProductsEditor({ product, candidates }: any) {
    const relatedForm = useForm<any>({
        related_product_ids: (product.related_products || []).map((related: any) => related.id),
    });
    const selectedIds: number[] = relatedForm.data.related_product_ids || [];

    const toggle = (id: number) => {
        const isSelected = selectedIds.includes(id);
        relatedForm.setData('related_product_ids', isSelected
            ? selectedIds.filter(selectedId => selectedId !== id)
            : [...selectedIds, id]);
    };

    const save = () => relatedForm.put(`/admin/products/${product.id}/related`, { preserveScroll: true });

    return <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
        <header className="flex flex-wrap items-center justify-between gap-4 border-b border-[#edf0f2] px-6 py-5">
            <div className="flex items-center gap-3"><Package size={18} className="text-[#1954b7]"/><div><h2 className="text-[14px] font-semibold text-[#132b3b]">Productos relacionados</h2><p className="mt-1 text-[10px] text-[#7a878e]">Elegí todos los productos que deban aparecer como cards al final de esta ficha pública.</p></div></div>
            <span className="rounded-full bg-[#edf3ff] px-3 py-1.5 text-[10px] font-semibold text-[#1954b7]">{selectedIds.length} seleccionados</span>
        </header>
        <div className="p-6">
            {!candidates.length ? <div className="rounded-xl bg-[#f8fafb] px-5 py-8 text-center text-[11px] text-[#7a878e]">No hay otros productos publicados disponibles para relacionar.</div> : <div className="grid gap-3 sm:grid-cols-2">
                {candidates.map((candidate: any) => {
                    const isSelected = selectedIds.includes(candidate.id);
                    return <button key={candidate.id} type="button" aria-pressed={isSelected} onClick={() => toggle(candidate.id)} className={`group flex min-h-[92px] cursor-pointer items-center gap-4 rounded-xl border p-3 text-left transition duration-200 active:scale-[.99] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#1954b7]/15 ${isSelected ? 'border-[#1954b7] bg-[#f3f7ff] shadow-[0_8px_20px_rgba(25,84,183,.08)]' : 'border-[#dfe5e8] bg-white hover:border-[#9fb9d9] hover:bg-[#f8fafb]'}`}>
                        <span className="flex size-16 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[#f5f7f8]"><img src={`/storage/${candidate.main_image || 'products/product-placeholder.svg'}`} className="max-h-full max-w-full object-contain" alt=""/></span>
                        <span className="min-w-0 grow"><strong className="block truncate text-[12px] text-[#132b3b]">{candidate.name}</strong><small className="mt-1 block truncate text-[10px] text-[#7a878e]">{candidate.category?.name || 'Sin categoría'}</small></span>
                        <span className={`grid size-5 shrink-0 place-items-center rounded-md border transition ${isSelected ? 'border-[#1954b7] bg-[#1954b7] text-white' : 'border-[#bdc9cf] bg-white text-transparent'}`}><CheckCircle2 size={13}/></span>
                    </button>;
                })}
            </div>}
            <div className="mt-5 flex flex-wrap items-center justify-between gap-3 border-t border-[#e5eaed] pt-5">
                <p className="text-[10px] leading-4 text-[#7a878e]">Solo aparecen productos activos dentro de categorías publicadas. Todos los seleccionados se imprimen en el sitio, en el orden elegido.</p>
                <button type="button" disabled={relatedForm.processing || !relatedForm.isDirty} onClick={save} className="inline-flex min-h-11 items-center gap-2 rounded-xl bg-[#1954b7] px-5 text-[11px] font-semibold text-white transition hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-45"><Save size={15}/>{relatedForm.processing ? 'GUARDANDO…' : 'GUARDAR RELACIONADOS'}</button>
            </div>
            {relatedForm.errors.related_product_ids && <p role="alert" className="mt-3 text-[11px] font-medium text-red-600">{relatedForm.errors.related_product_ids}</p>}
        </div>
    </section>;
}

export default function Edit({ product, categories, relatedCandidates }: any) {
    const form = useForm<any>({
        _method: 'put', category_id: product.category_id, name: product.name, sku: product.sku||'', summary: product.summary||'',
        description: product.description||'', main_image_file: null, gallery_files: [],
        video_type: product.video_type||'', video_file: null, youtube_url: product.youtube_url||'',
        datasheet_file: null, is_featured: !!product.is_featured, is_active: !!product.is_active, sort_order: product.sort_order||'A',
    });
    const [imageDimensions, setImageDimensions] = useState('');
    const [videoDimensions, setVideoDimensions] = useState('');
    const imagePreview = form.data.main_image_file ? URL.createObjectURL(form.data.main_image_file) : `/storage/${product.main_image||'products/product-placeholder.svg'}`;
    const videoPreview = form.data.video_file ? URL.createObjectURL(form.data.video_file) : (product.video_path ? `/storage/${product.video_path}` : '');

    return <AdminLayout title={`Editar · ${product.name}`}><Head title={`${product.name} | Productos`}/>
        <div className="mb-6 flex flex-col justify-between gap-4 sm:flex-row sm:items-center"><div><Link href="/admin/products" className="inline-flex min-h-11 items-center gap-2 text-[12px] font-medium text-[#64747d] hover:text-[#132b3b]"><ArrowLeft size={16}/> Volver a productos</Link><h1 className="mt-1 text-[24px] font-semibold text-[#132b3b]">{product.name}</h1></div><span className={`inline-flex items-center gap-2 self-start rounded-full px-4 py-2 text-[11px] font-semibold ${form.data.is_active ? 'bg-[#edf8f3] text-[#23835f]' : 'bg-[#f1f3f4] text-[#69767c]'}`}><CheckCircle2 size={14}/> {form.data.is_active ? 'Visible en la web' : 'Oculto en la web'}</span></div>
        <form onSubmit={e=>{e.preventDefault();form.post(`/admin/products/${product.id}`,{forceFormData:true})}} className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div className="space-y-6">
                <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><Package size={18} className="text-[#1954b7]"/><div><h2 className="text-[14px] font-semibold text-[#132b3b]">Información del producto</h2><p className="mt-1 text-[10px] text-[#7a878e]">Identidad, clasificación y contenido público.</p></div></header><div className="grid gap-5 p-6 md:grid-cols-2">
                    <label><span className={label}>Nombre del producto</span><input className={field} value={form.data.name} onChange={e=>form.setData('name',e.target.value)}/></label>
                    <label><span className={label}>SKU general</span><input className={field} value={form.data.sku} onChange={e=>form.setData('sku',e.target.value)}/></label>
                    <label><span className={label}>Orden</span><input type="text" maxLength={3} className={`${field} uppercase`} value={form.data.sort_order} onChange={e=>form.setData('sort_order',e.target.value.toUpperCase().replace(/[^A-Z]/g,''))}/><small className="mt-1.5 block text-[10px] font-normal text-[#7b878e]">Secuencia: A, AA, AB… AZ, B, BA…</small></label>
                    <label className="md:col-span-2"><span className={label}>Resumen</span><textarea rows={3} className={`${field} py-3`} value={form.data.summary} onChange={e=>form.setData('summary',e.target.value)}/></label>
                    <label className="md:col-span-2"><span className={label}>Descripción</span><textarea rows={6} className={`${field} py-3`} value={form.data.description} onChange={e=>form.setData('description',e.target.value)}/></label>
                </div></section>
                <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><Play size={18} className="text-[#1954b7]"/><div><h2 className="text-[14px] font-semibold text-[#132b3b]">Video del producto · 360°</h2><p className="mt-1 text-[10px] text-[#7a878e]">Cada producto puede tener su propio MP4/WebM o video de YouTube.</p></div></header><div className="p-6">
                    <div className="mb-5 grid grid-cols-3 gap-3">{[['','Sin video'],['upload','Archivo'],['youtube','YouTube']].map(([value,text])=><button key={value} type="button" onClick={()=>form.setData('video_type',value)} className={`min-h-12 rounded-xl border text-[12px] font-semibold transition ${form.data.video_type===value?'border-[#1954b7] bg-[#edf3ff] text-[#1954b7]':'border-[#dfe5e8] text-[#718087] hover:bg-[#f8fafb]'}`}>{text}</button>)}</div>
                    {form.data.video_type==='upload'&&<><UploadField title="Seleccionar video" name={form.data.video_file?.name} hint="Recomendado: 1920 × 1080 px (16:9) · MP4/WebM/MOV · máximo 100 MB" accept="video/mp4,video/webm,video/quicktime" onChange={(e:any)=>form.setData('video_file',e.target.files?.[0]||null)}/>{videoPreview&&<><video className="mt-4 aspect-video w-full rounded-xl bg-[#07131b] object-contain" controls src={videoPreview} onLoadedMetadata={event=>setVideoDimensions(`${event.currentTarget.videoWidth} × ${event.currentTarget.videoHeight} px · ${Math.round(event.currentTarget.duration)} s`)}/><p className="mt-2 text-[10px] text-[#7a878e]"><strong>Archivo actual:</strong> {videoDimensions || 'leyendo dimensiones…'}</p></>}</>}
                    {form.data.video_type==='youtube'&&<label><span className={label}>URL embebida de YouTube</span><input className={field} placeholder="https://www.youtube.com/embed/..." value={form.data.youtube_url} onChange={e=>form.setData('youtube_url',e.target.value)}/></label>}
                </div></section>
                <section id="codigos" className="scroll-mt-24 overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
                    <header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><FileText size={18} className="text-[#1954b7]"/><div><h2 className="text-[14px] font-semibold text-[#132b3b]">Códigos del producto</h2><p className="mt-1 text-[10px] text-[#7a878e]">{product.variants.length} variantes dentro de {product.name}. Abrí cada una para editar todos sus campos.</p></div></header>
                    <div className="space-y-3 p-6">
                        <NewVariant productId={product.id} nextSortOrder={product.variants.length}/>
                        {product.variants.map((variant:any) => <VariantEditor key={variant.id} variant={variant} productId={product.id}/>)}
                        {!product.variants.length && <div className="rounded-xl bg-[#f8fafb] px-5 py-8 text-center text-[11px] text-[#7a878e]">Este producto todavía no tiene códigos. Agregá el primero arriba.</div>}
                    </div>
                </section>
                <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
                    <header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><FileText size={18} className="text-[#1954b7]"/><div><h2 className="text-[14px] font-semibold text-[#132b3b]">Ficha técnica PDF</h2><p className="mt-1 text-[10px] text-[#7a878e]">Podés adjuntar una ficha propia; si no, se genera automáticamente con los códigos publicados.</p></div></header>
                    <div className="p-6"><UploadField title="Reemplazar ficha técnica" name={form.data.datasheet_file?.name} hint="PDF en formato A4 recomendado · máximo 20 MB." accept="application/pdf" onChange={(e:any)=>form.setData('datasheet_file',e.target.files?.[0]||null)}/>{product.datasheet&&<p className="mt-2 text-[10px] text-[#7a878e]"><strong>Archivo actual:</strong> {product.datasheet.split('/').pop()}</p>}</div>
                </section>
                <RelatedProductsEditor product={product} candidates={relatedCandidates || []}/>
            </div>
            <aside className="space-y-6">
                <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-5 py-4"><Image size={17} className="text-[#1954b7]"/><h2 className="text-[13px] font-semibold text-[#132b3b]">Imagen principal</h2></header><div className="p-5"><div className="mb-4 flex aspect-square items-center justify-center overflow-hidden rounded-xl bg-[#f5f7f8]"><img src={imagePreview} className="max-h-full max-w-full object-contain" alt="Vista previa" onLoad={event=>setImageDimensions(`${event.currentTarget.naturalWidth} × ${event.currentTarget.naturalHeight} px`)}/></div><UploadField title="Reemplazar imagen" name={form.data.main_image_file?.name} hint="Recomendado: 1200 × 1200 px (1:1) · JPG, PNG o WebP · máximo 5 MB" accept="image/jpeg,image/png,image/webp" onChange={(e:any)=>form.setData('main_image_file',e.target.files?.[0]||null)}/><p className="mt-2 text-[10px] text-[#7a878e]"><strong>Imagen actual:</strong> {imageDimensions || 'leyendo dimensiones…'}</p></div></section>
                <section className="rounded-2xl border border-[#dfe5e8] bg-white p-5"><h2 className="text-[13px] font-semibold text-[#132b3b]">Galería</h2><p className="mt-1 text-[10px] leading-4 text-[#7a878e]">Hasta 8 imágenes. Recomendado: 1200 × 1200 px (1:1). JPG, PNG o WebP · máximo 5 MB cada una.</p><div className="mt-4"><UploadField title="Agregar imágenes" name={form.data.gallery_files.length ? `${form.data.gallery_files.length} imágenes seleccionadas` : undefined} hint="Selección múltiple · se agregan a la galería actual" accept="image/jpeg,image/png,image/webp" multiple onChange={(e:any)=>form.setData('gallery_files',Array.from(e.target.files||[]))}/></div></section>
                <section className="rounded-2xl border border-[#dfe5e8] bg-white p-5"><h2 className="text-[13px] font-semibold text-[#132b3b]">Publicación</h2><label className="mt-4 flex min-h-14 cursor-pointer items-center justify-between rounded-xl bg-[#f8fafb] px-4 text-[12px]"><span><strong className="block">Visible en la web</strong><small className="mt-1 block text-[10px] text-[#7a878e]">Si se desactiva, no podrá abrirse en el sitio público.</small></span><span className={`relative h-6 w-11 rounded-full ${form.data.is_active?'bg-[#1954b7]':'bg-[#c9d1d5]'}`}><input type="checkbox" checked={form.data.is_active} onChange={e=>form.setData('is_active',e.target.checked)} className="sr-only"/><span className={`absolute top-1 size-4 rounded-full bg-white transition-transform ${form.data.is_active?'translate-x-6':'translate-x-1'}`}/></span></label><label className="mt-3 flex min-h-14 cursor-pointer items-center justify-between rounded-xl bg-[#f8fafb] px-4 text-[12px]"><span><strong className="block">Destacado en Home</strong><small className="mt-1 block text-[10px] text-[#7a878e]">Sólo se publica si el producto y su categoría están visibles.</small></span><span className={`relative h-6 w-11 rounded-full ${form.data.is_featured?'bg-[#1954b7]':'bg-[#c9d1d5]'}`}><input type="checkbox" checked={form.data.is_featured} onChange={e=>form.setData('is_featured',e.target.checked)} className="sr-only"/><span className={`absolute top-1 size-4 rounded-full bg-white transition-transform ${form.data.is_featured?'translate-x-6':'translate-x-1'}`}/></span></label></section>
                <button disabled={form.processing} className="flex min-h-13 w-full items-center justify-center gap-2 rounded-xl bg-[#1954b7] px-6 text-[12px] font-semibold text-white transition hover:bg-[#132b3b] disabled:opacity-45"><Save size={16}/>{form.processing?'Guardando…':'GUARDAR PRODUCTO'}</button>
                {Object.keys(form.errors).length>0&&<div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-[11px] text-red-700">{Object.values(form.errors).map((error:any)=><p key={error}>{error}</p>)}</div>}
            </aside>
        </form>
    </AdminLayout>;
}
