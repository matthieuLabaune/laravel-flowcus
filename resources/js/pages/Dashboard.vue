<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue'
import { Head, router, usePage } from '@inertiajs/vue3'
import { ref, computed } from 'vue'
import type { BreadcrumbItem } from '@/types'
import Card from '@/components/ui/card/Card.vue'
import Button from '@/components/ui/button/Button.vue'
import Input from '@/components/ui/input/Input.vue'
import Skeleton from '@/components/ui/skeleton/Skeleton.vue'
import { timeAgo } from '@/lib/time'

interface TaskDto { id:number; title:string; status:string; deadline_at?:string|null; completed_at?:string|null }
interface SessionDto { id:number; task_id?:number|null; planned_seconds:number; actual_seconds:number; started_at:string }

const page = usePage()
const tasks = computed(() => (page.props as any).tasks as TaskDto[] || [])
const activeSession = ref<SessionDto | null>(((page.props as any).activeSession as SessionDto) || null)

const plannedSeconds = ref(1500)
const selectedTaskId = ref<number | null>(null)
const starting = ref(false)
const finishing = ref(false)
const interrupting = ref(false)

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/dashboard' },
]

function startSession() {
    starting.value = true
    router.post(
        route('sessions.start'),
        { planned_seconds: plannedSeconds.value, task_id: selectedTaskId.value },
        {
            preserveScroll: true,
            onFinish: () => (starting.value = false),
                    onSuccess: () => {
                        activeSession.value = (usePage().props as any).activeSession ?? activeSession.value
                    },
        },
    )
}

function finishSession() {
    if (!activeSession.value) return
    finishing.value = true
    router.post(route('sessions.finish', activeSession.value.id), {}, {
        preserveScroll: true,
        onFinish: () => (finishing.value = false),
        onSuccess: () => (activeSession.value = null),
    })
}

function interruptSession() {
    if (!activeSession.value) return
    interrupting.value = true
    router.post(route('sessions.interrupt', activeSession.value.id), {}, {
        preserveScroll: true,
        onFinish: () => (interrupting.value = false),
    })
}

function humanElapsed() {
    if (!activeSession.value) return ''
    return timeAgo(activeSession.value.started_at)
}
</script>

<template>
    <Head title="Dashboard" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex flex-col gap-6 p-4">
            <!-- Active Session / Start Panel -->
            <div class="grid gap-6 md:grid-cols-3">
                <Card class="md:col-span-1 p-5 gap-4">
                    <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Focus Session</h2>
                    <template v-if="activeSession">
                        <div class="flex flex-col gap-2">
                            <p class="text-lg font-semibold">Session en cours</p>
                            <p class="text-sm text-muted-foreground">Démarrée il y a {{ humanElapsed() }}</p>
                            <div class="flex gap-2 mt-2">
                                <Button :disabled="finishing" variant="default" @click="finishSession">Terminer</Button>
                                <Button :disabled="interrupting" variant="secondary" @click="interruptSession">Interruption</Button>
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
                <Card class="md:col-span-2 p-5 gap-4">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-medium tracking-wide text-muted-foreground uppercase">Mes Tâches</h2>
                    </div>
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
            </div>
        </div>
    </AppLayout>
</template>
