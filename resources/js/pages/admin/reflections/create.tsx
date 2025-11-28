import AppLayout from '@/layouts/app-layout';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';

export default function Create() {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        type: 'text',
        scheduled_date: '',
        content: '',
        author: '',
        reference: '',
        media_url: '',
    });

    function submit(e: React.FormEvent) {
        e.preventDefault();
        post(route('admin.reflections.store'));
    }

    return (
        <AppLayout breadcrumbs={[{ title: 'Create Reflection', href: '/admin/reflections/create' }]}>
            <Head title="Create Reflection" />
            <div className="max-w-2xl mx-auto p-6">
                <h1 className="text-2xl font-bold mb-6">New Daily Reflection</h1>
                <form onSubmit={submit} className="space-y-4">
                    <div>
                        <label className="block text-sm font-medium mb-1">Scheduled Date</label>
                        <input 
                            type="date" 
                            className="border p-2 rounded w-full"
                            value={data.scheduled_date}
                            onChange={e => setData('scheduled_date', e.target.value)}
                        />
                        {errors.scheduled_date && <div className="text-red-500 text-xs">{errors.scheduled_date}</div>}
                    </div>

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
                        <label className="block text-sm font-medium mb-1">Type</label>
                        <select 
                            className="border p-2 rounded w-full"
                            value={data.type}
                            onChange={e => setData('type', e.target.value)}
                        >
                            <option value="text">Text</option>
                            <option value="quote">Quote</option>
                            <option value="verse">Bible Verse</option>
                            <option value="audio">Audio</option>
                        </select>
                    </div>

                    <div>
                        <label className="block text-sm font-medium mb-1">Content / Quote / Verse Text</label>
                        <textarea 
                            className="border p-2 rounded w-full h-32"
                            value={data.content}
                            onChange={e => setData('content', e.target.value)}
                        />
                    </div>

                    {data.type === 'quote' && (
                        <div>
                            <label className="block text-sm font-medium mb-1">Author</label>
                            <input 
                                type="text" 
                                className="border p-2 rounded w-full"
                                value={data.author}
                                onChange={e => setData('author', e.target.value)}
                            />
                        </div>
                    )}

                    {data.type === 'verse' && (
                        <div>
                            <label className="block text-sm font-medium mb-1">Reference (e.g. John 3:16)</label>
                            <input 
                                type="text" 
                                className="border p-2 rounded w-full"
                                value={data.reference}
                                onChange={e => setData('reference', e.target.value)}
                            />
                        </div>
                    )}

                    {data.type === 'audio' && (
                        <div>
                            <label className="block text-sm font-medium mb-1">Audio URL</label>
                            <input 
                                type="text" 
                                className="border p-2 rounded w-full"
                                placeholder="https://..."
                                value={data.media_url}
                                onChange={e => setData('media_url', e.target.value)}
                            />
                        </div>
                    )}

                    <div className="pt-4">
                        <Button type="submit" disabled={processing}>Save Reflection</Button>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
