import AppLayout from '@/layouts/app-layout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

type Notification = {
  id: number;
  title: string;
  description: string;
  sent_at: string;
};

type PageProps = {
  notifications: {
    data: Notification[];
    links: any[];
  };
};

export default function Index({ notifications }: PageProps) {
  return (
    <AppLayout breadcrumbs={[{ title: 'Notifications', href: '/admin/notifications' }]}>
      <Head title="Custom Notifications" />
      <div className="p-6">
        <div className="flex items-center justify-between mb-6">
            <h1 className="text-2xl font-semibold">Custom Notifications</h1>
            <Link href={route('admin.notifications.create')}>
                <Button>Send New Notification</Button>
            </Link>
        </div>

        <div className="rounded-md border">
            <table className="w-full text-sm">
                <thead className="border-b bg-muted/50">
                    <tr className="text-left">
                        <th className="p-4 font-medium">Title</th>
                        <th className="p-4 font-medium">Description</th>
                        <th className="p-4 font-medium">Sent At</th>
                    </tr>
                </thead>
                <tbody>
                    {notifications.data.map((n) => (
                        <tr key={n.id} className="border-b">
                            <td className="p-4">{n.title}</td>
                            <td className="p-4 text-muted-foreground">{n.description}</td>
                            <td className="p-4">{n.sent_at}</td>
                        </tr>
                    ))}
                    {notifications.data.length === 0 && (
                        <tr>
                            <td colSpan={3} className="p-8 text-center text-muted-foreground">
                                No notifications sent yet.
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
