<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import TaskCreationDrawer from '@/components/TaskCreationDrawer.vue'
import { Plus } from 'lucide-vue-next'
import { type BreadcrumbItem } from '@/types'

interface ProjectDto {
  id: number
  name: string
  color?: string | null
  tasks_count?: number
}

const page = usePage()
const projects = computed(() => (page.props as any).projects as ProjectDto[] || [])

// Drawer state for adding tasks
const isDrawerOpen = ref(false)
const selectedProject = ref<ProjectDto | null>(null)

const name = ref('')
const color = ref('#8855ff')
const creating = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Projects', href: '/projects' },
]

function createProject() {
  if (!name.value.trim()) return
  creating.value = true
  router.post(route('projects.store'), { name: name.value, color: color.value }, {
    preserveScroll: true,
    onFinish: () => (creating.value = false),
    onSuccess: () => {
      name.value = ''
    }
  })
}

function destroyProject(p: ProjectDto) {
  if (!confirm('Delete project?')) return
  router.delete(route('projects.destroy', p.id), {
    preserveScroll: true,
  })
}

function openTaskDrawer(project: ProjectDto) {
  selectedProject.value = project
  isDrawerOpen.value = true
}

function handleTaskCreated() {
  // Refresh the projects list to update task counts
  router.reload({ only: ['projects'], preserveState: true, preserveScroll: true })
}

const totalTasks = computed(() => projects.value.reduce((acc, p) => acc + (p.tasks_count || 0), 0))
</script>

<template>
  <Head title="Projects" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-4 flex flex-col gap-6">
      <div class="grid gap-6 md:grid-cols-3">
        <Card class="p-5 gap-4 md:col-span-1">
          <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">New Project</h2>
          <form class="flex flex-col gap-3" @submit.prevent="createProject">
            <div class="flex flex-col gap-1">
              <label class="text-xs font-medium text-muted-foreground">Name</label>
              <Input v-model="name" placeholder="Project name" />
            </div>
            <div class="flex flex-col gap-1">
              <label class="text-xs font-medium text-muted-foreground">Color</label>
              <input v-model="color" type="color" class="h-9 w-16 rounded border border-border p-1 bg-transparent" />
            </div>
            <Button :disabled="creating || !name.trim()">Create</Button>
          </form>
        </Card>
        <Card class="p-5 gap-4 md:col-span-2">
          <div class="flex items-center justify-between">
            <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Projects ({{ projects.length }})</h2>
            <span class="text-xs text-muted-foreground">{{ totalTasks }} tasks</span>
          </div>
          <ul class="divide-y divide-border rounded-md border">
            <li v-for="p in projects" :key="p.id" class="flex items-center justify-between gap-4 px-3 py-2 hover:bg-muted/50">
              <div class="flex items-center gap-3 flex-1 min-w-0">
                <span class="size-4 rounded-full border shrink-0" :style="p.color ? ('background:' + p.color) : ''" />
                <div class="flex flex-col min-w-0">
                  <Link :href="`/projects/${p.id}`" class="text-sm font-medium hover:text-primary transition-colors truncate">
                    {{ p.name }}
                  </Link>
                  <span class="text-xs text-muted-foreground">{{ p.tasks_count || 0 }} tasks</span>
                </div>
              </div>
              <div class="flex gap-2 shrink-0">
                <Button variant="secondary" size="sm" @click="openTaskDrawer(p)">
                  <Plus class="h-3 w-3 mr-1" />
                  Task
                </Button>
                <Button variant="destructive" size="sm" @click="destroyProject(p)">Delete</Button>
              </div>
            </li>
            <li v-if="!projects.length" class="p-4 text-center text-sm text-muted-foreground">No projects yet.</li>
          </ul>
        </Card>
      </div>
    </div>

    <!-- Task Creation Drawer Component -->
    <TaskCreationDrawer
      v-model:is-open="isDrawerOpen"
      :project="selectedProject"
      @task-created="handleTaskCreated"
    />
  </AppLayout>
</template>
