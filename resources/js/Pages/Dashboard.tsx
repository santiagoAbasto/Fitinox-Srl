import { Head } from '@inertiajs/react';
import { Boxes, FileText, FolderTree, Inbox } from 'lucide-react';
import AdminLayout from '../Components/AdminLayout';

export default function Dashboard({ stats }: { stats: Record<string, number> }) {
    const cards = [
        ['Páginas', stats.pages, FileText], ['Categorías', stats.categories, FolderTree],
        ['Productos', stats.products, Boxes], ['Consultas nuevas', stats.inquiries, Inbox],
    ] as const;
    return <AdminLayout title="Resumen general">
        <Head title="Administración | Fitinox" />
            <section className="grid grid-cols-4 gap-5">
                {cards.map(([label, value, Icon]) => <article key={label} className="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
                    <Icon className="text-fitinox-accent" size={24}/><p className="mt-5 text-3xl font-semibold text-fitinox">{value}</p><p className="mt-1 text-sm text-gray-500">{label}</p>
                </article>)}
            </section>
            <section className="mt-8 rounded-xl border border-gray-200 bg-white p-8">
                <h2 className="text-xl font-semibold text-fitinox">CMS Fitinox</h2>
                <p className="mt-2 max-w-2xl text-sm leading-6 text-gray-500">La base del catálogo, páginas, contenidos, archivos y consultas ya está conectada. Los siguientes módulos incorporarán altas, edición, ordenamiento, publicación y biblioteca de medios.</p>
            </section>
    </AdminLayout>;
}
