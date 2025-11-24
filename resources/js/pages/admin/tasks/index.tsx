import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';
import AppDataGrid from '@/components/datagrid';
import type { GridColDef } from '@mui/x-data-grid';

export default function TasksIndex() {
  const { props } = usePage<{ tasks: Array<{ id: number; title: string; exercise_id: number; schedule?: string | null; duration_days?: number | null; start_date?: string | null; is_active: boolean; sort_order?: number | null; created_at: string; exercise?: { id: number; title: string } }>; }>();
  const rows = props.tasks?.map((t) => ({ id: t.id, title: t.title, exercise: t.exercise?.title ?? `#${t.exercise_id}`, schedule: t.schedule ?? '', duration_days: t.duration_days ?? 0, start_date: t.start_date ?? '', is_active: t.is_active, sort_order: t.sort_order ?? 0, created_at: t.created_at })) ?? [];

  const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 80 },
    { field: 'title', headerName: 'Title', flex: 1, minWidth: 200 },
    { field: 'exercise', headerName: 'Exercise', flex: 1, minWidth: 200 },
    { field: 'schedule', headerName: 'Schedule', width: 120 },
    { field: 'duration_days', headerName: 'Duration', width: 100, type: 'number' },
    { field: 'start_date', headerName: 'Start', width: 130 },
    { field: 'is_active', headerName: 'Active', width: 90, type: 'boolean' },
    { field: 'sort_order', headerName: 'Sort', width: 90, type: 'number' },
    { field: 'created_at', headerName: 'Created', width: 160 },
  ];

  return (
    <AppLayout breadcrumbs={[{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Tasks', href: '/admin/tasks' }]}> 
      <Head title="Tasks" />
      <div className="p-4">
        <div className="mb-3">
          <Link href="/admin/tasks/create" className="text-sm underline">+ Create Task</Link>
        </div>
        <AppDataGrid rows={rows} columns={columns} pageSizeOptions={[10,25,50]} title="Tasks" excel={{ rows, fileName: 'tasks.xlsx', sheetName: 'Tasks' }} />
      </div>
    </AppLayout>
  );
}
