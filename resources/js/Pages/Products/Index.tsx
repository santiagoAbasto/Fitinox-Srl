import { Head, router, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { CheckCircle2, Download, FileImage, FileSpreadsheet, Film, FolderTree, Pencil, Search, Trash2, UploadCloud } from 'lucide-react';
import { Link } from '@inertiajs/react';
import AdminLayout from '../../Components/AdminLayout';

const input = 'w-full rounded-md border border-gray-200 px-3 py-2 text-sm outline-none focus:border-fitinox';

type MediaKind = 'images' | 'videos';
type MediaResult = { processed:number; matched:number; unmatched:number; replaced:number; files:Array<{name:string;status:string}> };

function MediaBatchUploader({ kind }: { kind: MediaKind }) {
    const isImage = kind === 'images';
    const [files, setFiles] = useState<File[]>([]);
    const [running, setRunning] = useState(false);
    const [completed, setCompleted] = useState(0);
    const [result, setResult] = useState<MediaResult|null>(null);
    const [error, setError] = useState('');

    const run = async () => {
        if (!files.length || running) return;
        setRunning(true); setCompleted(0); setError(''); setResult({ processed: 0, matched: 0, unmatched: 0, replaced: 0, files: [] });
        const batches: File[][] = [];
        const maxBatchBytes = 75 * 1024 * 1024;
        let batch: File[] = []; let bytes = 0;
        for (const file of files) {
            if (batch.length && (batch.length === 20 || bytes + file.size > maxBatchBytes)) {
                batches.push(batch); batch = []; bytes = 0;
            }
            batch.push(file); bytes += file.size;
        }
        if (batch.length) batches.push(batch);

        const total: MediaResult = { processed: 0, matched: 0, unmatched: 0, replaced: 0, files: [] };
        try {
            for (const group of batches) {
                const body = new FormData();
                group.forEach(file => body.append('files[]', file));
                const response = await fetch(`/admin/products/import/media/${kind}`, {
                    method: 'POST', body, headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')?.content || '',
                    },
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || Object.values(payload.errors || {}).flat().join(' ') || 'No se pudo procesar el bloque.');
                total.processed += payload.processed; total.matched += payload.matched; total.unmatched += payload.unmatched;
                total.replaced += payload.replaced || 0;
                total.files.push(...payload.files); setCompleted(value => value + group.length); setResult({ ...total, files: [...total.files] });
            }
            router.reload({ only: ['products'], preserveScroll: true });
        } catch (exception:any) {
            setError(exception.message || 'La carga se interrumpió. Podés volver a ejecutarla.');
        } finally { setRunning(false); }
    };

    const Icon = isImage ? FileImage : Film;
    const unmatched = result?.files.filter(file => file.status === 'unmatched') || [];
    return <div className="min-w-0 p-6">
        <div className="mb-4 flex items-start gap-3"><span className="grid size-10 shrink-0 place-items-center rounded-lg bg-[#edf3f7] text-[#1954b7]"><Icon size={19}/></span><div><h3 className="text-[14px] font-semibold text-[#132b3b]">{isImage?'Fotos de productos':'Videos de productos'}</h3><p className="mt-1 max-w-[62ch] text-[11px] leading-5 text-[#65747c]">Seleccioná todos los archivos juntos. El sistema los envía automáticamente en bloques de hasta 20 y relaciona el nombre ignorando la extensión.</p><p className="mt-2 inline-flex rounded-lg bg-[#edf3f7] px-3 py-1.5 text-[10px] font-semibold text-[#1954b7]">TAMAÑO RECOMENDADO: 496 × 496 PX (1:1)</p></div></div>
        <label className="flex min-h-16 cursor-pointer items-center gap-3 rounded-xl border border-dashed border-[#b8c7cf] bg-[#f8fafb] px-4 transition hover:border-[#1954b7] focus-within:border-[#1954b7]"><UploadCloud size={19} className="shrink-0 text-[#1954b7]"/><span className="min-w-0 grow truncate text-[11px] text-[#52616a]">{files.length ? `${files.length} archivo${files.length===1?'':'s'} seleccionado${files.length===1?'':'s'}` : `Seleccionar ${isImage?'fotos JPG, PNG o WebP':'videos MP4, WebM o MOV'}`}</span><input type="file" multiple accept={isImage?'image/jpeg,image/png,image/webp':'video/mp4,video/webm,video/quicktime'} className="sr-only" onChange={event=>{setFiles(Array.from(event.target.files||[]));setResult(null);setCompleted(0);setError('')}}/></label>
        {running && <div className="mt-4" aria-live="polite"><div className="mb-2 flex justify-between text-[10px] font-semibold text-[#52616a]"><span>Procesando por bloques…</span><span>{completed} / {files.length}</span></div><div className="h-2 overflow-hidden rounded-full bg-[#e7edf0]"><div className="h-full bg-[#1954b7] transition-[width] duration-200" style={{width:`${files.length ? (completed/files.length)*100 : 0}%`}}/></div></div>}
        {result && !running && <div className="mt-4 rounded-xl bg-[#f3f8f5] px-4 py-3 text-[11px] text-[#315e4b]" aria-live="polite"><strong>{result.matched} relacionados</strong> · {result.replaced} reemplazados · {result.unmatched} sin coincidencia{unmatched.length>0&&<p className="mt-2 break-words text-[#8c4c24]">Sin match: {unmatched.slice(0,8).map(file=>file.name).join(', ')}{unmatched.length>8?'…':''}</p>}</div>}
        {error && <p className="mt-3 text-[11px] text-red-700" role="alert">{error}</p>}
        <button type="button" onClick={run} disabled={!files.length||running} className="mt-4 min-h-11 w-full rounded-xl bg-[#132b3b] px-5 text-[11px] font-semibold text-white transition hover:bg-[#1954b7] focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#1954b7] disabled:cursor-not-allowed disabled:opacity-40">{running?'PROCESANDO…':`SUBIR ${isImage?'FOTOS':'VIDEOS'}`}</button>
    </div>;
}

export default function Index({ products, categories, filters, nextOrder }: { products: any, categories: any[], filters: any, nextOrder: string }) {
    const report = (usePage().props as any).flash?.import_report;
    const form = useForm<any>({ category_id: categories[0]?.id || '', name: '', sku: '', summary: '', description: '', main_image_file: null, video_type: '', video_file: null, youtube_url: '', is_featured: false, is_active: true, sort_order: nextOrder });
    const importForm = useForm<{file: File|null}>({ file: null });
    const [showClearInventory, setShowClearInventory] = useState(false);
    const clearForm = useForm<{confirmation:string}>({ confirmation: '' });
    return <AdminLayout title="Productos"><Head title="Productos | Fitinox"/>
        <section id="importar" className="mb-7 scroll-mt-28 overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white shadow-[0_10px_35px_rgba(19,43,59,.05)]">
            <div className="flex flex-wrap items-center gap-4 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-11 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><FileSpreadsheet size={20}/></span><div className="min-w-0 grow"><h2 className="text-[15px] font-semibold text-[#132b3b]">Importación masiva</h2><p className="mt-1 text-[11px] leading-5 text-[#758189]">El Excel sincroniza el catálogo completo: actualiza cada código existente, elimina los que ya no estén y ordena las tarjetas según las filas de la planilla. Las columnas técnicas —Tubo, Rosca, Calidad, Diámetro nominal/Sch o cualquier otra que agregues— se reconocen automáticamente.</p></div><a href="/admin/products/import/template" className="inline-flex min-h-11 items-center gap-2 rounded-xl border border-[#1954b7] px-4 text-[11px] font-semibold text-[#1954b7] transition hover:bg-[#edf3f7]"><Download size={16}/> DESCARGAR PLANTILLA ACTUAL</a></div>
            <form onSubmit={e=>{e.preventDefault();importForm.post('/admin/products/import',{forceFormData:true})}} className="flex flex-col gap-4 p-6 md:flex-row md:items-center">
                <label className="flex min-h-20 grow cursor-pointer items-center gap-4 rounded-xl border border-dashed border-[#b8c7cf] bg-[#f8fafb] px-5 transition hover:border-[#1954b7] hover:bg-[#f3f7ff]"><UploadCloud size={22} className="text-[#1954b7]"/><span className="grow text-[12px] text-[#52616a]">{importForm.data.file?.name || 'Seleccionar archivo .xlsx o .csv'}</span><input type="file" accept=".xlsx,.csv" className="hidden" onChange={e=>importForm.setData('file',e.target.files?.[0]||null)}/></label>
                <button disabled={!importForm.data.file || importForm.processing} className="min-h-12 rounded-xl bg-[#132b3b] px-6 text-[12px] font-semibold text-white disabled:opacity-40">{importForm.processing?'Importando…':'IMPORTAR PRODUCTOS'}</button>
            </form>
            {importForm.errors.file && <p className="px-6 pb-5 text-[12px] text-red-600">{importForm.errors.file}</p>}
        </section>
        <section className="mb-7 overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
            <header className="border-b border-[#edf0f2] px-6 py-5"><h2 className="text-[15px] font-semibold text-[#132b3b]">Archivos multimedia masivos</h2><p className="mt-1 text-[11px] text-[#65747c]">Primero importá el Excel. Después cargá fotos y videos: el nombre debe coincidir con las columnas Foto y Video; la extensión puede ser diferente.</p></header>
            <div className="divide-y divide-[#edf0f2] lg:grid lg:grid-cols-2 lg:divide-x lg:divide-y-0"><MediaBatchUploader kind="images"/><MediaBatchUploader kind="videos"/></div>
        </section>
        <section className="mb-7 overflow-hidden rounded-2xl border border-red-200 bg-white">
            <div className="flex flex-wrap items-center gap-4 px-6 py-5">
                <span className="grid size-11 place-items-center rounded-xl bg-red-50 text-red-700"><Trash2 size={20}/></span>
                <div className="min-w-0 grow"><h2 className="text-[15px] font-semibold text-[#132b3b]">Limpieza del inventario</h2><p className="mt-1 max-w-[75ch] text-[11px] leading-5 text-[#65747c]">El Excel sincroniza automáticamente: crea lo nuevo, actualiza lo existente y elimina códigos, productos y archivos que ya no aparecen. Usá el vaciado total solamente para comenzar desde cero.</p></div>
                <button type="button" onClick={()=>setShowClearInventory(value=>!value)} className="inline-flex min-h-11 items-center gap-2 rounded-xl border border-red-300 px-4 text-[11px] font-semibold text-red-700 transition hover:bg-red-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-red-600"><Trash2 size={16}/> VACIAR INVENTARIO</button>
            </div>
            {showClearInventory && <form onSubmit={event=>{event.preventDefault();clearForm.delete('/admin/products/inventory/all',{preserveScroll:true,onSuccess:()=>{clearForm.reset();setShowClearInventory(false)}})}} className="border-t border-red-100 bg-red-50/60 px-6 py-5">
                <p className="text-[12px] font-semibold text-red-900">Esta acción elimina todos los productos, códigos, categorías, fotos y videos del inventario.</p>
                <p className="mt-1 text-[11px] text-red-800">Para confirmar, escribí <strong>VACIAR</strong>.</p>
                <div className="mt-4 flex flex-col gap-3 sm:flex-row">
                    <input value={clearForm.data.confirmation} onChange={event=>clearForm.setData('confirmation',event.target.value.toUpperCase())} className="min-h-11 grow rounded-xl border border-red-200 bg-white px-4 text-[12px] outline-none focus:border-red-600" placeholder="Escribí VACIAR" autoComplete="off"/>
                    <button type="button" onClick={()=>{clearForm.reset();setShowClearInventory(false)}} className="min-h-11 rounded-xl border border-[#cdd5d9] bg-white px-5 text-[11px] font-semibold text-[#52616a]">CANCELAR</button>
                    <button disabled={clearForm.data.confirmation!=='VACIAR'||clearForm.processing} className="min-h-11 rounded-xl bg-red-700 px-5 text-[11px] font-semibold text-white transition hover:bg-red-800 disabled:cursor-not-allowed disabled:opacity-40">{clearForm.processing?'ELIMINANDO…':'ELIMINAR TODO'}</button>
                </div>
                {clearForm.errors.confirmation && <p className="mt-2 text-[11px] text-red-700">{clearForm.errors.confirmation}</p>}
            </form>}
        </section>
        {report && <section className="mb-7 overflow-hidden rounded-2xl border border-[#cce9dc] bg-white shadow-[0_12px_36px_rgba(18,104,75,.08)]">
            <header className="flex flex-wrap items-center gap-4 border-b border-[#e5f2ec] bg-[#f1faf6] px-6 py-5"><span className="grid size-11 place-items-center rounded-xl bg-white text-[#21845f] shadow-sm"><CheckCircle2 size={21}/></span><div className="min-w-0 grow"><h2 className="text-[15px] font-semibold text-[#173c30]">Informe de importación</h2><p className="mt-1 text-[10px] text-[#698078]">{report.file} · {report.processed} códigos únicos · {report.duplicates || 0} duplicados resueltos · {report.inferred_details || 0} familias inferidas</p>{report.technical_columns?.length > 0 && <p className="mt-2 text-[10px] font-semibold text-[#315e4b]">Características detectadas: {report.technical_columns.map((column:any)=>column.label).join(', ')}</p>}</div><span className="rounded-full bg-white px-4 py-2 text-[10px] font-semibold text-[#21845f]">{report.products_created} productos nuevos</span></header>
            <div className="grid border-b border-[#edf2ef] sm:grid-cols-4">
                {[['Creados',report.created,'text-[#21845f]'],['Actualizados',report.updated,'text-[#1954b7]'],['Sin cambios',report.unchanged,'text-[#58676e]'],['Omitidos',report.skipped,'text-[#b66b20]']].map(([text,value,color]:any)=><div key={text} className="border-b border-[#edf2ef] px-6 py-5 last:border-0 sm:border-r sm:border-b-0"><strong className={`block text-[24px] ${color}`}>{value}</strong><span className="text-[10px] font-medium text-[#7a878e]">{text}</span></div>)}
            </div>
            {(report.changes?.length > 0 || report.errors?.length > 0) && <details><summary className="cursor-pointer list-none px-6 py-4 text-[11px] font-semibold text-[#132b3b] hover:bg-[#f8fafb]">Ver detalle de cambios</summary><div className="max-h-80 overflow-auto border-t border-[#edf0f2]">
                {report.changes?.map((change:any)=><div key={`${change.row}-${change.code}`} className="grid gap-2 border-b border-[#edf0f2] px-6 py-3 text-[10px] md:grid-cols-[70px_180px_90px_1fr]"><span className="text-[#7a878e]">Fila {change.row}</span><strong className="text-[#132b3b]">{change.code}</strong><span className={change.action==='Creado'?'text-[#21845f]':'text-[#1954b7]'}>{change.action}</span><span className="text-[#69777e]">{change.fields.join(', ')}</span></div>)}
                {report.errors?.map((error:any)=><div key={`error-${error.row}`} className="border-b border-red-100 bg-red-50 px-6 py-3 text-[10px] text-red-700">Fila {error.row}: {error.message}</div>)}
            </div></details>}
        </section>}
        <form onSubmit={e=>{e.preventDefault();form.post('/admin/products',{forceFormData:true,onSuccess:()=>form.reset('name','sku','summary','description','main_image_file','video_file','youtube_url')})}} className="mb-7 rounded-xl border border-gray-200 bg-white p-5">
            <h2 className="mb-4 font-semibold text-fitinox">Nuevo producto</h2><div className="grid gap-3 md:grid-cols-3">
                <input className={input} placeholder="Nombre" value={form.data.name} onChange={e=>form.setData('name',e.target.value)}/>
                <input className={input} placeholder="Código / SKU" value={form.data.sku} onChange={e=>form.setData('sku',e.target.value)}/>
                <select className={input} value={form.data.category_id} onChange={e=>form.setData('category_id',Number(e.target.value))}>{categories.map(c=><option key={c.id} value={c.id}>{c.name}{!c.is_active?' · oculta':''}</option>)}</select>
                <label className="cursor-pointer"><span className={`${input} flex items-center text-[#68747b]`}><span className="truncate">{form.data.main_image_file?.name || 'Subir imagen principal'}</span></span><input type="file" accept="image/jpeg,image/png,image/webp" className="hidden" onChange={e=>form.setData('main_image_file',e.target.files?.[0]||null)}/><small className="mt-1.5 block text-[10px] leading-4 text-[#7a878e]">Recomendado: 496 × 496 px (1:1) · JPG, PNG o WebP · máx. 5 MB</small></label>
                <select className={input} value={form.data.video_type} onChange={e=>form.setData('video_type',e.target.value)}><option value="">Sin video 360°</option><option value="upload">Subir video</option><option value="youtube">YouTube</option></select>
                {form.data.video_type === 'upload'
                    ? <label className="cursor-pointer"><span className={`${input} flex items-center text-[#68747b]`}><span className="truncate">{form.data.video_file?.name || 'Subir MP4 / WebM / MOV'}</span></span><input type="file" accept="video/mp4,video/webm,video/quicktime" className="hidden" onChange={e=>form.setData('video_file',e.target.files?.[0]||null)}/><small className="mt-1.5 block text-[10px] leading-4 text-[#7a878e]">Recomendado: 496 × 496 px (1:1) · máximo 100 MB</small></label>
                    : <input className={input} placeholder="URL embebida de YouTube" value={form.data.youtube_url} onChange={e=>form.setData('youtube_url',e.target.value)}/>}
                <div className="flex flex-wrap items-center justify-between gap-3"><div className="flex gap-4"><label className="flex items-center gap-2 text-[12px]"><input type="checkbox" checked={form.data.is_active} onChange={e=>form.setData('is_active',e.target.checked)} className="size-4 accent-[#1954b7]"/> Visible en web</label><label className="flex items-center gap-2 text-[12px]"><input type="checkbox" checked={form.data.is_featured} onChange={e=>form.setData('is_featured',e.target.checked)} className="size-4 accent-[#1954b7]"/> Destacado en Home</label></div><button className="rounded-md bg-fitinox px-5 py-2 text-sm text-white">AGREGAR</button></div>
            </div>
        </form>
        <div className="mb-4 flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
            <form onSubmit={e=>{e.preventDefault(); const q=new FormData(e.currentTarget).get('search');router.get('/admin/products',{search:q},{preserveState:true})}} className="flex w-full max-w-md"><input name="search" defaultValue={filters.search} className={`${input} rounded-r-none`} placeholder="Buscar por producto, SKU o código"/><button className="rounded-r-md bg-fitinox px-4 text-white" aria-label="Buscar"><Search size={17}/></button></form>
            <p className="text-[11px] text-[#758189]">Mostrando <strong className="text-[#132b3b]">{products.from || 0}–{products.to || 0}</strong> de {products.total} productos, ordenados por categoría y nombre.</p>
        </div>
        <div className="space-y-5">
            {categories.map(category => {
                const categoryProducts = products.data.filter((product:any) => product.category_id === category.id);
                if (!categoryProducts.length) return null;
                return <section key={category.id} className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white shadow-[0_8px_30px_rgba(19,43,59,.04)]">
                    <header className="flex items-center gap-4 border-b border-[#edf0f2] bg-[#f8fafb] px-6 py-5"><span className="grid size-10 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><FolderTree size={18}/></span><div className="grow"><h2 className="text-[15px] font-semibold text-[#132b3b]">{category.name}</h2><p className="mt-1 text-[10px] text-[#758189]">{categoryProducts.length} en esta página · los códigos y datos comerciales se editan dentro de cada producto.</p></div><Link href={`/admin/categories/${category.id}/edit`} className="text-[11px] font-semibold text-[#1954b7]">Editar categoría</Link></header>
                    <div className="overflow-x-auto"><table className="min-w-[820px] w-full text-left text-[12px]"><thead className="bg-[#132b3b] text-white"><tr><th className="px-6 py-4">Producto</th><th>Códigos</th><th>Home</th><th>Publicación</th><th className="pr-6 text-right">Acciones</th></tr></thead><tbody>{categoryProducts.map((p:any)=><tr key={p.id} className="border-t border-[#edf0f2]"><td className="flex items-center gap-4 px-6 py-4"><div className="grid size-14 place-items-center overflow-hidden rounded-xl border border-[#e5eaed] bg-[#f5f7f8]"><img src={`/storage/${p.main_image||'products/product-placeholder.svg'}${p.main_image&&p.updated_at?`?v=${encodeURIComponent(p.updated_at)}`:''}`} className="max-h-12 max-w-12 object-contain" alt=""/></div><div><span className="block text-[13px] font-semibold text-[#263b45]">{p.name}</span><small className="mt-1 block text-[#7b878e]">Orden {p.sort_order}{p.sku ? ` · SKU ${p.sku}` : ''}</small></div></td><td><span className="inline-flex rounded-full bg-[#edf3f7] px-3 py-1.5 text-[10px] font-semibold text-[#1954b7]">{p.variants_count} {p.variants_count === 1 ? 'código' : 'códigos'}</span></td><td>{p.is_featured?'Destacado':'No'}</td><td>{p.is_active?'Visible':'Oculto'}</td><td className="pr-6 text-right"><div className="inline-flex gap-2"><Link aria-label={`Editar ${p.name}`} href={`/admin/products/${p.id}/edit#codigos`} className="inline-flex min-h-10 items-center gap-2 rounded-lg border border-[#dfe5e8] px-3 text-[11px] font-semibold text-[#132b3b] hover:bg-[#edf3f7]"><Pencil size={15}/> Producto y códigos</Link><button aria-label={`Eliminar ${p.name}`} onClick={()=>confirm('¿Eliminar producto y todos sus códigos?')&&router.delete(`/admin/products/${p.id}`)} className="grid size-10 place-items-center rounded-lg border border-red-100 text-red-600 hover:bg-red-50"><Trash2 size={16}/></button></div></td></tr>)}</tbody></table></div>
                </section>;
            })}
        </div>
        {products.last_page > 1 && <nav className="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-[#dfe5e8] bg-white px-5 py-4" aria-label="Paginación de productos">
            <p className="text-[11px] text-[#758189]">Página <strong className="text-[#132b3b]">{products.current_page}</strong> de {products.last_page}</p>
            <div className="flex items-center gap-2">
                <button type="button" disabled={!products.prev_page_url} onClick={()=>products.prev_page_url&&router.get(products.prev_page_url,{}, {preserveScroll:true,preserveState:true})} className="min-h-10 rounded-lg border border-[#dfe5e8] px-4 text-[11px] font-semibold text-[#132b3b] transition hover:border-[#1954b7] hover:text-[#1954b7] disabled:cursor-not-allowed disabled:opacity-35">ANTERIOR</button>
                <span className="grid min-h-10 min-w-10 place-items-center rounded-lg bg-[#132b3b] px-3 text-[11px] font-semibold text-white">{products.current_page}</span>
                <button type="button" disabled={!products.next_page_url} onClick={()=>products.next_page_url&&router.get(products.next_page_url,{}, {preserveScroll:true,preserveState:true})} className="min-h-10 rounded-lg border border-[#dfe5e8] px-4 text-[11px] font-semibold text-[#132b3b] transition hover:border-[#1954b7] hover:text-[#1954b7] disabled:cursor-not-allowed disabled:opacity-35">SIGUIENTE</button>
            </div>
        </nav>}
    </AdminLayout>;
}
