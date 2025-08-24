<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { computed, ref } from 'vue'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import type { BreadcrumbItem } from '@/types'

interface TaskDto { id:number; title:string; status:string; project_id?:number|null; deadline_at?:string|null }

const page = usePage()
const tasks = computed(() => (page.props as any).tasks as TaskDto[] || [])
const meta = computed(() => (page.props as any).meta || {})
const filters = ref<{status?:string|null; project_id?:number|null}>({ ...(page.props as any).filters })

const newTitle = ref('')
const creating = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Tasks', href: '/tasks' },
]

function applyFilters() {
  router.get(route('tasks.index'), { ...filters.value }, { preserveState: true, preserveScroll: true, replace: true })
}

function quickCreate() {
  if (!newTitle.value.trim()) return
  creating.value = true
  router.post(route('tasks.store'), { title: newTitle.value, project_id: filters.value.project_id }, {
    preserveScroll: true,
    onFinish: () => (creating.value = false),
    onSuccess: () => {
      newTitle.value = ''
      router.reload({ only: ['tasks', 'meta'] })
    }
  })
}

function completeTask(t: TaskDto) {
  router.post(route('tasks.complete', t.id), {}, {
    preserveState: true,
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['tasks'] })
  })
}

function removeTask(t: TaskDto) {
  if (!confirm('Delete task?')) return
  router.delete(route('tasks.destroy', t.id), {
    preserveScroll: true,
    onSuccess: () => router.reload({ only: ['tasks', 'meta'] })
  })
}
</script>

<template>
  <Head title="Tasks" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 flex flex-col gap-6">
      <Card class="p-5 gap-4">
        <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Filters</h2>
        <div class="flex flex-wrap gap-3 items-end">
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-muted-foreground">Status</label>
            <select v-model="filters.status" class="h-9 rounded-md border bg-transparent px-2 text-sm min-w-40">
              <option :value="null">All</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In progress</option>
              <option value="done">Done</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label class="text-xs font-medium text-muted-foreground">Project ID</label>
            <Input :value="filters.project_id ?? ''" @input="filters.project_id = ($event.target as HTMLInputElement).value ? Number(($event.target as HTMLInputElement).value) : null" placeholder="id" type="number" />
          </div>
          <Button size="sm" @click="applyFilters">Appliquer</Button>
        </div>
      </Card>
      <Card class="p-5 gap-4">
        <div class="flex items-center justify-between">
          <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Tasks ({{ meta.total || tasks.length }})</h2>
        </div>
        <form class="flex gap-2" @submit.prevent="quickCreate">
          <Input v-model="newTitle" placeholder="Nouvelle tâche" class="flex-1" />
          <Button size="sm" :disabled="creating || !newTitle.trim()">Ajouter</Button>
        </form>
        <ul class="divide-y divide-border rounded border">
          <li v-for="t in tasks" :key="t.id" class="flex items-center justify-between gap-4 px-3 py-2 hover:bg-muted/50">
            <div class="flex flex-col flex-1 min-w-0">
              <Link :href="`/tasks/${t.id}`" class="text-sm font-medium hover:text-primary transition-colors" :class="t.status === 'done' && 'line-through text-muted-foreground'">
                {{ t.title }}
              </Link>
            </div>
            <div class="flex gap-2 shrink-0">
              <Button v-if="t.status !== 'done'" size="sm" variant="secondary" @click="completeTask(t)">Done</Button>
              <Button size="sm" variant="ghost" @click="removeTask(t)">Suppr</Button>
            </div>
          </li>
          <li v-if="!tasks.length" class="p-4 text-center text-sm text-muted-foreground">Aucune tâche.</li>
        </ul>
      </Card>
    </div>
  </AppLayout>
</template>
