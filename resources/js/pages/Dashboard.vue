<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage, Link } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Skeleton from '@/components/ui/skeleton/Skeleton.vue'
import PomodoroRing from '@/components/pomodoro/PomodoroRing.vue'
import NotesPanel from '@/components/NotesPanel.vue'

interface TaskDto { id:number; title:string; status:string; deadline_at?:string|null; completed_at?:string|null }
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

const plannedSeconds = ref(1500)
const selectedTaskId = ref<number | null>(null)
const starting = ref(false)
const finishing = ref(false)
const interrupting = ref(false)
const resuming = ref(false)
// Quick add task
const newTaskTitle = ref('')
const creatingTask = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
]

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
            <!-- Active Session / Start Panel -->
            <div class="grid gap-6 lg:grid-cols-3">
                <Card class="lg:col-span-1 p-5 gap-4">
                    <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Focus Session</h2>
                    <template v-if="activeSession">
                        <div class="flex flex-col gap-4 items-center">
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
                        <form class="flex flex-col gap-3" @submit.prevent="startSession">
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

                <!-- Tasks List -->
                <Card class="lg:col-span-1 p-5 gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Mes Tâches</h2>
                    </div>
                    <form class="flex gap-2" @submit.prevent="quickAddTask">
                        <Input v-model="newTaskTitle" placeholder="Nouvelle tâche rapide" class="flex-1" />
                        <Button :disabled="creatingTask || !newTaskTitle.trim()" size="sm">Ajouter</Button>
                    </form>
                    <ul class="divide-y divide-border rounded-md border">
                        <li v-for="t in tasks" :key="t.id" class="flex items-center justify-between gap-4 px-3 py-2">
                            <div class="flex flex-col">
                                <span class="text-sm font-medium" :class="t.status === 'done' && 'line-through text-muted-foreground'">{{ t.title }}</span>
                                <span v-if="t.deadline_at" class="text-xs text-muted-foreground">Due {{ new Date(t.deadline_at).toLocaleDateString() }}</span>
                            </div>
                            <span class="text-xs uppercase tracking-wide rounded bg-secondary px-2 py-1" :class="t.status === 'done' ? 'bg-green-100 text-green-700 dark:bg-green-600/20 dark:text-green-300' : ''">
                                {{ t.status }}
                            </span>
                        </li>
                        <li v-if="!tasks.length" class="p-4 text-center text-sm text-muted-foreground">
                            <Skeleton class="h-4 w-1/2 mx-auto" />
                            <p class="mt-2">Aucune tâche pour l'instant.</p>
                        </li>
                    </ul>
                </Card>

                <!-- Notes Panel - Always visible -->
                <Card class="lg:col-span-1 p-0">
                    <div class="p-4 border-b border-border">
                        <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase flex items-center justify-between">
                            Notes rapides
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('notes.index')" class="text-xs">
                                    Voir tout
                                </Link>
                            </Button>
                        </h2>
                    </div>

                    <!-- Session Notes when active -->
                    <div v-if="activeSession" class="border-b border-border">
                        <div class="p-3 bg-purple-50 dark:bg-purple-950/30">
                            <span class="text-xs text-purple-700 dark:text-purple-300 font-medium">Notes de la session active</span>
                        </div>
                        <NotesPanel
                            noteable-type="App\\Models\\PomodoroSession"
                            :noteable-id="activeSession.id"
                            :initial-notes="sessionNotes"
                        />
                    </div>

                    <!-- General notes area -->
                    <div v-else class="p-4">
                        <div class="text-center py-8">
                            <svg class="mx-auto h-8 w-8 text-muted-foreground/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m-7 8h16a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <p class="mt-2 text-sm text-muted-foreground">Aucune session active</p>
                            <p class="text-xs text-muted-foreground">Démarrez une session pour prendre des notes</p>
                        </div>
                    </div>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
