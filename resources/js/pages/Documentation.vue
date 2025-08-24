<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Clock, Target, NotebookPen, FolderOpen, Play, Pause, SkipForward } from 'lucide-vue-next';
</script>

<template>
    <Head title="Documentation" />

    <AppLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
                    Documentation Flowcus
                </h2>
                <Badge variant="secondary" class="bg-primary/10 text-primary">
                    Guide d'utilisation
                </Badge>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl space-y-6 px-4 sm:px-6 lg:px-8">
                <!-- Introduction -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-primary">
                            <Target class="h-6 w-6" />
                            Qu'est-ce que Flowcus ?
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <p class="text-gray-700 dark:text-gray-300">
                            Flowcus est une application de gestion de productivité basée sur la technique Pomodoro.
                            Elle vous aide à organiser vos tâches, suivre votre temps de travail et maintenir votre concentration
                            grâce à des sessions de travail structurées.
                        </p>
                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="flex items-center gap-2 text-sm">
                                <Clock class="h-4 w-4 text-primary" />
                                <span>Sessions chronométrées</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <Target class="h-4 w-4 text-primary" />
                                <span>Gestion des tâches</span>
                            </div>
                            <div class="flex items-center gap-2 text-sm">
                                <NotebookPen class="h-4 w-4 text-primary" />
                                <span>Prise de notes</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Sessions Focus -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-primary">
                            <Play class="h-6 w-6" />
                            Sessions Focus
                        </CardTitle>
                        <CardDescription>
                            Le cœur de Flowcus : des sessions de travail concentré
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <h4 class="font-semibold">Comment démarrer une session ?</h4>
                        <ol class="space-y-2 pl-4 text-sm text-gray-600 dark:text-gray-400">
                            <li class="flex items-start gap-2">
                                <span class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary/20 text-xs font-medium text-primary">1</span>
                                <span>Cliquez sur le bouton "Démarrer" dans le cercle de la session focus</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary/20 text-xs font-medium text-primary">2</span>
                                <span>Optionnellement, associez une tâche à votre session</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary/20 text-xs font-medium text-primary">3</span>
                                <span>Configurez la durée (25 minutes par défaut)</span>
                            </li>
                            <li class="flex items-start gap-2">
                                <span class="mt-1 flex h-5 w-5 items-center justify-center rounded-full bg-primary/20 text-xs font-medium text-primary">4</span>
                                <span>Concentrez-vous sur votre travail jusqu'à la fin du timer</span>
                            </li>
                        </ol>

                        <div class="mt-6 grid gap-4 md:grid-cols-2">
                            <div class="rounded-lg border p-4">
                                <h5 class="flex items-center gap-2 font-medium">
                                    <Pause class="h-4 w-4" />
                                    Pause et interruption
                                </h5>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Vous pouvez mettre en pause ou interrompre une session si nécessaire.
                                    Les interruptions sont comptabilisées pour vos statistiques.
                                </p>
                            </div>
                            <div class="rounded-lg border p-4">
                                <h5 class="flex items-center gap-2 font-medium">
                                    <SkipForward class="h-4 w-4" />
                                    Pauses automatiques
                                </h5>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    Après chaque session, prenez une pause de 5 minutes.
                                    Toutes les 4 sessions, prenez une pause longue de 15-30 minutes.
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Gestion des tâches -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-primary">
                            <Target class="h-6 w-6" />
                            Gestion des tâches
                        </CardTitle>
                        <CardDescription>
                            Organisez votre travail avec un système de tâches intégré
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-3">
                            <h4 class="font-semibold">Fonctionnalités des tâches :</h4>
                            <ul class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                                <li class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                                    <span><strong>Création rapide</strong> : Ajoutez des tâches depuis le tableau de bord</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                                    <span><strong>Priorités</strong> : Classez vos tâches par importance (Faible, Moyenne, Haute, Critique)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                                    <span><strong>Statuts</strong> : Suivez l'avancement (En attente, En cours, Terminée)</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                                    <span><strong>Organisation</strong> : Groupez vos tâches par projets</span>
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="h-2 w-2 rounded-full bg-primary"></div>
                                    <span><strong>Association</strong> : Liez vos sessions Pomodoro à des tâches spécifiques</span>
                                </li>
                            </ul>
                        </div>

                        <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                            <h5 class="font-medium text-blue-900 dark:text-blue-100">💡 Conseil</h5>
                            <p class="mt-1 text-sm text-blue-800 dark:text-blue-200">
                                Utilisez le tableau de données pour trier, filtrer et gérer efficacement vos tâches.
                                Vous pouvez masquer des colonnes et personnaliser l'affichage selon vos besoins.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Projets -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-primary">
                            <FolderOpen class="h-6 w-6" />
                            Projets
                        </CardTitle>
                        <CardDescription>
                            Organisez vos tâches par projets pour une meilleure structure
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Les projets vous permettent de regrouper des tâches liées et de suivre leur progression globale.
                            Chaque projet peut contenir plusieurs tâches et vous aide à maintenir une vue d'ensemble sur vos objectifs.
                        </p>
                    </CardContent>
                </Card>

                <!-- Notes -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2 text-primary">
                            <NotebookPen class="h-6 w-6" />
                            Notes rapides
                        </CardTitle>
                        <CardDescription>
                            Capturez vos idées et observations pendant vos sessions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Les notes rapides sont parfaites pour capturer des idées spontanées, des observations ou des réflexions
                            pendant vos sessions de travail. Elles peuvent être associées à vos sessions actives pour conserver le contexte.
                        </p>
                    </CardContent>
                </Card>

                <!-- Conseils d'utilisation -->
                <Card>
                    <CardHeader>
                        <CardTitle class="text-primary">
                            Conseils pour maximiser votre productivité
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <h5 class="font-semibold">🎯 Préparation</h5>
                                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>• Préparez vos tâches avant de commencer</li>
                                    <li>• Définissez des objectifs clairs pour chaque session</li>
                                    <li>• Éliminez les distractions de votre environnement</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <h5 class="font-semibold">⏰ Pendant la session</h5>
                                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>• Concentrez-vous sur une seule tâche</li>
                                    <li>• Notez les idées sans perdre le focus</li>
                                    <li>• Respectez les pauses pour maintenir l'efficacité</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <h5 class="font-semibold">📊 Suivi</h5>
                                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>• Consultez vos statistiques régulièrement</li>
                                    <li>• Ajustez votre approche selon vos résultats</li>
                                    <li>• Célébrez vos accomplissements</li>
                                </ul>
                            </div>
                            <div class="space-y-2">
                                <h5 class="font-semibold">🔄 Organisation</h5>
                                <ul class="space-y-1 text-sm text-gray-600 dark:text-gray-400">
                                    <li>• Utilisez les projets pour structurer</li>
                                    <li>• Priorisez vos tâches efficacement</li>
                                    <li>• Maintenez vos listes à jour</li>
                                </ul>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
