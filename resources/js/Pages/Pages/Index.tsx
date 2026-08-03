import { Head, Link } from '@inertiajs/react';
import { Pencil } from 'lucide-react';
import AdminLayout from '../../Components/AdminLayout';

export default function Index({ pages }: { pages: any[] }) {
    return <AdminLayout title="Páginas"><Head title="Páginas | Fitinox"/><div className="overflow-hidden rounded-xl border border-gray-200 bg-white">
        <table className="w-full text-left text-sm"><thead className="bg-fitinox text-white"><tr><th className="px-6 py-4">Página</th><th>Plantilla</th><th>Estado</th><th></th></tr></thead>
        <tbody>{pages.map(page => <tr key={page.id} className="border-t border-gray-100"><td className="px-6 py-5 font-medium text-fitinox">{page.title}</td><td>{page.template}</td><td>{page.is_published ? 'Publicada' : 'Borrador'}</td><td className="pr-6 text-right"><Link href={`/admin/pages/${page.id}/edit`} className="inline-flex rounded-md border border-gray-200 p-2"><Pencil size={17}/></Link></td></tr>)}</tbody></table>
    </div></AdminLayout>;
}
