import AppLayout from '@/layouts/app-layout';
import { Head, usePage, Link } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { Avatar } from '@/components/ui/avatar';
import { cn } from '@/lib/utils';

type PageProps = {
  totals: Record<string, number>;
  usersByExerciseReads: Array<{ id: number; title: string; readers_count: number }>;
  topJournalers: Array<{ user_id: number; entries_count: number }>;
  topDoers: Array<{ user_id: number; done_count: number }>;
  recentUsers: Array<{ id: number; name: string; email: string; avatar?: string | null; created_at: string; tasks_done_count: number; journals_count: number; }>;
  generatedAt: string;
};

export default function AdminDashboard() {
  const { props } = usePage<PageProps>();
  const { totals, usersByExerciseReads, topJournalers, topDoers, recentUsers, generatedAt } = props;

  const activePct = totals.users ? Math.round(((totals.activeUsers7d || 0) / totals.users) * 100) : 0;
  const maxReaders = usersByExerciseReads.reduce((m, e) => Math.max(m, e.readers_count || 0), 0) || 1;

  return (
    <AppLayout breadcrumbs={[{ title: 'Dashboard', href: '/admin/dashboard' }]}> 
      <Head title="Admin Dashboard" />
      <div className="flex flex-col gap-6 p-4">
        {/* Header actions */}
        <div className="flex items-center justify-between">
          <div>
            <h1 className="text-2xl font-semibold">Dashboard</h1>
            <p className="text-sm opacity-70">Plan, monitor, and analyze engagement.</p>
          </div>
          <div className="flex gap-2">
            <Button variant="outline" size="sm">Import Data</Button>
            <Link href="/admin/tasks/create">
              <Button size="sm">Add Item</Button>
            </Link>
          </div>
        </div>

        {/* KPI cards */}
        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <Kpi title="Total Users" value={totals.users} hint="All time" />
          <Kpi title="Exercises" value={totals.exercises} hint="Published" />
          <Kpi title="Tasks" value={totals.tasks} hint="Active + Scheduled" />
          <Kpi title="Active (7d)" value={totals.activeUsers7d} hint="Users active last week" />
          <Kpi title="Task Completions (M)" value={totals.taskCompletionsThisMonth || 0} hint="This month" />
          <Kpi title="Journal Entries (M)" value={totals.journalEntriesThisMonth || 0} hint="This month" />
          <Kpi title="All Completions" value={totals.taskCompletions} hint="Lifetime" />
          <Kpi title="All Journals" value={totals.journalEntries} hint="Lifetime" />
        </div>

        {/* Analytics row */}
        <div className="grid gap-4 lg:grid-cols-3">
          <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
            <div className="font-semibold mb-3">Project Analytics</div>
            <ul className="space-y-3">
              {usersByExerciseReads.map((e) => {
                const pct = Math.round(((e.readers_count || 0) / maxReaders) * 100);
                return (
                  <li key={e.id}>
                    <div className="flex items-center justify-between text-sm mb-1">
                      <div className="truncate pr-2">{e.title}</div>
                      <div className="tabular-nums opacity-70">{e.readers_count}</div>
                    </div>
                    <div className="h-2 w-full rounded bg-neutral-200/60 dark:bg-neutral-800 overflow-hidden">
                      <div className="h-2 bg-emerald-500" style={{ width: `${pct}%` }} />
                    </div>
                  </li>
                );
              })}
            </ul>
          </div>

          <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
            <div className="font-semibold mb-3">Top Journalers</div>
            <ul className="space-y-2">
              {topJournalers.map((u) => (
                <li key={u.user_id} className="flex items-center justify-between text-sm">
                  <span>User #{u.user_id}</span>
                  <span className="tabular-nums">{u.entries_count}</span>
                </li>
              ))}
            </ul>
          </div>

          <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
            <div className="font-semibold mb-3">Engagement (7d)</div>
            <div className="flex items-center gap-4">
              <div
                className="relative size-28 rounded-full"
                style={{
                  background: `conic-gradient(#10b981 ${activePct}%, rgba(0,0,0,0.08) 0)`
                }}
              >
                <div className="absolute inset-2 rounded-full bg-background flex items-center justify-center">
                  <div className="text-xl font-semibold">{activePct}%</div>
                </div>
              </div>
              <div className="text-sm opacity-80">
                <div className="font-medium">Active Users</div>
                <div>{totals.activeUsers7d || 0} of {totals.users || 0} were active</div>
              </div>
            </div>
          </div>
        </div>

        {/* Users & activity */}
        <div className="grid gap-4 lg:grid-cols-3">
          <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4 lg:col-span-2">
            <div className="flex items-center justify-between mb-3">
              <div className="font-semibold">Team Collaboration</div>
              <Button size="sm" variant="outline">+ Add Member</Button>
            </div>
            <ul className="divide-y divide-neutral-200/60 dark:divide-neutral-800">
              {recentUsers.map((u) => (
                <li key={u.id} className="flex items-center justify-between py-3">
                  <div className="flex items-center gap-3 min-w-0">
                    <Avatar src={u.avatar ?? undefined} alt={u.name} className="size-8" />
                    <div className="min-w-0">
                      <div className="text-sm font-medium truncate">{u.name}</div>
                      <div className="text-xs opacity-70 truncate">{u.email}</div>
                    </div>
                  </div>
                  <div className="flex items-center gap-6 text-sm">
                    <div className="text-right">
                      <div className="font-medium tabular-nums">{u.tasks_done_count}</div>
                      <div className="text-xs opacity-70">Tasks Done</div>
                    </div>
                    <div className="text-right">
                      <div className="font-medium tabular-nums">{u.journals_count}</div>
                      <div className="text-xs opacity-70">Journaled</div>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
          </div>
          <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
            <div className="font-semibold mb-3">Top Doers</div>
            <ul className="space-y-2">
              {topDoers.map((u) => (
                <li key={u.user_id} className="flex items-center justify-between text-sm">
                  <span>User #{u.user_id}</span>
                  <span className="tabular-nums">{u.done_count}</span>
                </li>
              ))}
            </ul>
          </div>
        </div>

        <div className="text-xs opacity-60">Generated at {generatedAt}</div>
      </div>
    </AppLayout>
  );
}

function Kpi({ title, value, hint }: { title: string; value?: number; hint?: string }) {
  return (
    <div className="rounded-xl border border-sidebar-border/70 dark:border-sidebar-border p-4">
      <div className="text-xs uppercase opacity-70">{title}</div>
      <div className="text-2xl font-semibold">{value ?? 0}</div>
      {hint && <div className="text-xs opacity-60">{hint}</div>}
    </div>
  );
}
