import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        description: '',
        content: '',
        image_url: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('admin.notifications.store'));
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Send Notification', href: '/admin/notifications/create' }]}>
            <Head title="Send Notification" />
            <div className="max-w-2xl mx-auto p-6">
                <h1 className="text-2xl font-bold mb-6">Send Custom Notification</h1>
                <p className="text-sm text-muted-foreground mb-6">
                    This will send a push notification to ALL users immediately.
                </p>
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-1">Title</label>
                        <input 
                            type="text" 
                            className="border p-2 rounded w-full"
                            value={data.title}
                            onChange={e => setData('title', e.target.value)}
                        />
                        {errors.title && <div className="text-red-500 text-xs">{errors.title}</div>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Short Description (Notification Body)</label>
                        <input 
                            type="text" 
                            className="border p-2 rounded w-full"
                            value={data.description}
                            onChange={e => setData('description', e.target.value)}
                            maxLength={100}
                        />
                        {errors.description && <div className="text-red-500 text-xs">{errors.description}</div>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Full Content (Shown in App)</label>
                        <textarea 
                            className="border p-2 rounded w-full h-32"
                            value={data.content}
                            onChange={e => setData('content', e.target.value)}
                        />
                        {errors.content && <div className="text-red-500 text-xs">{errors.content}</div>}
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Image URL (Optional)</label>
                        <input 
                            type="text" 
                            className="border p-2 rounded w-full"
                            placeholder="https://..."
                            value={data.image_url}
                            onChange={e => setData('image_url', e.target.value)}
                        />
                    </div>

                    <div className="pt-4">
                        <Button type="submit" disabled={processing}>Send Notification Now</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
