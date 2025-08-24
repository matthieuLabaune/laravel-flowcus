<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, Link } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import NotesPanel from '@/components/NotesPanel.vue'
import { FolderOpen, ArrowLeft, Plus } from 'lucide-vue-next'

interface ProjectDto {
    id: number
    name: string
    description?: string
    created_at: string
    updated_at: string
    tasks_count?: number
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
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Projets', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
]

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
    <Head :title="project.name" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Button variant="outline" size="sm" as-child>
                        <Link href="/projects">
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour
                        </Link>
                    </Button>
                    <div>
                        <div class="flex items-center gap-2">
                            <FolderOpen class="h-6 w-6 text-purple-600" />
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                                {{ project.name }}
                            </h1>
                        </div>
                        <div class="flex items-center gap-2 mt-2">
                            <Badge v-if="project.tasks_count !== undefined" variant="outline">
                                {{ project.tasks_count }} tâche{{ project.tasks_count > 1 ? 's' : '' }}
                            </Badge>
                        </div>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline">
                        Modifier
                    </Button>
                    <Button>
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

                        <!-- Tasks Section (for later) -->
                        <div class="mt-8">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-semibold">Tâches du projet</h3>
                                <Button size="sm">
                                    <Plus class="h-4 w-4 mr-2" />
                                    Ajouter une tâche
                                </Button>
                            </div>
                            <div class="text-center py-8 text-gray-500">
                                <p>Les tâches liées à ce projet s'afficheront ici</p>
                            </div>
                        </div>
                    </div>
                </Card>

                <!-- Notes Panel -->
                <Card class="lg:col-span-1 p-0">
                    <div class="p-4 border-b border-border">
                        <h3 class="text-lg font-semibold">Notes</h3>
                        <p class="text-sm text-gray-500 mt-1">
                            Notes liées à ce projet
                        </p>
                    </div>
                    <NotesPanel
                        :noteable-type="'App\\Models\\Project'"
                        :noteable-id="project.id"
                        :initial-notes="projectNotes"
                    />
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
