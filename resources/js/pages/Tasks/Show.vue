<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import { computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import NotesPanel from '@/components/NotesPanel.vue'
import { CheckCircle, Clock, Calendar, ArrowLeft } from 'lucide-vue-next'

interface TaskDto {
    id: number
    title: string
    description?: string
    status: string
    deadline_at?: string | null
    completed_at?: string | null
    created_at: string
    updated_at: string
    project?: {
        id: number
        title: string
    } | null
}

interface NoteDto {
    id: number
    content: string
    created_at: string
    updated_at: string
    noteable_type: string
    noteable_id: number
}

interface Props {
    task: TaskDto
    taskNotes: NoteDto[]
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Tâches', href: '/tasks' },
    { title: props.task.title, href: `/tasks/${props.task.id}` },
]

const statusColor = computed(() => {
    switch (props.task.status) {
        case 'done':
            return 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400'
        case 'in_progress':
            return 'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400'
        case 'pending':
        default:
            return 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
    }
})

const statusLabel = computed(() => {
    switch (props.task.status) {
        case 'done':
            return 'Terminée'
        case 'in_progress':
            return 'En cours'
        case 'pending':
        default:
            return 'En attente'
    }
})

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}
</script>

<template>
    <Head :title="task.title" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/tasks">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour
                        </Link>
                    </Button>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                            {{ task.title }}
                        </h1>
                        <div class="flex items-center gap-2 mt-2">
                            <Badge :class="statusColor">
                                <component :is="task.status === 'done' ? CheckCircle : Clock" class="h-3 w-3 mr-1" />
                                {{ statusLabel }}
                            </Badge>
                            <span v-if="task.project" class="text-sm text-gray-500">
                                dans {{ task.project.title }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        Modifier
                    </Button>
                    <Button v-if="task.status !== 'done'" variant="default">
                        Marquer comme terminée
                    </Button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Task Details -->
                <Card class="lg:col-span-2 p-6">
                    <div class="space-y-6">
                        <div v-if="task.description">
                            <h3 class="text-lg font-semibold mb-3">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {{ task.description }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Créée le</span>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDate(task.created_at) }}
                                </p>
                            </div>
                            <div v-if="task.deadline_at">
                                <span class="font-medium text-gray-500 dark:text-gray-400 flex items-center gap-1">
                                    <Calendar class="h-4 w-4" />
                                    Échéance
                                </span>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDate(task.deadline_at) }}
                                </p>
                            </div>
                            <div v-if="task.completed_at">
                                <span class="font-medium text-gray-500 dark:text-gray-400">Terminée le</span>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDate(task.completed_at) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Notes Panel -->
                <Card class="lg:col-span-1 p-0">
                    <div class="p-4 border-b border-border">
                        <h3 class="text-lg font-semibold">Notes</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Notes liées à cette tâche
                        </p>
                    </div>
                    <NotesPanel
                        :noteable-type="'App\\Models\\Task'"
                        :noteable-id="task.id"
                        :initial-notes="taskNotes"
                    />
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
