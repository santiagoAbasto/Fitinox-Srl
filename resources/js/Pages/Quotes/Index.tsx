import { Head, Link, router, useForm } from '@inertiajs/react';
import { CalendarDays, ExternalLink, Image, Mail, Package, Phone, Save, UploadCloud, UserRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const field = 'min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';
const statusLabels: Record<string,string> = {new:'Nueva',in_progress:'En seguimiento',quoted:'Cotizada',closed:'Cerrada'};

function RequestCard({request}: any) {
    const form = useForm({status: request.status});
    return <article className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
        <header className="flex flex-wrap items-center gap-4 border-b border-[#edf0f2] px-6 py-5">
            <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><UserRound size={20}/></span>
            <div className="min-w-0 grow">
                <div className="flex flex-wrap items-center gap-2"><h3 className="text-[14px] font-semibold text-[#132b3b]">{request.name}</h3><span className="rounded-full bg-[#f1f4f5] px-2.5 py-1 text-[9px] font-semibold text-[#65747c]">#{String(request.id).padStart(5,'0')}</span></div>
                <p className="mt-1 flex items-center gap-1.5 text-[10px] text-[#7a878e]"><CalendarDays size={12}/>{new Date(request.created_at).toLocaleString('es-AR')}</p>
            </div>
            <select className="h-10 rounded-xl border border-[#dfe5e8] bg-white px-3 text-[11px] font-semibold text-[#132b3b] outline-none" value={form.data.status} onChange={e=>{const status=e.target.value;form.setData('status',status);router.patch(`/admin/quotes/${request.id}/status`,{status},{preserveScroll:true})}}>
                {Object.entries(statusLabels).map(([value,label])=><option key={value} value={value}>{label}</option>)}
            </select>
        </header>
        <div className="grid gap-6 p-6 lg:grid-cols-[280px_1fr]">
            <div className="space-y-3 text-[11px] leading-[18px] text-[#52636c]">
                <p className="flex gap-2"><Mail size={15} className="mt-0.5 shrink-0 text-[#1954b7]"/><a href={`mailto:${request.email}`} className="break-all hover:text-[#1954b7]">{request.email}</a></p>
                <p className="flex gap-2"><Phone size={15} className="mt-0.5 shrink-0 text-[#1954b7]"/><a href={`tel:${request.phone}`}>{request.phone}</a></p>
                {request.company&&<p><strong className="text-[#132b3b]">Empresa:</strong> {request.company}</p>}
                {request.notes&&<div className="rounded-xl bg-[#f6f8f9] p-3"><strong className="block text-[#132b3b]">Comentarios</strong><p className="mt-1 whitespace-pre-wrap">{request.notes}</p></div>}
            </div>
            <div className="overflow-hidden rounded-xl border border-[#e7ebed]">
                <div className="grid grid-cols-[1fr_140px_80px] bg-[#132b3b] px-4 py-3 text-[9px] font-semibold tracking-[.08em] text-white"><span>PRODUCTO / CÓDIGO</span><span>MEDIDAS</span><span className="text-right">CANT.</span></div>
                {request.items.map((item:any)=><div key={item.id} className="grid min-h-14 grid-cols-[1fr_140px_80px] items-center border-b border-[#edf0f2] px-4 py-2 text-[11px] last:border-0">
                    <span><strong className="block text-[#132b3b]">{item.product_name}</strong><small className="text-[9px] text-[#7a878e]">{item.code}</small></span>
                    <span className="text-[#52636c]">{item.tube || '—'} / {item.thread || '—'}</span>
                    <strong className="text-right text-[#132b3b]">{item.quantity}</strong>
                </div>)}
            </div>
        </div>
    </article>;
}

export default function Index({page,requests}:any) {
    const pageForm = useForm<any>({hero_title:page.hero_title||'Solicitud de presupuesto',hero_image_file:null});
    const [preview,setPreview] = useState(`/storage/${page.hero_image||'figma/quote/hero.png'}`);
    useEffect(()=>{if(!pageForm.data.hero_image_file){setPreview(`/storage/${page.hero_image||'figma/quote/hero.png'}`);return}const url=URL.createObjectURL(pageForm.data.hero_image_file);setPreview(url);return()=>URL.revokeObjectURL(url)},[pageForm.data.hero_image_file,page.hero_image]);

    return <AdminLayout title="Presupuesto"><Head title="Presupuesto | Administración"/>
        <div className="mx-auto max-w-[1120px] space-y-6">
            <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
                <header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5">
                    <span className="grid size-10 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><Image size={19}/></span>
                    <div className="grow"><h2 className="text-[15px] font-semibold text-[#132b3b]">Portada de Presupuesto</h2><p className="mt-1 text-[10px] text-[#7a878e]">Título e imagen superior de la pantalla pública.</p></div>
                    <a href="/presupuesto" target="_blank" className="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#dfe5e8] px-4 text-[10px] font-semibold"><ExternalLink size={14}/>VER PÁGINA</a>
                </header>
                <div className="grid gap-6 p-6 lg:grid-cols-[1.4fr_1fr]">
                    <div className="aspect-[1224/300] overflow-hidden rounded-xl bg-[#132b3b]"><img src={preview} className="h-full w-full object-cover" alt="Portada actual"/></div>
                    <div className="space-y-5">
                        <label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Título de portada</span><input className={field} value={pageForm.data.hero_title} onChange={e=>pageForm.setData('hero_title',e.target.value)}/></label>
                        <label className="flex min-h-[78px] cursor-pointer items-center gap-3 rounded-xl border border-dashed border-[#b8c7cf] px-4"><UploadCloud size={18} className="text-[#1954b7]"/><span><strong className="block text-[11px]">{pageForm.data.hero_image_file?.name||'Reemplazar portada'}</strong><small className="mt-1 block text-[9px] text-[#7a878e]">Recomendado: 1920 × 600 px · JPG, PNG o WebP · máx. 12 MB.</small></span><input type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={e=>pageForm.setData('hero_image_file',e.target.files?.[0]||null)}/></label>
                        <button type="button" disabled={pageForm.processing||!pageForm.isDirty} onClick={()=>pageForm.post('/admin/quotes/page',{forceFormData:true,preserveScroll:true})} className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#1954b7] text-[10px] font-semibold text-white disabled:opacity-40"><Save size={15}/>GUARDAR PORTADA</button>
                    </div>
                </div>
            </section>
            <div className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-xl bg-[#132b3b] text-white"><Package size={18}/></span><div><h2 className="text-[18px] font-semibold text-[#132b3b]">Solicitudes recibidas</h2><p className="mt-1 text-[11px] text-[#7a878e]">{requests.total} presupuestos registrados.</p></div></div>
            <div className="space-y-5">{requests.data.map((request:any)=><RequestCard key={request.id} request={request}/>)}</div>
            {requests.data.length===0&&<div className="rounded-2xl border border-dashed border-[#cfd8dc] bg-white py-16 text-center text-[12px] text-[#7a878e]">Todavía no se recibieron solicitudes.</div>}
            {requests.links?.length>3&&<nav className="flex flex-wrap justify-center gap-2">{requests.links.map((link:any,index:number)=>link.url?<Link key={index} href={link.url} preserveScroll className={`grid min-h-9 min-w-9 place-items-center rounded-lg px-3 text-[10px] ${link.active?'bg-[#132b3b] text-white':'border border-[#dfe5e8] bg-white text-[#52636c]'}`} dangerouslySetInnerHTML={{__html:link.label}}/>:null)}</nav>}
        </div>
    </AdminLayout>;
}
