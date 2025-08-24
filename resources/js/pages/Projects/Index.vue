<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { Plus, CheckCircle, RefreshCw } from 'lucide-vue-next'
import {
  Drawer,
  DrawerClose,
  DrawerContent,
  DrawerDescription,
  DrawerFooter,
  DrawerHeader,
  DrawerTitle,
} from '@/components/ui/drawer'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { type BreadcrumbItem } from '@/types'

interface ProjectDto { id:number; name:string; color?:string|null; tasks_count?:number }
const page = usePage()
const projects = computed(() => (page.props as any).projects as ProjectDto[] || [])

// Drawer state for adding tasks
const isDrawerOpen = ref(false)
const selectedProject = ref<ProjectDto | null>(null)
const newTaskTitle = ref('')
const newTaskDescription = ref('')
const creatingTask = ref(false)

// Success modal state
const showSuccessModal = ref(false)
const lastCreatedTaskTitle = ref('')

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
  newTaskTitle.value = ''
  newTaskDescription.value = ''
  isDrawerOpen.value = true
}

function closeTaskDrawer() {
  isDrawerOpen.value = false
  selectedProject.value = null
  newTaskTitle.value = ''
  newTaskDescription.value = ''
}

function submitNewTask() {
  if (!newTaskTitle.value.trim() || !selectedProject.value) return

  creatingTask.value = true
  router.post(route('tasks.store'), {
    title: newTaskTitle.value,
    description: newTaskDescription.value,
    project_id: selectedProject.value.id
  }, {
    preserveScroll: true,
    onFinish: () => (creatingTask.value = false),
    onSuccess: () => {
      // Store the task title for the success message
      lastCreatedTaskTitle.value = newTaskTitle.value

      // Reset form but keep drawer open
      newTaskTitle.value = ''
      newTaskDescription.value = ''

      // Show success modal
      showSuccessModal.value = true

      // Refresh the projects list without redirecting
      router.reload({ only: ['projects'], preserveState: true, preserveScroll: true })
    }
  })
}

function createAnotherTask() {
  showSuccessModal.value = false
  // Drawer stays open, form is already reset
}

function closeAllModals() {
  showSuccessModal.value = false
  closeTaskDrawer()
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

    <!-- Task Creation Drawer -->
    <Drawer v-model:open="isDrawerOpen" direction="right">
      <DrawerContent class="h-screen top-0 right-0 left-auto mt-0 rounded-none">
        <div class="mx-auto w-full p-4 sm:p-6">
          <DrawerHeader class="px-0">
            <DrawerTitle class="text-lg sm:text-xl">Nouvelle tâche</DrawerTitle>
            <DrawerDescription v-if="selectedProject" class="text-sm sm:text-base">
              Ajouter une tâche au projet
              <span class="inline-flex items-center gap-1">
                <span class="w-2 h-2 rounded-full" :style="selectedProject.color ? ('background:' + selectedProject.color) : 'background: #6b7280'"></span>
                <span class="font-medium">{{ selectedProject.name }}</span>
              </span>
            </DrawerDescription>
          </DrawerHeader>

          <form @submit.prevent="submitNewTask" class="flex flex-col gap-4 py-4">
            <div class="flex flex-col gap-2">
              <label for="task-title" class="text-sm font-medium text-foreground">
                Titre de la tâche
              </label>
              <Input
                id="task-title"
                v-model="newTaskTitle"
                placeholder="Entrez le titre de la tâche"
                required
                autofocus
                class="text-base"
              />
            </div>

            <div class="flex flex-col gap-2">
              <label for="task-description" class="text-sm font-medium text-foreground">
                Description (optionnelle)
              </label>
              <Textarea
                id="task-description"
                v-model="newTaskDescription"
                placeholder="Décrivez la tâche..."
                class="min-h-[80px] sm:min-h-[100px] text-base"
              />
            </div>

            <DrawerFooter class="px-0 pt-6">
              <Button type="submit" :disabled="creatingTask || !newTaskTitle.trim()" class="w-full sm:w-auto">
                <CheckCircle v-if="!creatingTask" class="h-4 w-4 mr-2" />
                <RefreshCw v-else class="h-4 w-4 mr-2 animate-spin" />
                <span v-if="creatingTask">Création...</span>
                <span v-else>Créer la tâche</span>
              </Button>
              <DrawerClose as-child>
                <Button variant="outline" @click="closeTaskDrawer" class="w-full sm:w-auto">
                  Annuler
                </Button>
              </DrawerClose>
            </DrawerFooter>
          </form>
        </div>
      </DrawerContent>
    </Drawer>

    <!-- Success Confirmation Modal -->
    <Dialog v-model:open="showSuccessModal">
      <DialogContent class="sm:max-w-md w-[95vw] sm:w-full">
        <DialogHeader>
          <DialogTitle class="text-lg sm:text-xl">✅ Tâche créée avec succès !</DialogTitle>
          <DialogDescription class="text-sm sm:text-base">
            La tâche <span class="font-medium">{{ lastCreatedTaskTitle }}</span> a été ajoutée au projet
            <span class="font-medium">{{ selectedProject?.name }}</span>.
          </DialogDescription>
        </DialogHeader>
        <DialogFooter class="flex flex-col sm:flex-row gap-3 sm:justify-center">
          <Button @click="createAnotherTask" variant="outline" class="w-full sm:w-auto">
            <Plus class="h-4 w-4 mr-2" />
            Créer une autre tâche
          </Button>
          <Button @click="closeAllModals" class="w-full sm:w-auto">
            <CheckCircle class="h-4 w-4 mr-2" />
            Fermer
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </AppLayout>
</template>
