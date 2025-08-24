<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { ref } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import NotesPanel from '@/components/NotesPanel.vue'
import TaskCreationDrawer from '@/components/TaskCreationDrawer.vue'
import { FolderOpen, ArrowLeft, Plus, Edit, Calendar, CheckCircle2, Clock, AlertCircle } from 'lucide-vue-next'

interface ProjectDto {
    id: number
    name: string
    description?: string
    created_at: string
    updated_at: string
    tasks_count?: number
}

interface TaskDto {
    id: number
    title: string
    description?: string
    status: 'pending' | 'in_progress' | 'done'
    created_at: string
    updated_at: string
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
    project: ProjectDto
    projectNotes: NoteDto[]
    projectTasks: TaskDto[]
}

const props = defineProps<Props>()

// Drawer state for adding tasks
const isDrawerOpen = ref(false)

// NotesPanel reference
const notesPanelRef = ref()

function openAddNoteForm() {
    if (notesPanelRef.value) {
        notesPanelRef.value.toggleAddForm()
    }
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Projets', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
]

function openTaskDrawer() {
    isDrawerOpen.value = true
}

function handleTaskCreated() {
    // Refresh the page data to show the new task
    router.reload({ only: ['projectTasks'], preserveState: true, preserveScroll: true })
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getTaskStatusIcon(status: TaskDto['status']) {
    switch (status) {
        case 'done':
            return CheckCircle2
        case 'in_progress':
            return Clock
        default:
            return AlertCircle
    }
}

function getTaskStatusColor(status: TaskDto['status']) {
    switch (status) {
        case 'done':
            return 'text-green-600 dark:text-green-400'
        case 'in_progress':
            return 'text-blue-600 dark:text-blue-400'
        default:
            return 'text-gray-600 dark:text-gray-400'
    }
}

function getTaskStatusLabel(status: TaskDto['status']) {
    switch (status) {
        case 'done':
            return 'Terminée'
        case 'in_progress':
            return 'En cours'
        default:
            return 'En attente'
    }
}
</script>

<template>
    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex flex-col gap-4 sm:gap-6">
                <!-- Top row: Back button and title -->
                <div class="flex items-start gap-4">
                    <Button variant="outline" size="sm" as-child class="shrink-0">
                        <Link href="/projects">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            <span class="hidden sm:inline">Retour</span>
                            <span class="sm:hidden">Retour</span>
                        </Link>
                    </Button>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-2">
                            <FolderOpen class="h-5 w-5 sm:h-6 sm:w-6 text-purple-600 shrink-0" />
                            <h1 class="text-lg sm:text-2xl font-bold text-gray-900 dark:text-white truncate">
                                {{ project.name }}
                            </h1>
                        </div>
                        <div class="flex items-center gap-2">
                            <Badge v-if="project.tasks_count !== undefined" variant="outline" class="text-xs">
                                {{ project.tasks_count }} tâche{{ project.tasks_count > 1 ? 's' : '' }}
                            </Badge>
                        </div>
                    </div>
                </div>

                <!-- Bottom row: Action buttons (stacked on mobile) -->
                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:justify-end">
                    <Button variant="outline" as-child class="w-full sm:w-auto">
                        <Link :href="`/projects/${project.id}/edit`">
                            <Edit class="h-4 w-4 mr-2" />
                            Modifier
                        </Link>
                    </Button>
                    <Button @click="openTaskDrawer" class="w-full sm:w-auto">
                        <Plus class="h-4 w-4 mr-2" />
                        Nouvelle tâche
                    </Button>
                </div>
            </div>

            <!-- Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Project Details -->
                <Card class="lg:col-span-2 p-6">
                    <div class="space-y-6">
                        <div v-if="project.description">
                            <h3 class="text-lg font-semibold mb-3">Description</h3>
                            <p class="text-gray-700 dark:text-gray-300 whitespace-pre-wrap">
                                {{ project.description }}
                            </p>
                        </div>

                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Créé le</span>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDate(project.created_at) }}
                                </p>
                            </div>
                            <div>
                                <span class="font-medium text-gray-500 dark:text-gray-400">Modifié le</span>
                                <p class="text-gray-900 dark:text-white">
                                    {{ formatDate(project.updated_at) }}
                                </p>
                            </div>
                        </div>

                        <!-- Tasks Section -->
                        <div class="mt-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Tâches du projet ({{ projectTasks.length }})</h3>
                                <Button size="sm" @click="openTaskDrawer">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Ajouter une tâche
                                </Button>
                            </div>

                            <!-- Task List -->
                            <div v-if="projectTasks.length > 0" class="space-y-3">
                                <div
                                    v-for="task in projectTasks"
                                    :key="task.id"
                                    class="flex items-start gap-3 p-4 border border-border rounded-lg hover:bg-muted/50 transition-colors"
                                >
                                    <component
                                        :is="getTaskStatusIcon(task.status)"
                                        class="h-5 w-5 mt-0.5 shrink-0"
                                        :class="getTaskStatusColor(task.status)"
                                    />
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-1">
                                            <h4 class="font-medium text-gray-900 dark:text-white truncate">
                                                {{ task.title }}
                                            </h4>
                                            <Badge
                                                variant="secondary"
                                                class="text-xs shrink-0"
                                                :class="{
                                                    'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400': task.status === 'done',
                                                    'bg-blue-100 text-blue-800 dark:bg-blue-900/20 dark:text-blue-400': task.status === 'in_progress',
                                                    'bg-gray-100 text-gray-800 dark:bg-gray-900/20 dark:text-gray-400': task.status === 'pending'
                                                }"
                                            >
                                                {{ getTaskStatusLabel(task.status) }}
                                            </Badge>
                                        </div>
                                        <p v-if="task.description" class="text-sm text-gray-600 dark:text-gray-400 mb-2 line-clamp-2">
                                            {{ task.description }}
                                        </p>
                                        <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                                            <span class="flex items-center gap-1">
                                                <Calendar class="h-3 w-3" />
                                                Créée {{ formatDate(task.created_at) }}
                                            </span>
                                            <Link
                                                :href="`/tasks/${task.id}`"
                                                class="text-primary hover:text-primary/80 font-medium"
                                            >
                                                Voir détails →
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Empty State -->
                            <div v-else class="text-center py-12 text-gray-500">
                                <FolderOpen class="h-12 w-12 mx-auto mb-3 text-gray-300" />
                                <h4 class="font-medium text-gray-900 dark:text-white mb-1">Aucune tâche</h4>
                                <p class="text-sm">Ce projet ne contient pas encore de tâches.</p>
                                <Button class="mt-4" size="sm" @click="openTaskDrawer">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Créer la première tâche
                                </Button>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Notes Panel -->
                <Card class="lg:col-span-1 p-0">
                    <div class="p-4 border-b border-border">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold">Notes</h3>
                                <p class="text-sm text-gray-500 mt-1">
                                    Notes liées à ce projet
                                </p>
                            </div>
                            <Button @click="openAddNoteForm" size="sm" variant="default">
                                <Plus class="w-4 h-4 mr-1" />
                                Ajouter
                            </Button>
                        </div>
                    </div>
                    <NotesPanel
                        ref="notesPanelRef"
                        :noteable-type="'App\\Models\\Project'"
                        :noteable-id="project.id"
                        :initial-notes="projectNotes"
                        :hide-header="true"
                    />
                </Card>
            </div>
        </div>

        <!-- Task Creation Drawer Component -->
        <TaskCreationDrawer
            v-model:is-open="isDrawerOpen"
            :project="project"
            @task-created="handleTaskCreated"
        />
    </AppLayout>
</template>
