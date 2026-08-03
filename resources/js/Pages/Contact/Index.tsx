import { Head, Link, router, useForm } from '@inertiajs/react';
import { CalendarDays, ExternalLink, Image, Mail, MapPin, MessageCircle, Phone, Save, UploadCloud, UserRound } from 'lucide-react';
import { useEffect, useState } from 'react';
import AdminLayout from '../../Components/AdminLayout';

const field='min-h-12 w-full rounded-xl border border-[#dfe5e8] bg-white px-4 text-[13px] outline-none transition focus:border-[#1954b7] focus:ring-4 focus:ring-[#1954b7]/8';
const statuses:Record<string,string>={new:'Nuevo',in_progress:'En seguimiento',replied:'Respondido',closed:'Cerrado'};

function MessageCard({message}:any){
    const [status,setStatus]=useState(message.status);
    return <article className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white">
        <header className="flex flex-wrap items-center gap-4 border-b border-[#edf0f2] px-6 py-5">
            <span className="grid size-11 shrink-0 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><UserRound size={20}/></span>
            <div className="min-w-0 grow"><div className="flex flex-wrap items-center gap-2"><h3 className="text-[14px] font-semibold text-[#132b3b]">{message.name}</h3><span className="rounded-full bg-[#f1f4f5] px-2.5 py-1 text-[9px] font-semibold text-[#65747c]">#{String(message.id).padStart(5,'0')}</span></div><p className="mt-1 flex items-center gap-1.5 text-[10px] text-[#7a878e]"><CalendarDays size={12}/>{new Date(message.created_at).toLocaleString('es-AR')}</p></div>
            <select value={status} onChange={e=>{setStatus(e.target.value);router.patch(`/admin/contact/${message.id}/status`,{status:e.target.value},{preserveScroll:true})}} className="h-10 rounded-xl border border-[#dfe5e8] bg-white px-3 text-[11px] font-semibold text-[#132b3b] outline-none">{Object.entries(statuses).map(([value,label])=><option key={value} value={value}>{label}</option>)}</select>
        </header>
        <div className="grid gap-6 p-6 lg:grid-cols-[280px_1fr]">
            <div className="space-y-3 text-[11px] leading-[18px] text-[#52636c]"><p className="flex gap-2"><Mail size={15} className="mt-0.5 shrink-0 text-[#1954b7]"/><a href={`mailto:${message.email}`} className="break-all hover:text-[#1954b7]">{message.email}</a></p><p className="flex gap-2"><Phone size={15} className="mt-0.5 shrink-0 text-[#1954b7]"/><a href={`tel:${message.phone}`}>{message.phone}</a></p>{message.company&&<p><strong className="text-[#132b3b]">Empresa:</strong> {message.company}</p>}</div>
            <div className="rounded-xl bg-[#f6f8f9] p-4 text-[12px] leading-[20px] text-[#52636c]"><strong className="mb-1 block text-[#132b3b]">Mensaje</strong><p className="whitespace-pre-wrap">{message.message}</p></div>
        </div>
    </article>
}

const contactList = (value:string|undefined, fallback:string) => {
    const source = value || fallback;
    let lines:string[] = [];
    try {
        const parsed = JSON.parse(source);
        lines = Array.isArray(parsed) ? parsed : source.split(/\r?\n/);
    } catch (_) {}
    if (!lines.length) lines = source.split(/\r?\n/);
    return lines.flatMap((line) => {
        const groups:string[] = [];
        line.split(/\s+\/\s+/).map((part) => part.trim()).filter(Boolean).forEach((part) => {
            if (part.replace(/\D/g, '').length >= 8 || !groups.length) groups.push(part);
            else groups[groups.length - 1] += ` / ${part}`;
        });
        return groups;
    }).join('\n');
};

export default function Index({page,messages,settings}:any){
    const pageForm=useForm<any>({hero_title:page.hero_title||'Contacto',hero_image_file:null});
    const detailsForm=useForm<any>({
        contact_address:settings.contact_address||settings.address||'Calle 102 (ex industria) N 4946/52 (B1650MSN) San Martín. Bs As, Argentina',
        contact_email:settings.contact_email||settings.email_sales||'ventas@fittinox.com.ar',
        contact_phone_numbers:contactList(settings.contact_phone_numbers,settings.phone||'+54 (011) 4738-5562 /\n+54 (011) 4764-2389 / 6647/8'),
        contact_whatsapp_numbers:contactList(settings.contact_whatsapp_numbers,settings.whatsapp||'54 9 11 4073-2016\n11 2237-2899\n11 5145-7853'),
        contact_latitude:settings.contact_latitude||'-34.5728',
        contact_longitude:settings.contact_longitude||'-58.5416',
    });
    const [preview,setPreview]=useState(`/storage/${page.hero_image||'figma/contact/hero.png'}`);
    useEffect(()=>{if(!pageForm.data.hero_image_file){setPreview(`/storage/${page.hero_image||'figma/contact/hero.png'}`);return}const url=URL.createObjectURL(pageForm.data.hero_image_file);setPreview(url);return()=>URL.revokeObjectURL(url)},[pageForm.data.hero_image_file,page.hero_image]);
    return <AdminLayout title="Contacto"><Head title="Contacto | Administración"/><div className="mx-auto max-w-[1120px] space-y-6">
        <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-10 place-items-center rounded-xl bg-[#edf3f7] text-[#1954b7]"><Image size={19}/></span><div className="grow"><h2 className="text-[15px] font-semibold text-[#132b3b]">Portada de Contacto</h2><p className="mt-1 text-[10px] text-[#7a878e]">Título e imagen superior de la página pública.</p></div><a href="/contacto" target="_blank" className="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#dfe5e8] px-4 text-[10px] font-semibold"><ExternalLink size={14}/>VER PÁGINA</a></header>
            <div className="grid gap-6 p-6 lg:grid-cols-[1.4fr_1fr]"><div className="aspect-[1224/301] overflow-hidden rounded-xl bg-[#132b3b]"><img src={preview} className="h-full w-full object-cover" alt="Portada actual"/></div><div className="space-y-5"><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Título de portada</span><input className={field} value={pageForm.data.hero_title} onChange={e=>pageForm.setData('hero_title',e.target.value)}/></label><label className="flex min-h-[78px] cursor-pointer items-center gap-3 rounded-xl border border-dashed border-[#b8c7cf] px-4"><UploadCloud size={18} className="text-[#1954b7]"/><span><strong className="block text-[11px]">{pageForm.data.hero_image_file?.name||'Reemplazar portada'}</strong><small className="mt-1 block text-[9px] text-[#7a878e]">Recomendado: 1920 × 600 px · JPG, PNG o WebP · máx. 12 MB.</small></span><input type="file" accept="image/jpeg,image/png,image/webp" className="sr-only" onChange={e=>pageForm.setData('hero_image_file',e.target.files?.[0]||null)}/></label><button type="button" disabled={pageForm.processing||!pageForm.isDirty} onClick={()=>pageForm.post('/admin/contact/page',{forceFormData:true,preserveScroll:true})} className="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#1954b7] text-[10px] font-semibold text-white disabled:opacity-40"><Save size={15}/>GUARDAR PORTADA</button></div></div>
        </section>
        <section className="overflow-hidden rounded-2xl border border-[#dfe5e8] bg-white"><header className="flex items-center gap-3 border-b border-[#edf0f2] px-6 py-5"><span className="grid size-10 place-items-center rounded-xl bg-[#132b3b] text-white"><Phone size={19}/></span><div><h2 className="text-[15px] font-semibold text-[#132b3b]">Datos públicos de contacto</h2><p className="mt-1 text-[10px] text-[#7a878e]">Los teléfonos y WhatsApps se cargan una línea por número. Con más de uno, el sitio permite elegir antes de llamar o escribir.</p></div></header><div className="grid gap-5 p-6 md:grid-cols-2"><label className="md:col-span-2"><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Dirección</span><textarea rows={2} className={`${field} py-3 leading-5`} value={detailsForm.data.contact_address} onChange={e=>detailsForm.setData('contact_address',e.target.value)}/></label><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">E-mail de contacto</span><input type="email" className={field} value={detailsForm.data.contact_email} onChange={e=>detailsForm.setData('contact_email',e.target.value)}/></label><div className="hidden md:block"><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Mapa de Contacto</span><div className="flex min-h-12 items-center gap-2 rounded-xl border border-[#dfe5e8] bg-[#f8fafb] px-4 text-[11px] leading-4 text-[#617078]"><MapPin size={15} className="shrink-0 text-[#1954b7]"/>Configurá la sede que muestra el mapa público.</div></div><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Teléfonos</span><textarea rows={5} className={`${field} py-3 leading-5`} placeholder={'+54 (011) 4738-5562\n+54 (011) 4764-2389'} value={detailsForm.data.contact_phone_numbers} onChange={e=>detailsForm.setData('contact_phone_numbers',e.target.value)}/><small className="mt-1.5 block text-[10px] text-[#7a878e]">Un teléfono por línea. Si hay varios, el visitante podrá elegir uno.</small></label><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">WhatsApps</span><textarea rows={5} className={`${field} py-3 leading-5`} placeholder={'54 9 11 4073-2016\n54 9 11 2237-2899'} value={detailsForm.data.contact_whatsapp_numbers} onChange={e=>detailsForm.setData('contact_whatsapp_numbers',e.target.value)}/><small className="mt-1.5 block text-[10px] text-[#7a878e]">Un número por línea, con código de país. Ejemplo: 54 9 11 4073-2016.</small></label><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Latitud de la sede</span><input type="number" step="any" min="-90" max="90" className={field} value={detailsForm.data.contact_latitude} onChange={e=>detailsForm.setData('contact_latitude',e.target.value)}/><small className="mt-1.5 block text-[10px] text-[#7a878e]">Ejemplo: -34.5728. Copiala desde Google Maps.</small></label><label><span className="mb-2 block text-[12px] font-semibold text-[#334851]">Longitud de la sede</span><input type="number" step="any" min="-180" max="180" className={field} value={detailsForm.data.contact_longitude} onChange={e=>detailsForm.setData('contact_longitude',e.target.value)}/><small className="mt-1.5 block text-[10px] text-[#7a878e]">Ejemplo: -58.5416. Es el segundo valor de las coordenadas.</small></label><div className="flex flex-wrap items-center justify-between gap-4 border-t border-[#edf0f2] pt-5 md:col-span-2"><p className="text-[10px] leading-4 text-[#7a878e]">Los cambios actualizan los datos compartidos y el mapa de Contacto.</p><button type="button" disabled={detailsForm.processing||!detailsForm.isDirty} onClick={()=>detailsForm.post('/admin/contact/details',{preserveScroll:true})} className="inline-flex min-h-12 items-center gap-2 rounded-xl bg-[#1954b7] px-5 text-[11px] font-semibold text-white transition hover:bg-[#132b3b] disabled:cursor-not-allowed disabled:opacity-40"><Save size={15}/>{detailsForm.processing?'GUARDANDO…':'GUARDAR DATOS'}</button></div>{Object.keys(detailsForm.errors).length>0&&<div role="alert" className="rounded-xl border border-red-200 bg-red-50 p-4 text-[11px] text-red-700 md:col-span-2">{Object.values(detailsForm.errors).map((error:any)=><p key={error}>{error}</p>)}</div>}</div></section>
        <div className="flex items-center gap-3"><span className="grid size-10 place-items-center rounded-xl bg-[#132b3b] text-white"><MessageCircle size={18}/></span><div><h2 className="text-[18px] font-semibold text-[#132b3b]">Mensajes recibidos</h2><p className="mt-1 text-[11px] text-[#7a878e]">{messages.total} consultas registradas.</p></div></div>
        <div className="space-y-5">{messages.data.map((message:any)=><MessageCard key={message.id} message={message}/>)}</div>
        {messages.data.length===0&&<div className="rounded-2xl border border-dashed border-[#cfd8dc] bg-white py-16 text-center text-[12px] text-[#7a878e]">Todavía no se recibieron mensajes.</div>}
        {messages.links?.length>3&&<nav className="flex flex-wrap justify-center gap-2">{messages.links.map((link:any,index:number)=>link.url?<Link key={index} href={link.url} preserveScroll className={`grid min-h-9 min-w-9 place-items-center rounded-lg px-3 text-[10px] ${link.active?'bg-[#132b3b] text-white':'border border-[#dfe5e8] bg-white text-[#52636c]'}`}>{String(link.label).replace(/&laquo;/g,'«').replace(/&raquo;/g,'»')}</Link>:null)}</nav>}
    </div></AdminLayout>
}
