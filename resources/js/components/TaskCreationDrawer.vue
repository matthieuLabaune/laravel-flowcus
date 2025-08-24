<script setup lang="ts">
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
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

interface ProjectDto {
  id: number
  name: string
  color?: string | null
}

interface Props {
  isOpen: boolean
  project: ProjectDto | null
}

interface Emits {
  (e: 'update:isOpen', value: boolean): void
  (e: 'taskCreated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Local reactive state
const newTaskTitle = ref('')
const newTaskDescription = ref('')
const creatingTask = ref(false)
const showSuccessModal = ref(false)
const lastCreatedTaskTitle = ref('')

// Computed for v-model binding
const isDrawerOpen = computed({
  get: () => props.isOpen,
  set: (value) => emit('update:isOpen', value)
})

function closeTaskDrawer() {
  isDrawerOpen.value = false
  newTaskTitle.value = ''
  newTaskDescription.value = ''
}

function submitNewTask() {
  if (!newTaskTitle.value.trim() || !props.project) return

  creatingTask.value = true
  router.post(route('tasks.store'), {
    title: newTaskTitle.value,
    description: newTaskDescription.value,
    project_id: props.project.id
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

      // Notify parent component that a task was created
      emit('taskCreated')
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
</script>

<template>
  <!-- Task Creation Drawer -->
  <Drawer v-model:open="isDrawerOpen" direction="right">
    <DrawerContent class="h-screen top-0 right-0 left-auto mt-0 rounded-none">
      <div class="mx-auto w-full p-4 sm:p-6">
        <DrawerHeader class="px-0">
          <DrawerTitle class="text-lg sm:text-xl">Nouvelle tâche</DrawerTitle>
          <DrawerDescription v-if="project" class="text-sm sm:text-base">
            Ajouter une tâche au projet
            <span class="inline-flex items-center gap-1">
              <span class="w-2 h-2 rounded-full" :style="project.color ? ('background:' + project.color) : 'background: #6b7280'"></span>
              <span class="font-medium">{{ project.name }}</span>
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
          <span class="font-medium">{{ project?.name }}</span>.
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
</template>
