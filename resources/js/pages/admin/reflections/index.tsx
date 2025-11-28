import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Reflection = {
  id: number;
  title: string;
  scheduled_date: string;
  type: string;
};

type PageProps = {
  reflections: {
    data: Reflection[];
    links: any[];
  };
};

export default function Index({ reflections }: PageProps) {
  return (
    <AppLayout breadcrumbs={[{ title: 'Reflections', href: '/admin/reflections' }]}>
      <Head title="Daily Reflections" />
      <div className="p-6">
        <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-semibold">Daily Reflections</h1>
            <Link href={route('admin.reflections.create')}>
                <Button>Create Reflection</Button>
            </Link>
        </div>

        <div className="rounded-md border">
            <table className="w-full text-sm">
                <thead className="border-b bg-muted/50">
                    <tr className="text-left">
                        <th className="p-4 font-medium">Date</th>
                        <th className="p-4 font-medium">Title</th>
                        <th className="p-4 font-medium">Type</th>
                        <th className="p-4 font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    {reflections.data.map((reflection) => (
                        <tr key={reflection.id} className="border-b">
                            <td className="p-4">{reflection.scheduled_date}</td>
                            <td className="p-4">{reflection.title}</td>
                            <td className="p-4 capitalize">{reflection.type}</td>
                            <td className="p-4">
                                <Link href={route('admin.reflections.edit', reflection.id)} className="text-blue-500 hover:underline mr-4">
                                    Edit
                                </Link>
                            </td>
                        </tr>
                    ))}
                    {reflections.data.length === 0 && (
                        <tr>
                            <td colSpan={4} className="p-8 text-center text-muted-foreground">
                                No reflections found. Create one!
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
      </div>
    </AppLayout>
  );
}
