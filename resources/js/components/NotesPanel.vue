<template>
  <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700">
    <div class="p-4 border-b border-gray-200 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900 dark:text-white">
          Notes
        </h3>
        <button
          @click="showAddForm = !showAddForm"
          class="inline-flex items-center px-3 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500"
        >
          <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
          </svg>
          Ajouter
        </button>
      </div>
    </div>

    <!-- Add note form -->
    <div v-if="showAddForm" class="p-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-700/50">
      <form @submit.prevent="addNote">
        <textarea
          v-model="newNoteContent"
          placeholder="Écrivez votre note..."
          rows="3"
          class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 dark:placeholder-gray-300 focus:outline-none focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-800 dark:text-white"
          required
        ></textarea>
        <div class="flex items-center justify-end gap-2 mt-3">
          <button
            type="button"
            @click="cancelAdd"
            class="px-3 py-1.5 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-400"
          >
            Annuler
          </button>
          <button
            type="submit"
            :disabled="!newNoteContent.trim() || isSubmitting"
            class="inline-flex items-center px-4 py-1.5 border border-transparent text-sm font-medium rounded-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg v-if="isSubmitting" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Ajouter
          </button>
        </div>
      </form>
    </div>

    <!-- Notes list -->
    <div class="max-h-96 overflow-y-auto">
      <div v-if="notes.length === 0 && !isLoading" class="p-8 text-center text-gray-500 dark:text-gray-400">
        <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
        </svg>
        <p class="mt-2">Aucune note pour le moment</p>
      </div>

      <div v-if="isLoading" class="p-4 space-y-3">
        <div v-for="i in 3" :key="i" class="animate-pulse">
          <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4 mb-2"></div>
          <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
        </div>
      </div>

      <div v-for="note in notes" :key="note.id" class="p-4 border-b border-gray-100 dark:border-gray-700 last:border-b-0 hover:bg-gray-50 dark:hover:bg-gray-700/30">
        <div v-if="editingNoteId === note.id">
          <!-- Edit mode -->
          <form @submit.prevent="updateNote(note)">
            <textarea
              v-model="editingContent"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500 dark:bg-gray-800 dark:text-white"
              required
            ></textarea>
            <div class="flex items-center justify-end gap-2 mt-2">
              <button
                type="button"
                @click="cancelEdit"
                class="px-3 py-1 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-400"
              >
                Annuler
              </button>
              <button
                type="submit"
                :disabled="!editingContent.trim() || isSubmitting"
                class="px-3 py-1 text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 rounded-md disabled:opacity-50"
              >
                Sauvegarder
              </button>
            </div>
          </form>
        </div>
        <div v-else>
          <!-- View mode -->
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <p class="text-gray-900 dark:text-white whitespace-pre-wrap">{{ note.content }}</p>
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ formatDate(note.created_at) }}
                <span v-if="note.updated_at !== note.created_at">
                  · Modifié {{ formatDate(note.updated_at) }}
                </span>
              </p>
            </div>
            <div class="flex items-center gap-1 ml-3">
              <button
                @click="startEdit(note)"
                class="p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                title="Modifier"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
              </button>
              <button
                @click="deleteNote(note)"
                class="p-1 text-gray-400 hover:text-red-600 dark:hover:text-red-400"
                title="Supprimer"
                :disabled="isSubmitting"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'

interface Note {
  id: number
  content: string
  created_at: string
  updated_at: string
  noteable_type: string
  noteable_id: number
}

interface Props {
  noteableType: string
  noteableId: number
  initialNotes?: Note[]
}

const props = withDefaults(defineProps<Props>(), {
  initialNotes: () => []
})

const notes = ref<Note[]>([...props.initialNotes])
const showAddForm = ref(false)
const newNoteContent = ref('')
const isLoading = ref(false)
const isSubmitting = ref(false)
const editingNoteId = ref<number | null>(null)
const editingContent = ref('')

const loadNotes = async () => {
  isLoading.value = true
  try {
    const response = await fetch(`/notes?noteable_type=${props.noteableType}&noteable_id=${props.noteableId}`, {
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })

    if (response.ok) {
      const data = await response.json()
      notes.value = data.notes || []
    }
  } catch (error) {
    console.error('Error loading notes:', error)
  } finally {
    isLoading.value = false
  }
}

const addNote = async () => {
  if (!newNoteContent.value.trim() || isSubmitting.value) return

  isSubmitting.value = true
  try {
    const response = await fetch('/notes', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
      },
      body: JSON.stringify({
        content: newNoteContent.value,
        noteable_type: props.noteableType,
        noteable_id: props.noteableId
      })
    })

    if (response.ok) {
      const data = await response.json()
      notes.value.unshift(data.note)
      newNoteContent.value = ''
      showAddForm.value = false
    } else {
      const errorData = await response.json()
      console.error('Error adding note:', errorData)
      alert('Erreur lors de l\'ajout de la note: ' + (errorData.message || 'Erreur inconnue'))
    }
  } catch (error) {
    console.error('Error adding note:', error)
    alert('Erreur de connexion lors de l\'ajout de la note')
  } finally {
    isSubmitting.value = false
  }
}

const startEdit = (note: Note) => {
  editingNoteId.value = note.id
  editingContent.value = note.content
}

const cancelEdit = () => {
  editingNoteId.value = null
  editingContent.value = ''
}

const updateNote = async (note: Note) => {
  if (!editingContent.value.trim() || isSubmitting.value) return

  isSubmitting.value = true
  try {
    const response = await fetch(`/notes/${note.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
      },
      body: JSON.stringify({
        content: editingContent.value
      })
    })

    if (response.ok) {
      const data = await response.json()
      const index = notes.value.findIndex(n => n.id === note.id)
      if (index !== -1) {
        notes.value[index] = data.note
      }
      cancelEdit()
    } else {
      const errorData = await response.json()
      console.error('Error updating note:', errorData)
      alert('Erreur lors de la modification: ' + (errorData.message || 'Erreur inconnue'))
    }
  } catch (error) {
    console.error('Error updating note:', error)
    alert('Erreur de connexion lors de la modification')
  } finally {
    isSubmitting.value = false
  }
}

const deleteNote = async (note: Note) => {
  if (!confirm('Êtes-vous sûr de vouloir supprimer cette note ?')) return

  isSubmitting.value = true
  try {
    const response = await fetch(`/notes/${note.id}`, {
      method: 'DELETE',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
      }
    })

    if (response.ok) {
      notes.value = notes.value.filter(n => n.id !== note.id)
    } else {
      const errorData = await response.json()
      console.error('Error deleting note:', errorData)
      alert('Erreur lors de la suppression: ' + (errorData.message || 'Erreur inconnue'))
    }
  } catch (error) {
    console.error('Error deleting note:', error)
    alert('Erreur de connexion lors de la suppression')
  } finally {
    isSubmitting.value = false
  }
}

const cancelAdd = () => {
  showAddForm.value = false
  newNoteContent.value = ''
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

onMounted(() => {
  if (props.initialNotes.length === 0) {
    loadNotes()
  }
})
</script>
