import AppLayout from '@/layouts/app-layout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';

export default function TaskCreate() {
  const { props } = usePage<{ exercises: Array<{ id: number; title: string }> }>();
  const exercises = props.exercises ?? [];

  const { data, setData, post, processing, errors } = useForm({
    exercise_id: exercises[0]?.id?.toString() ?? '',
    title: '',
    description: '',
    duration_days: '',
    start_date: '',
    is_active: true,
    schedule: '',
    sort_order: '',
  });

  function submit(e: React.FormEvent) {
    e.preventDefault();
    post('/admin/tasks');
  }

  return (
    <AppLayout breadcrumbs={[{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Tasks', href: '/admin/tasks' }, { title: 'Create', href: '/admin/tasks/create' }]}> 
      <Head title="Create Task" />
      <form onSubmit={submit} className="flex flex-col gap-6 p-4 max-w-2xl">
        <div className="grid gap-4">
          <div>
            <Label>Exercise</Label>
            <Select value={data.exercise_id} onValueChange={(v) => setData('exercise_id', v)}>
              <SelectTrigger>
                <SelectValue placeholder="Select exercise" />
              </SelectTrigger>
              <SelectContent>
                {exercises.map((e) => (
                  <SelectItem key={e.id} value={String(e.id)}>{e.title}</SelectItem>
                ))}
              </SelectContent>
            </Select>
            {errors.exercise_id && <p className="text-xs text-red-600 mt-1">{errors.exercise_id}</p>}
          </div>

          <div>
            <Label>Title</Label>
            <Input value={data.title} onChange={(e) => setData('title', e.target.value)} />
            {errors.title && <p className="text-xs text-red-600 mt-1">{errors.title}</p>}
          </div>

          <div>
            <Label>Description</Label>
            <Input value={data.description} onChange={(e) => setData('description', e.target.value)} />
            {errors.description && <p className="text-xs text-red-600 mt-1">{errors.description}</p>}
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Duration (days)</Label>
              <Input type="number" value={data.duration_days} onChange={(e) => setData('duration_days', e.target.value)} />
              {errors.duration_days && <p className="text-xs text-red-600 mt-1">{errors.duration_days}</p>}
            </div>
            <div>
              <Label>Start Date</Label>
              <Input type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
              {errors.start_date && <p className="text-xs text-red-600 mt-1">{errors.start_date}</p>}
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <Label>Schedule</Label>
              <Input value={data.schedule} onChange={(e) => setData('schedule', e.target.value)} placeholder="e.g. daily, weekly" />
              {errors.schedule && <p className="text-xs text-red-600 mt-1">{errors.schedule}</p>}
            </div>
            <div>
              <Label>Sort Order</Label>
              <Input type="number" value={data.sort_order} onChange={(e) => setData('sort_order', e.target.value)} />
              {errors.sort_order && <p className="text-xs text-red-600 mt-1">{errors.sort_order}</p>}
            </div>
          </div>
        </div>

        <div className="flex gap-2">
          <Button type="submit" disabled={processing}>Create Task</Button>
          <Link href="/admin/dashboard">
            <Button type="button" variant="outline">Cancel</Button>
          </Link>
        </div>
      </form>
    </AppLayout>
  );
}
