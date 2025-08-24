<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import { Save, ArrowLeft, X } from 'lucide-vue-next'

interface ProjectDto {
    id: number
    name: string
    description?: string
    color?: string
    created_at: string
    updated_at: string
}

interface Props {
    project: ProjectDto
}

const props = defineProps<Props>()

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Projets', href: '/projects' },
    { title: props.project.name, href: `/projects/${props.project.id}` },
    { title: 'Modifier', href: `/projects/${props.project.id}/edit` },
]

const form = useForm({
    name: props.project.name || '',
    description: props.project.description || '',
    color: props.project.color || '#8855ff',
})

function updateProject() {
    form.put(route('projects.update', props.project.id), {
        preserveScroll: true,
    })
}

function cancelEdit() {
    router.get(route('projects.show', props.project.id))
}
</script>

<template>
    <Head :title="`Modifier ${project.name}`" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 max-w-2xl mx-auto">
            <!-- Header -->
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                        Modifier le projet
                    </h1>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Modifiez les informations de votre projet
                    </p>
                </div>
                <Button variant="outline" size="sm" @click="cancelEdit">
                    <X class="h-4 w-4 mr-2" />
                    Annuler
                </Button>
            </div>

            <!-- Edit Form -->
            <Card class="p-6">
                <form @submit.prevent="updateProject" class="space-y-6">
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <!-- Project Name -->
                            <div class="md:col-span-1">
                                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Nom du projet <span class="text-red-500">*</span>
                                </label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    autofocus
                                    placeholder="Entrez le nom du projet"
                                    :class="{ 'border-red-300': form.errors.name }"
                                />
                                <div v-if="form.errors.name" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.name }}
                                </div>
                            </div>

                            <!-- Project Color -->
                            <div class="md:col-span-1">
                                <label for="color" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                    Couleur du projet
                                </label>
                                <div class="flex items-center gap-3">
                                    <input
                                        id="color"
                                        v-model="form.color"
                                        type="color"
                                        class="h-10 w-16 rounded-md border border-border p-1 bg-transparent cursor-pointer"
                                    />
                                    <div class="flex-1">
                                        <Input
                                            v-model="form.color"
                                            type="text"
                                            placeholder="#8855ff"
                                            class="font-mono text-sm"
                                        />
                                    </div>
                                </div>
                                <div v-if="form.errors.color" class="mt-1 text-sm text-red-600">
                                    {{ form.errors.color }}
                                </div>
                            </div>
                        </div>

                        <!-- Project Description -->
                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Description (optionnelle)
                            </label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                rows="4"
                                placeholder="Décrivez brièvement votre projet..."
                                :class="{ 'border-red-300': form.errors.description }"
                            />
                            <div v-if="form.errors.description" class="mt-1 text-sm text-red-600">
                                {{ form.errors.description }}
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-between pt-6 border-t border-border">
                        <Button
                            type="button"
                            variant="outline"
                            @click="cancelEdit"
                        >
                            <ArrowLeft class="h-4 w-4 mr-2" />
                            Retour au projet
                        </Button>

                        <Button
                            type="submit"
                            :disabled="form.processing || !form.name.trim()"
                            class="min-w-[120px]"
                        >
                            <Save class="h-4 w-4 mr-2" />
                            <span v-if="form.processing">Enregistrement...</span>
                            <span v-else>Enregistrer</span>
                        </Button>
                    </div>
                </form>
            </Card>

            <!-- Preview -->
            <Card class="mt-6 p-4 bg-gray-50 dark:bg-gray-800/50">
                <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Aperçu</h3>
                <div class="flex items-center gap-3">
                    <div
                        class="w-4 h-4 rounded-full border"
                        :style="{ backgroundColor: form.color }"
                    />
                    <div>
                        <div class="font-medium text-gray-900 dark:text-white">
                            {{ form.name || 'Nom du projet' }}
                        </div>
                        <div v-if="form.description" class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ form.description }}
                        </div>
                    </div>
                </div>
            </Card>
        </div>
    </AppLayout>
</template>
