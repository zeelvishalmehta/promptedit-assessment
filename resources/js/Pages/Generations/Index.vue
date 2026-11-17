

<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { watch, onUnmounted } from 'vue';

const props = defineProps({
    generations: {
        type: Object,
        required: true,
    },
});

const statusClass = (status) => {
    const classes = {
        queued: 'bg-yellow-100 text-yellow-800',
        processing: 'bg-blue-100 text-blue-800',
        succeeded: 'bg-green-100 text-green-800',
        failed: 'bg-red-100 text-red-800',
    };

    return classes[status] ?? 'bg-gray-100 text-gray-800';
};

const formatCost = (cost) => {
    if (cost === null || cost === undefined) {
        return 'Not available';
    }

    return '$' + Number(cost).toFixed(4);
};

const formatDate = (date) => {
    return date ? new Date(date).toLocaleString() : 'Not available';
};

// Automatic status refreshing
let pollingInterval = null;
let isRefreshing = false;

watch(
    () => props.generations.data.some(
        generation => ['queued', 'processing'].includes(generation.status)
    ),
    (hasPendingGenerations) => {
        if (hasPendingGenerations && !pollingInterval) {
            pollingInterval = setInterval(() => {
                if (isRefreshing) {
                    return;
                }

                isRefreshing = true;

                router.reload({
                    only: ['generations'],
                    preserveState: true,
                    preserveScroll: true,
                    onFinish: () => {
                        isRefreshing = false;
                    },
                });
            }, 10000);
        }

        if (!hasPendingGenerations && pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
    },
    { immediate: true }
);

// Clean up the timer when leaving this page.
onUnmounted(() => {
    if (pollingInterval) {
        clearInterval(pollingInterval);
    }
});
</script>


<template>
    <Head title="Generation History" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Generation History
                </h2>

                <Link
                    :href="route('generations.create')"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-700"
                >
                    New Generation
                </Link>
            </div>
        </template>

        <div class="py-8">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

                <div
                    v-if="generations.data.length === 0"
                    class="rounded-lg bg-white p-8 text-center shadow"
                >
                    <h3 class="text-lg font-semibold text-gray-800">
                        No generations yet
                    </h3>

                    <p class="mt-2 text-gray-500">
                        Your generated images will appear here.
                    </p>
                </div>

                <div
                    v-for="generation in generations.data"
                    :key="generation.id"
                    class="overflow-hidden rounded-lg bg-white shadow"
                >
                    <div class="grid gap-6 p-6 md:grid-cols-3">

                        <!-- Image preview -->
                        <div>
                            <img
                                v-if="generation.image_url"
                                :src="generation.image_url"
                                alt="Generated image"
                                class="w-full rounded-lg object-cover"
                            />

                            <div
                                v-else
                                class="flex min-h-48 items-center justify-center rounded-lg bg-gray-100 text-gray-500"
                            >
                                <span v-if="generation.status === 'failed'">
                                    Generation failed
                                </span>

                                <span v-else-if="generation.status === 'processing'">
                                    Processing...
                                </span>

                                <span v-else>
                                    Waiting for image...
                                </span>
                            </div>
                        </div>

                        <!-- Generation details -->
                        <div class="space-y-3 md:col-span-2">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <h3 class="font-semibold text-gray-900">
                                    Generation #{{ generation.id }}
                                </h3>

                                <span
                                    class="rounded-full px-3 py-1 text-xs font-semibold capitalize"
                                    :class="statusClass(generation.status)"
                                >
                                    {{ generation.status }}
                                </span>
                            </div>

                            <p class="text-sm text-gray-700">
                                {{ generation.prompt }}
                            </p>

                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <p class="text-gray-500">Size</p>
                                    <p class="font-medium">{{ generation.size }}</p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Quality</p>
                                    <p class="font-medium capitalize">
                                        {{ generation.quality }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Estimated price</p>
                                    <p class="font-medium">
                                        {{ formatCost(generation.estimated_customer_price) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Actual customer price</p>
                                    <p class="font-medium">
                                        {{ formatCost(generation.actual_customer_price) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Provider cost</p>
                                    <p class="font-medium">
                                        {{ formatCost(generation.actual_provider_cost) }}
                                    </p>
                                </div>

                                <div>
                                    <p class="text-gray-500">Created</p>
                                    <p class="font-medium">
                                        {{ formatDate(generation.created_at) }}
                                    </p>
                                </div>
                            </div>

                            <div
                                v-if="generation.status === 'failed'"
                                class="rounded-md bg-red-50 p-3 text-sm text-red-700"
                            >
                                <p class="font-semibold">
                                    {{ generation.error_code || 'Generation error' }}
                                </p>

                                <p>{{ generation.error_message }}</p>
                            </div>

                            <p
                                v-if="generation.image_url_expires_at"
                                class="text-xs text-gray-500"
                            >
                                Image URL expires:
                                {{ formatDate(generation.image_url_expires_at) }}
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div
                    v-if="generations.links.length > 3"
                    class="flex flex-wrap justify-center gap-2"
                >
                    <template
                        v-for="(link, index) in generations.links"
                        :key="index"
                    >
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="rounded border px-3 py-2 text-sm"
                            :class="{
                                'bg-indigo-600 text-white': link.active,
                                'bg-white text-gray-700': !link.active,
                            }"
                            v-html="link.label"
                        />

                        <span
                            v-else
                            class="rounded border px-3 py-2 text-sm text-gray-400"
                            v-html="link.label"
                        />
                    </template>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
