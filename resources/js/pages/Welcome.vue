<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

interface StackItem { label: string; value: string; doc: string; }
const stack: StackItem[] = [
    { label: 'Backend', value: 'Laravel 12 (PHP 8.4)', doc: 'https://laravel.com/docs' },
    { label: 'SPA Layer', value: 'Inertia.js v2', doc: 'https://inertiajs.com' },
    { label: 'Frontend', value: 'Vue 3 + TypeScript', doc: 'https://vuejs.org/guide/' },
    { label: 'UI', value: 'Tailwind CSS v4 + shadcn-vue', doc: 'https://tailwindcss.com' },
    { label: 'Build', value: 'Vite', doc: 'https://vitejs.dev/guide/' },
    { label: 'Tests', value: 'Pest', doc: 'https://pestphp.com/docs' },
    { label: 'Dev Env', value: 'Docker (single app container)', doc: 'https://docs.docker.com/' }
];

const quickSteps = [
    { n: 1, t: 'Créer un module', d: 'Modèle + migration + contrôleur Inertia.' },
    { n: 2, t: 'Page', d: 'resources/js/pages + layout si besoin.' },
    { n: 3, t: 'Composants', d: 'Réutilise AppLayout, Heading, etc.' },
    { n: 4, t: 'Tests', d: 'Pest Feature pour la route.' },
    { n: 5, t: 'Build', d: 'task npm-build (ou Vite dev).' }
];
</script>

<template>
    <Head title="Starter Kit" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
        <div class="mx-auto max-w-5xl px-6 py-14">
            <header class="mb-12 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-6">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Laravel + Inertia + Vue Starter</h1>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">Page d’accueil générique à personnaliser.</p>
                </div>
                <div class="flex gap-3">
                    <Link v-if="!$page.props.auth.user" :href="route('login')" class="px-4 py-2 text-sm rounded-md border border-gray-300 dark:border-gray-600 hover:bg-gray-100 dark:hover:bg-gray-800">Connexion</Link>
                    <Link v-if="!$page.props.auth.user" :href="route('register')" class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-500">S’inscrire</Link>
                    <Link v-if="$page.props.auth.user" :href="route('dashboard')" class="px-4 py-2 text-sm rounded-md bg-indigo-600 text-white hover:bg-indigo-500">Tableau de bord</Link>
                </div>
            </header>

            <section class="grid gap-6 md:grid-cols-2 mb-12">
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h2 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Stack</h2>
                    <ul class="space-y-3">
                        <li v-for="s in stack" :key="s.label" class="flex items-start justify-between gap-4">
                            <div>
                                <p class="font-medium text-sm">{{ s.label }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ s.value }}</p>
                            </div>
                            <a :href="s.doc" target="_blank" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">Docs ↗</a>
                        </li>
                    </ul>
                </div>
                <div class="rounded-lg border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 p-6 shadow-sm">
                    <h2 class="font-semibold mb-4 text-sm uppercase tracking-wide text-gray-500 dark:text-gray-400">Démarrage rapide</h2>
                    <ol class="space-y-3 list-decimal list-inside">
                        <li v-for="s in quickSteps" :key="s.n" class="text-sm">
                            <span class="font-medium">{{ s.t }}:</span> {{ s.d }}
                        </li>
                    </ol>
                    <div class="mt-5 text-xs text-gray-500 dark:text-gray-400">
                        Commandes utiles: <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">task dev</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">task test</code> <code class="bg-gray-100 dark:bg-gray-700 px-1 rounded">task npm-build</code>
                    </div>
                </div>
            </section>

            <section class="rounded-lg border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center bg-white/70 dark:bg-gray-800/40">
                <h2 class="text-lg font-semibold mb-2">Personnalisation</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">Remplace ce composant par ta propre page d’accueil ou reviens à la welcome Blade de Laravel.</p>
                <pre class="text-left text-xs overflow-auto bg-gray-900 text-gray-100 rounded p-4"><code># Revenir à la page Laravel par défaut
# 1. Crée resources/views/welcome.blade.php
# 2. Route::get('/', fn() => view('welcome'));
# 3. Supprime pages/Welcome.vue si inutile
</code></pre>
                <p class="mt-4 text-xs text-gray-500 dark:text-gray-500">Cette page est volontairement minimale.</p>
            </section>

            <footer class="mt-16 text-center text-xs text-gray-500 dark:text-gray-600">
                Starter Kit • Laravel / Inertia / Vue • Personnalise librement
            </footer>
        </div>
    </div>
</template>
