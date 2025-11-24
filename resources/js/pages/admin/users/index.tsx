import AppLayout from '@/layouts/app-layout';
import { Head, Link, usePage } from '@inertiajs/react';
import AppDataGrid from '@/components/datagrid';
import type { GridColDef } from '@mui/x-data-grid';

export default function UsersIndex() {
  const { props } = usePage<{ users: Array<{ id: number; name: string; email: string; avatar?: string | null; created_at: string; tasks_done: number; journals: number; }> }>();
  const rows = props.users?.map((u) => ({ id: u.id, name: u.name, email: u.email, created_at: u.created_at, tasks_done: u.tasks_done, journals: u.journals })) ?? [];

  const columns: GridColDef[] = [
    { field: 'id', headerName: 'ID', width: 80 },
    { field: 'name', headerName: 'Name', flex: 1, minWidth: 180 },
    { field: 'email', headerName: 'Email', flex: 1, minWidth: 220 },
    { field: 'tasks_done', headerName: 'Tasks Done', width: 120, type: 'number' },
    { field: 'journals', headerName: 'Journals', width: 110, type: 'number' },
    { field: 'created_at', headerName: 'Joined', width: 160 },
    {
      field: 'actions', headerName: 'Actions', width: 120, sortable: false, filterable: false, renderCell: (params) => (
        <Link href={`/admin/users/${params.row.id}`}>View</Link>
      )
    },
  ];

  return (
    <AppLayout breadcrumbs={[{ title: 'Admin', href: '/admin/dashboard' }, { title: 'Users', href: '/admin/users' }]}> 
      <Head title="Users" />
      <div className="p-4">
        <AppDataGrid rows={rows} columns={columns} pageSizeOptions={[10,25,50]} title="Users" excel={{ rows, fileName: 'users.xlsx', sheetName: 'Users' }} />
      </div>
    </AppLayout>
  );
}
