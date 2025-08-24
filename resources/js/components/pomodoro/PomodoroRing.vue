<script setup lang="ts">
import { onMounted, onBeforeUnmount, ref, computed, watch } from 'vue'

interface Props {
  startedAt: string
  plannedSeconds: number
  endedAt?: string | null
  isPaused?: boolean
  pausedSeconds?: number
  lastPausedAt?: string | null
  size?: number
  stroke?: number
}

const props = withDefaults(defineProps<Props>(), {
  size: 140,
  stroke: 10,
  endedAt: null,
  isPaused: false,
  pausedSeconds: 0,
  lastPausedAt: null,
})

const now = ref<Date>(new Date())
let interval: number | undefined

function tick() {
  now.value = new Date()
}

onMounted(() => {
  interval = window.setInterval(tick, 1000)
})
onBeforeUnmount(() => {
  if (interval) window.clearInterval(interval)
})

watch(() => props.endedAt, (val) => {
  if (val && interval) {
    window.clearInterval(interval)
  }
})

const started = computed(() => new Date(props.startedAt))
const elapsed = computed(() => {
  // If session is ended, calculate final time
  if (props.endedAt) {
    const end = new Date(props.endedAt)
    const totalElapsed = Math.max(0, Math.floor((end.getTime() - started.value.getTime()) / 1000))
    return Math.max(0, totalElapsed - (props.pausedSeconds || 0))
  }

  // If currently paused, freeze timer at the moment of pause minus previous pauses
  if (props.isPaused && props.lastPausedAt) {
    const pauseStart = new Date(props.lastPausedAt)
    const elapsedUntilPause = Math.max(0, Math.floor((pauseStart.getTime() - started.value.getTime()) / 1000))
    return Math.max(0, elapsedUntilPause - (props.pausedSeconds || 0))
  }

  // Normal running: current time minus all previous pauses
  const totalElapsed = Math.max(0, Math.floor((now.value.getTime() - started.value.getTime()) / 1000))
  return Math.max(0, totalElapsed - (props.pausedSeconds || 0))
})
const percent = computed(() => Math.min(1, elapsed.value / props.plannedSeconds))
const overtime = computed(() => elapsed.value > props.plannedSeconds)
const remaining = computed(() => Math.max(0, props.plannedSeconds - elapsed.value))

function fmt(sec: number) {
  const m = Math.floor(sec / 60)
  const s = sec % 60
  return `${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`
}

const displayMain = computed(() => fmt(elapsed.value))
const displaySecondary = computed(() => overtime.value ? '+' + fmt(elapsed.value - props.plannedSeconds) : fmt(remaining.value))

// Circle metrics
const radius = computed(() => (props.size - props.stroke) / 2)
const circumference = computed(() => 2 * Math.PI * radius.value)
const dash = computed(() => {
  const ratio = overtime.value ? 1 : percent.value
  return `${ratio * circumference.value} ${circumference.value}`
})

const ringColor = computed(() => {
  if (props.endedAt) return 'stroke-muted-foreground/40'
  if (props.isPaused) return 'stroke-amber-500 dark:stroke-amber-400'
  if (overtime.value) return 'stroke-green-500 dark:stroke-green-400'
  return 'stroke-violet-500 dark:stroke-violet-400'
})
</script>

<template>
  <div class="flex flex-col items-center gap-2 select-none">
    <div class="relative" :style="{ width: size + 'px', height: size + 'px' }">
      <svg :width="size" :height="size" class="block" :viewBox="`0 0 ${size} ${size}`">
        <circle
          :cx="size/2" :cy="size/2" :r="radius"
          class="fill-none stroke-muted/30"
          :stroke-width="stroke"/>
        <circle
          :cx="size/2" :cy="size/2" :r="radius"
          class="fill-none transition-[stroke-dasharray] duration-500 ease-linear"
          :class="ringColor"
          :stroke-width="stroke"
          stroke-linecap="round"
          :stroke-dasharray="dash"
          :transform="`rotate(-90 ${size/2} ${size/2})`"
        />
        <text
          :x="size/2" :y="size/2 - 4" text-anchor="middle"
          class="font-medium fill-foreground text-[20px]"
          dominant-baseline="middle"
        >{{ displayMain }}</text>
        <text
          :x="size/2" :y="size/2 + 20" text-anchor="middle"
          class="fill-muted-foreground text-xs"
        >{{ displaySecondary }}</text>
      </svg>
    </div>
    <div class="text-xs text-muted-foreground uppercase tracking-wide">
      <span v-if="props.isPaused">Pause</span>
      <span v-else-if="overtime">Overtime</span>
      <span v-else>Focus</span>
    </div>
  </div>
</template>

<style scoped>
/* utility fallback if needed */
</style>
