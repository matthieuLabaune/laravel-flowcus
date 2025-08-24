<script setup lang="ts">
import { Head } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Badge } from '@/components/ui/badge'
import NotesPanel from '@/components/NotesPanel.vue'

interface Note {
  id: number
  content: string
  created_at: string
  updated_at: string
  noteable_type: string
  noteable_id: number
  noteable?: {
    id: number
    title?: string
    name?: string
  }
}

interface Props {
  notes?: Note[]
  sessionNotes?: Note[]
  taskNotes?: Note[]
  projectNotes?: Note[]
}

const props = withDefaults(defineProps<Props>(), {
  notes: () => [],
  sessionNotes: () => [],
  taskNotes: () => [],
  projectNotes: () => [],
})

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Notes', href: '/notes' },
]

const activeTab = ref('all')

const allNotes = computed(() => [
  ...props.sessionNotes.map(note => ({ ...note, type: 'session' })),
  ...props.taskNotes.map(note => ({ ...note, type: 'task' })),
  ...props.projectNotes.map(note => ({ ...note, type: 'project' })),
].sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()))

const getTypeLabel = (type: string) => {
  switch(type) {
    case 'session': return 'Session'
    case 'task': return 'Tâche'
    case 'project': return 'Projet'
    default: return type
  }
}

const getTypeColor = (type: string) => {
  switch(type) {
    case 'session': return 'bg-purple-100 text-purple-700 dark:bg-purple-600/20 dark:text-purple-300'
    case 'task': return 'bg-blue-100 text-blue-700 dark:bg-blue-600/20 dark:text-blue-300'
    case 'project': return 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300'
    default: return 'bg-gray-100 text-gray-700 dark:bg-gray-600/20 dark:text-gray-300'
  }
}

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  const now = new Date()
  const diffInHours = (now.getTime() - date.getTime()) / (1000 * 60 * 60)

  if (diffInHours < 1) {
    const diffInMinutes = Math.floor(diffInHours * 60)
    return diffInMinutes <= 1 ? 'À l\'instant' : `Il y a ${diffInMinutes} min`
  } else if (diffInHours < 24) {
    return `Il y a ${Math.floor(diffInHours)} h`
  } else {
    return date.toLocaleDateString('fr-FR', {
      day: 'numeric',
      month: 'short',
      year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
    })
  }
}
</script>

<template>
  <Head title="Notes" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="flex flex-col gap-6 p-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-semibold tracking-tight">Notes</h1>
          <p class="text-muted-foreground">
            Gérez toutes vos notes en un seul endroit
          </p>
        </div>
      </div>

      <!-- Tabs -->
      <Tabs v-model="activeTab" default-value="all" class="w-full">
        <TabsList class="grid w-full grid-cols-4">
          <TabsTrigger value="all">
            Toutes
            <Badge variant="secondary" class="ml-2">{{ allNotes.length }}</Badge>
          </TabsTrigger>
          <TabsTrigger value="sessions">
            Sessions
            <Badge variant="secondary" class="ml-2">{{ sessionNotes.length }}</Badge>
          </TabsTrigger>
          <TabsTrigger value="tasks">
            Tâches
            <Badge variant="secondary" class="ml-2">{{ taskNotes.length }}</Badge>
          </TabsTrigger>
          <TabsTrigger value="projects">
            Projets
            <Badge variant="secondary" class="ml-2">{{ projectNotes.length }}</Badge>
          </TabsTrigger>
        </TabsList>

        <!-- All Notes -->
        <TabsContent value="all" class="space-y-4">
          <Card class="p-6">
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Toutes les notes</h3>

              <div v-if="allNotes.length === 0" class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p class="mt-2 text-sm text-muted-foreground">Aucune note trouvée</p>
                <p class="text-xs text-muted-foreground">Créez votre première note depuis le dashboard</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="note in allNotes"
                  :key="note.id"
                  class="flex items-start gap-4 p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors"
                >
                  <div class="flex-1 space-y-2">
                    <div class="flex items-center gap-2">
                      <Badge :class="getTypeColor(note.type)" variant="secondary">
                        {{ getTypeLabel(note.type) }}
                      </Badge>
                      <span class="text-xs text-muted-foreground">
                        {{ formatDate(note.created_at) }}
                      </span>
                    </div>

                    <p class="text-sm leading-relaxed whitespace-pre-wrap">{{ note.content }}</p>

                    <div v-if="note.noteable" class="text-xs text-muted-foreground">
                      {{ note.noteable.title || note.noteable.name || `#${note.noteable.id}` }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </TabsContent>

        <!-- Session Notes -->
        <TabsContent value="sessions" class="space-y-4">
          <Card class="p-6">
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Notes de sessions Pomodoro</h3>

              <div v-if="sessionNotes.length === 0" class="text-center py-8">
                <p class="text-sm text-muted-foreground">Aucune note de session</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="note in sessionNotes"
                  :key="note.id"
                  class="p-4 border border-border rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-muted-foreground">
                      {{ formatDate(note.created_at) }}
                    </span>
                  </div>
                  <p class="text-sm whitespace-pre-wrap">{{ note.content }}</p>
                </div>
              </div>
            </div>
          </Card>
        </TabsContent>

        <!-- Task Notes -->
        <TabsContent value="tasks" class="space-y-4">
          <Card class="p-6">
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Notes de tâches</h3>

              <div v-if="taskNotes.length === 0" class="text-center py-8">
                <p class="text-sm text-muted-foreground">Aucune note de tâche</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="note in taskNotes"
                  :key="note.id"
                  class="p-4 border border-border rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-muted-foreground">
                      {{ formatDate(note.created_at) }}
                    </span>
                  </div>
                  <p class="text-sm whitespace-pre-wrap">{{ note.content }}</p>
                  <div v-if="note.noteable" class="mt-2 text-xs text-muted-foreground">
                    Tâche: {{ note.noteable.title || `#${note.noteable.id}` }}
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </TabsContent>

        <!-- Project Notes -->
        <TabsContent value="projects" class="space-y-4">
          <Card class="p-6">
            <div class="space-y-4">
              <h3 class="text-lg font-medium">Notes de projets</h3>

              <div v-if="projectNotes.length === 0" class="text-center py-8">
                <p class="text-sm text-muted-foreground">Aucune note de projet</p>
              </div>

              <div v-else class="space-y-3">
                <div
                  v-for="note in projectNotes"
                  :key="note.id"
                  class="p-4 border border-border rounded-lg"
                >
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-muted-foreground">
                      {{ formatDate(note.created_at) }}
                    </span>
                  </div>
                  <p class="text-sm whitespace-pre-wrap">{{ note.content }}</p>
                  <div v-if="note.noteable" class="mt-2 text-xs text-muted-foreground">
                    Projet: {{ note.noteable.name || `#${note.noteable.id}` }}
                  </div>
                </div>
              </div>
            </div>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </AppLayout>
</template>
