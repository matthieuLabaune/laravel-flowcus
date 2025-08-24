<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import PomodoroRing from '@/components/pomodoro/PomodoroRing.vue'
import NotesPanel from '@/components/NotesPanel.vue'
import TaskDataTable from '@/components/tasks/TaskDataTable.vue'
import { taskColumns } from '@/components/tasks/TaskColumns'

interface TaskDto {
    id: number;
    title: string;
    status: 'pending' | 'in_progress' | 'done';
    priority?: 'low' | 'medium' | 'high';
    deadline_at?: string | null;
    completed_at?: string | null;
}
interface SessionDto {
    id:number;
    task_id?:number|null;
    planned_seconds:number;
    actual_seconds:number;
    started_at:string;
    interruptions_count?:number;
    paused_seconds?:number;
    last_paused_at?:string|null;
}
interface NoteDto {
    id: number;
    content: string;
    created_at: string;
    updated_at: string;
    noteable_type: string;
    noteable_id: number;
}

const page = usePage()
const tasks = computed(() => (page.props as any).tasks as TaskDto[] || [])
const activeSession = computed(() => (page.props as any).activeSession as SessionDto | null || null)
const sessionNotes = computed(() => (page.props as any).sessionNotes as NoteDto[] || [])

// Convert TaskDto to Task for the datatable
const tasksForTable = computed(() => tasks.value.map(task => ({
    ...task,
    status: task.status as 'pending' | 'in_progress' | 'done'
})))

const plannedSeconds = ref(1500)
const selectedTaskId = ref<number | null>(null)
const starting = ref(false)
const finishing = ref(false)
const interrupting = ref(false)
const resuming = ref(false)
// Quick add task
const newTaskTitle = ref('')
const creatingTask = ref(false)

// Notes panel reference
const notesPanelRef = ref<InstanceType<typeof NotesPanel> | null>(null)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
]

function openNotesForm() {
    if (notesPanelRef.value) {
        notesPanelRef.value.toggleAddForm()
    }
}

function startSession() {
    starting.value = true
    router.post(route('sessions.start'), { planned_seconds: plannedSeconds.value, task_id: selectedTaskId.value }, {
        preserveScroll: true,
        onFinish: () => (starting.value = false),
    onSuccess: () => {},
    })
}

function finishSession() {
    if (!activeSession.value) return
    finishing.value = true
    router.post(route('sessions.finish', activeSession.value.id), {}, {
        preserveScroll: true,
        onFinish: () => (finishing.value = false),
        onSuccess: () => {},
    })
}

function interruptSession() {
    if (!activeSession.value) return
    interrupting.value = true
    router.post(route('sessions.pause', activeSession.value.id), {}, {
        preserveScroll: true,
        onFinish: () => (interrupting.value = false),
        onSuccess: () => {
            // Force reload of dashboard data
            router.reload({ only: ['activeSession'] })
        }
    })
}

function resumeSession() {
    if (!activeSession.value) return
    resuming.value = true
    router.post(route('sessions.resume', activeSession.value.id), {}, {
        preserveScroll: true,
        onFinish: () => (resuming.value = false),
        onSuccess: () => {
            // Force reload of dashboard data
            router.reload({ only: ['activeSession'] })
        }
    })
}


function quickAddTask() {
    if (!newTaskTitle.value.trim()) return
    creatingTask.value = true
    router.post(route('tasks.store'), { title: newTaskTitle.value }, {
        preserveScroll: true,
        onFinish: () => (creatingTask.value = false),
        onSuccess: () => {
            newTaskTitle.value = ''
            // Reload only tasks on dashboard (simple: full visit to dashboard with partial reload later optimization)
            router.visit(route('dashboard'), { preserveState: true, preserveScroll: true })
        }
    })
}
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Première rangée : Focus Session + Notes Rapides -->
            <div class="grid gap-6 lg:grid-cols-2">
                <!-- Focus Session -->
                <Card class="p-5 gap-4 h-[400px] flex flex-col">
                    <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Focus Session</h2>
                    <template v-if="activeSession">
                        <div class="flex flex-col gap-4 items-center flex-1 justify-center">
                            <PomodoroRing
                              :started-at="activeSession.started_at"
                              :planned-seconds="activeSession.planned_seconds"
                              :is-paused="(activeSession.interruptions_count || 0) > 0"
                              :paused-seconds="activeSession.paused_seconds || 0"
                              :last-paused-at="activeSession.last_paused_at" />
                            <div class="flex gap-2">
                                <Button :disabled="finishing" variant="default" @click="finishSession">Terminer</Button>
                                <template v-if="(activeSession.interruptions_count || 0) > 0">
                                    <Button :disabled="resuming" variant="outline" @click="resumeSession">Reprendre</Button>
                                </template>
                                <template v-else>
                                    <Button :disabled="interrupting" variant="secondary" @click="interruptSession">Pause</Button>
                                </template>
                            </div>
                        </div>
                    </template>
                    <template v-else>
                        <form class="flex flex-col gap-3 flex-1 justify-center" @submit.prevent="startSession">
                            <label class="text-xs font-medium tracking-wide text-muted-foreground">Durée (sec)</label>
                            <Input v-model.number="plannedSeconds" type="number" min="60" max="7200" />
                            <label class="text-xs font-medium tracking-wide text-muted-foreground">Tâche (optionnel)</label>
                            <select v-model.number="selectedTaskId" class="h-9 rounded-md border bg-transparent px-2 text-sm">
                                <option :value="null">-- Aucune --</option>
                                <option v-for="t in tasks" :key="t.id" :value="t.id">{{ t.title }}</option>
                            </select>
                            <Button :disabled="starting" class="mt-2">Démarrer</Button>
                        </form>
                    </template>
                </Card>

                <!-- Notes Rapides -->
                <Card class="h-[400px] flex flex-col">
                    <div class="p-4 border-b border-border flex-shrink-0">
                        <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase flex items-center justify-between">
                            <span class="flex items-center gap-2">
                                Notes rapides
                                <span class="bg-purple-100 text-purple-700 dark:bg-purple-900/50 dark:text-purple-300 rounded-full px-2 py-0.5 text-xs font-medium">
                                    {{ activeSession ? sessionNotes.length : 0 }}
                                </span>
                            </span>
                            <div class="flex items-center gap-2">
                                <Button variant="outline" size="sm" as-child tabindex="0">
                                    <Link :href="route('notes.index')" class="text-xs">
                                        Voir tout
                                    </Link>
                                </Button>
                                <Button
                                    size="sm"
                                    tabindex="0"
                                    @click="openNotesForm"
                                    v-if="activeSession"
                                    class="focus:outline-none focus:ring-2 focus:ring-purple-500"
                                >
                                    Ajouter
                                </Button>
                            </div>
                        </h2>
                    </div>

                    <!-- Session Notes when active -->
                    <div v-if="activeSession" class="flex-1 flex flex-col">
                        <div class="p-3 bg-purple-50 dark:bg-purple-950/30 flex-shrink-0 border-b border-border">
                            <span class="text-xs text-purple-700 dark:text-purple-300 font-medium">Notes de la session active</span>
                        </div>
                        <div class="flex-1">
                            <NotesPanel
                                ref="notesPanelRef"
                                :noteable-type="'App\\Models\\PomodoroSession'"
                                :noteable-id="activeSession.id"
                                :initial-notes="sessionNotes"
                                :hide-header="true"
                            />
                        </div>
                    </div>

                    <!-- General notes area -->
                    <div v-else class="flex-1 flex items-center justify-center p-4">
                        <div class="text-center">
                            <svg class="mx-auto h-8 w-8 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">Aucune session active</p>
                            <p class="text-xs text-muted-foreground">Démarrez une session pour prendre des notes</p>
                        </div>
                    </div>
                </Card>
            </div>

            <!-- Deuxième rangée : Tâches en DataTable -->
            <Card class="p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-semibold tracking-tight">Mes Tâches</h2>
                        <p class="text-sm text-muted-foreground">Gérez vos tâches avec tri, filtres et pagination</p>
                    </div>
                    <form class="flex gap-2" @submit.prevent="quickAddTask">
                        <Input v-model="newTaskTitle" placeholder="Nouvelle tâche rapide" class="w-64" />
                        <Button :disabled="creatingTask || !newTaskTitle.trim()" size="sm">Ajouter</Button>
                    </form>
                </div>

                <TaskDataTable
                    :columns="taskColumns"
                    :data="tasksForTable"
                />
            </Card>
        </div>
    </AppLayout>
</template>
