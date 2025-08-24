import type { ColumnDef } from '@tanstack/vue-table'
import { h } from 'vue'
import { Badge } from '@/components/ui/badge'
import { Checkbox } from '@/components/ui/checkbox'
import { Button } from '@/components/ui/button'
import { ArrowUpDown } from 'lucide-vue-next'

export interface Task {
  id: number
  title: string
  status: 'pending' | 'in_progress' | 'done'
  priority?: 'low' | 'medium' | 'high'
  deadline_at?: string | null
  completed_at?: string | null
}

export const taskColumns: ColumnDef<Task>[] = [
  {
    id: 'select',
    header: ({ table }) => h(Checkbox, {
      'modelValue': table.getIsAllPageRowsSelected() || (table.getIsSomePageRowsSelected() && 'indeterminate'),
      'onUpdate:modelValue': value => table.toggleAllPageRowsSelected(!!value),
      'ariaLabel': 'Select all',
      'class': 'translate-y-0.5',
    }),
    cell: ({ row }) => h(Checkbox, {
      'modelValue': row.getIsSelected(),
      'onUpdate:modelValue': value => row.toggleSelected(!!value),
      'ariaLabel': 'Select row',
      'class': 'translate-y-0.5',
    }),
    enableSorting: false,
    enableHiding: false,
  },
  {
    accessorKey: 'title',
    header: ({ column }) => {
      return h(Button, {
        variant: 'ghost',
        onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        class: 'h-auto p-0 hover:bg-transparent',
      }, () => [
        'Titre',
        h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
      ])
    },
    cell: ({ row }) => {
      const task = row.original
      return h('div', { class: 'flex flex-col' }, [
        h('span', {
          class: `font-medium ${task.status === 'done' ? 'line-through text-muted-foreground' : ''}`
        }, task.title),
        task.deadline_at ? h('span', {
          class: 'text-xs text-muted-foreground'
        }, `Échéance: ${new Date(task.deadline_at).toLocaleDateString('fr-FR')}`) : null
      ])
    },
  },
  {
    accessorKey: 'status',
    header: ({ column }) => {
      return h(Button, {
        variant: 'ghost',
        onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        class: 'h-auto p-0 hover:bg-transparent',
      }, () => [
        'Statut',
        h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
      ])
    },
    cell: ({ row }) => {
      const status = row.getValue('status') as string
      const statusConfig = {
        pending: { label: 'En attente', class: 'bg-yellow-100 text-yellow-700 dark:bg-yellow-600/20 dark:text-yellow-300' },
        in_progress: { label: 'En cours', class: 'bg-blue-100 text-blue-700 dark:bg-blue-600/20 dark:text-blue-300' },
        done: { label: 'Terminé', class: 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' }
      }

      const config = statusConfig[status as keyof typeof statusConfig] || statusConfig.pending

      return h(Badge, {
        variant: 'secondary',
        class: `${config.class} capitalize`
      }, () => config.label)
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  },
  {
    accessorKey: 'priority',
    header: ({ column }) => {
      return h(Button, {
        variant: 'ghost',
        onClick: () => column.toggleSorting(column.getIsSorted() === 'asc'),
        class: 'h-auto p-0 hover:bg-transparent',
      }, () => [
        'Priorité',
        h(ArrowUpDown, { class: 'ml-2 h-4 w-4' })
      ])
    },
    cell: ({ row }) => {
      const priority = row.getValue('priority') as string
      if (!priority) return h('span', { class: 'text-muted-foreground' }, '—')

      const priorityConfig = {
        low: { label: 'Faible', class: 'bg-gray-100 text-gray-700 dark:bg-gray-600/20 dark:text-gray-300' },
        medium: { label: 'Moyenne', class: 'bg-orange-100 text-orange-700 dark:bg-orange-600/20 dark:text-orange-300' },
        high: { label: 'Élevée', class: 'bg-red-100 text-red-700 dark:bg-red-600/20 dark:text-red-300' }
      }

      const config = priorityConfig[priority as keyof typeof priorityConfig]
      if (!config) return h('span', { class: 'text-muted-foreground' }, '—')

      return h(Badge, {
        variant: 'outline',
        class: `${config.class} capitalize`
      }, () => config.label)
    },
    filterFn: (row, id, value) => {
      return value.includes(row.getValue(id))
    },
  }
]
