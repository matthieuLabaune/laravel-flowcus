<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import Textarea from '@/components/ui/textarea/Textarea.vue'
import Input from '@/components/ui/input/Input.vue'
import { ResizablePanelGroup, ResizablePanel, ResizableHandle } from '@/components/ui/resizable'
import { Search, Plus, NotebookPen, Clock, CheckCircle, FolderOpen, Edit3, Trash2 } from 'lucide-vue-next'

interface NoteDto {
    id: number;
    content: string;
    created_at: string;
    updated_at: string;
    noteable_type: string;
    noteable_id: number;
}

const page = usePage()
const notes = computed(() => (page.props as any).notes as NoteDto[] || [])

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Notes', href: '/notes' },
]

// Selected note for detail view
const selectedNote = ref<NoteDto | null>(null)
const searchQuery = ref('')

// Filtered notes based on search
const filteredNotes = computed(() => {
    if (!searchQuery.value) return notes.value
    return notes.value.filter(note =>
        note.content.toLowerCase().includes(searchQuery.value.toLowerCase())
    )
})

// Form for adding new note
const addForm = useForm({
    content: '',
    noteable_type: '',
    noteable_id: null as number | null
})

function addNote() {
    addForm.post('/notes', {
        preserveScroll: true,
        onSuccess: () => {
            addForm.reset('content')
        }
    })
}

// Form for editing existing note
const editForm = useForm({
    id: null as number | null,
    content: '',
})
const editingNoteId = ref<number | null>(null)

function startEditing(note: NoteDto) {
    editingNoteId.value = note.id
    editForm.id = note.id
    editForm.content = note.content
}

function cancelEditing() {
    editingNoteId.value = null
    editForm.reset()
}

function updateNote() {
    if (!editForm.id) return
    editForm.put(`/notes/${editForm.id}`, {
        preserveScroll: true,
        onSuccess: () => {
            editingNoteId.value = null
            editForm.reset()
        }
    })
}

function deleteNote(noteId: number) {
    if (confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) {
        const deleteForm = useForm({})
        deleteForm.delete(`/notes/${noteId}`, {
            preserveScroll: true
        })
    }
}

function formatDate(dateString: string): string {
    return new Date(dateString).toLocaleDateString('fr-FR', {
        day: 'numeric',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit'
    })
}

function getTypeIcon(type: string) {
    switch (type) {
        case 'App\\Models\\PomodoroSession':
            return Clock
        case 'App\\Models\\Task':
            return CheckCircle
        case 'App\\Models\\Project':
            return FolderOpen
        default:
            return NotebookPen
    }
}

function getTypeLabel(type: string) {
    switch (type) {
        case 'App\\Models\\PomodoroSession':
            return 'Session'
        case 'App\\Models\\Task':
            return 'Tâche'
        case 'App\\Models\\Project':
            return 'Projet'
        default:
            return 'Générale'
    }
}

function selectNote(note: NoteDto) {
    selectedNote.value = note
}

function truncateContent(content: string, length: number = 100): string {
    if (content.length <= length) return content
    return content.substring(0, length) + '...'
}
</script>

<template>
    <Head title="Notes" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="h-screen flex flex-col">
            <!-- Header -->
            <div class="flex items-center justify-between p-4 border-b">
                <div class="flex items-center gap-2">
                    <NotebookPen class="h-5 w-5" />
                    <h1 class="text-lg font-semibold">Notes</h1>
                </div>
                <Button @click="addForm.content = ''; selectedNote = null" size="sm">
                    <Plus class="h-4 w-4 mr-2" />
                    Nouvelle note
                </Button>
            </div>

            <!-- Main Layout -->
            <ResizablePanelGroup direction="horizontal" class="flex-1">
                <!-- Sidebar - Notes List -->
                <ResizablePanel :default-size="30" :min-size="25">
                    <div class="h-full flex flex-col">
                        <!-- Search -->
                        <div class="p-4 border-b">
                            <div class="relative">
                                <Search class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Rechercher dans les notes..."
                                    class="pl-8"
                                />
                            </div>
                        </div>

                        <!-- Notes List -->
                        <div class="flex-1 overflow-auto">
                            <div v-if="filteredNotes.length === 0" class="p-4 text-center text-muted-foreground">
                                <NotebookPen class="h-8 w-8 mx-auto mb-2 opacity-50" />
                                <p>Aucune note trouvée</p>
                            </div>
                            <div v-else class="divide-y">
                                <div
                                    v-for="note in filteredNotes"
                                    :key="note.id"
                                    class="p-4 cursor-pointer hover:bg-muted/50 transition-colors"
                                    :class="selectedNote?.id === note.id && 'bg-muted border-r-2 border-primary'"
                                    @click="selectNote(note)"
                                >
                                    <div class="flex items-start justify-between gap-2 mb-2">
                                        <div class="flex items-center gap-2 min-w-0 flex-1">
                                            <component :is="getTypeIcon(note.noteable_type)" class="h-4 w-4 text-muted-foreground flex-shrink-0" />
                                            <Badge variant="outline" class="text-xs">
                                                {{ getTypeLabel(note.noteable_type) }}
                                            </Badge>
                                        </div>
                                        <span class="text-xs text-muted-foreground flex-shrink-0">
                                            {{ formatDate(note.created_at) }}
                                        </span>
                                    </div>
                                    <p class="text-sm text-muted-foreground line-clamp-3">
                                        {{ truncateContent(note.content, 150) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </ResizablePanel>

                <ResizableHandle />

                <!-- Main Content Area -->
                <ResizablePanel :default-size="70">
                    <div class="h-full flex flex-col">
                        <!-- Content Header -->
                        <div v-if="selectedNote" class="p-4 border-b">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <component :is="getTypeIcon(selectedNote.noteable_type)" class="h-5 w-5 text-muted-foreground" />
                                    <Badge variant="outline">
                                        {{ getTypeLabel(selectedNote.noteable_type) }}
                                    </Badge>
                                    <span class="text-sm text-muted-foreground">
                                        {{ formatDate(selectedNote.created_at) }}
                                    </span>
                                    <span v-if="selectedNote.updated_at !== selectedNote.created_at" class="text-xs text-muted-foreground">
                                        (modifié {{ formatDate(selectedNote.updated_at) }})
                                    </span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Button variant="ghost" size="sm" @click="startEditing(selectedNote)">
                                        <Edit3 class="h-4 w-4" />
                                    </Button>
                                    <Button variant="ghost" size="sm" @click="deleteNote(selectedNote.id)">
                                        <Trash2 class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Content Body -->
                        <div class="flex-1 p-4">
                            <!-- Show selected note -->
                            <div v-if="selectedNote && editingNoteId !== selectedNote.id" class="h-full">
                                <div class="prose prose-sm max-w-none">
                                    <div class="whitespace-pre-wrap">{{ selectedNote.content }}</div>
                                </div>
                            </div>

                            <!-- Edit existing note -->
                            <div v-else-if="selectedNote && editingNoteId === selectedNote.id" class="h-full">
                                <form @submit.prevent="updateNote" class="h-full flex flex-col gap-4">
                                    <Textarea
                                        v-model="editForm.content"
                                        placeholder="Contenu de la note..."
                                        class="flex-1 resize-none"
                                        required
                                    />
                                    <div class="flex justify-end gap-2">
                                        <Button variant="outline" type="button" @click="cancelEditing">
                                            Annuler
                                        </Button>
                                        <Button type="submit" :disabled="editForm.processing">
                                            Sauvegarder
                                        </Button>
                                    </div>
                                </form>
                            </div>

                            <!-- Add new note -->
                            <div v-else-if="!selectedNote" class="h-full">
                                <div class="text-center py-12">
                                    <NotebookPen class="h-12 w-12 mx-auto mb-4 text-muted-foreground/50" />
                                    <h2 class="text-lg font-medium mb-2">Créer une nouvelle note</h2>
                                    <p class="text-muted-foreground mb-6">
                                        Ajoutez vos pensées, idées et rappels importants
                                    </p>

                                    <form @submit.prevent="addNote" class="max-w-2xl mx-auto">
                                        <Textarea
                                            v-model="addForm.content"
                                            placeholder="Écrivez votre note ici..."
                                            class="min-h-[200px] mb-4"
                                            required
                                        />
                                        <div class="flex justify-end">
                                            <Button type="submit" :disabled="addForm.processing">
                                                <Plus class="h-4 w-4 mr-2" />
                                                Ajouter la note
                                            </Button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Empty state -->
                            <div v-else class="h-full flex items-center justify-center">
                                <div class="text-center">
                                    <NotebookPen class="h-12 w-12 mx-auto mb-4 text-muted-foreground/50" />
                                    <h2 class="text-lg font-medium mb-2">Sélectionnez une note</h2>
                                    <p class="text-muted-foreground">
                                        Choisissez une note dans la liste pour la voir ou la modifier
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </ResizablePanel>
            </ResizablePanelGroup>
        </div>
    </AppLayout>
</template>

<style>
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
