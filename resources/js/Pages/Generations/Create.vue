
<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { ref, watch } from 'vue';


const prompt = ref('');
const size = ref('1024x1024');
const quality = ref('medium');
const referenceImagesText = ref('');

const estimatedPrice = ref(null);
const isEstimating = ref(false);
const isSubmitting = ref(false);

const errorMessage = ref('');
const successMessage = ref('');
const generation = ref(null);

watch([prompt, size, quality, referenceImagesText], () => {
    estimatedPrice.value = null;
});


function getPayload() {
    return {
        prompt: prompt.value,
        size: size.value,
        quality: quality.value,
        reference_images: referenceImagesText.value
            .split(/\r?\n|,/)
            .map(url => url.trim())
            .filter(url => url !== ''),
    };
}

async function calculateEstimate() {
    errorMessage.value = '';
    successMessage.value = '';
    estimatedPrice.value = null;

    isEstimating.value = true;

    try {
        const response = await axios.post(
            '/generations/estimate',
            getPayload()
        );

        estimatedPrice.value = response.data;
    } catch (error) {
        errorMessage.value =
            error.response?.data?.message ||
            'Unable to calculate the estimated price.';
    } finally {
        isEstimating.value = false;
    }
}

async function generateImage() {
    errorMessage.value = '';
    successMessage.value = '';
    generation.value = null;

    isSubmitting.value = true;

    try {
        const response = await axios.post(
            '/generations',
            getPayload()
        );

        generation.value = response.data.generation;

        successMessage.value =
            'Your image generation request was submitted successfully.';

        estimatedPrice.value = null;
    } catch (error) {
        const errors = error.response?.data?.errors;

        if (errors) {
            errorMessage.value = Object.values(errors)
                .flat()
                .join(' ');
        } else {
            errorMessage.value =
                error.response?.data?.message ||
                'Unable to submit your generation request.';
        }
    } finally {
        isSubmitting.value = false;
    }
}
</script>

<template>
    <Head title="Create Generation" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Create Image
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-4xl sm:px-6 lg:px-8">

                <div class="rounded-lg bg-white p-6 shadow-sm">

                    <h3 class="mb-6 text-lg font-semibold text-gray-900">
                        Generate an AI Image
                    </h3>

                    <!-- Prompt -->
                    <div class="mb-6">
                        <label class="mb-2 block font-medium text-gray-700">
                            Image Prompt
                        </label>

                        <textarea
                            v-model="prompt"
                            rows="5"
                            maxlength="4000"
                            placeholder="Describe the image you want to generate..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        ></textarea>

                        <p class="mt-1 text-sm text-gray-500">
                            {{ prompt.length }} / 4000 characters
                        </p>
                    </div>

                    <!-- Size -->
                    <div class="mb-6">
                        <label class="mb-2 block font-medium text-gray-700">
                            Image Size
                        </label>

                        <select
                            v-model="size"
                            class="w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="1024x1024">1024 × 1024</option>
                            <option value="1536x1024">1536 × 1024</option>
                            <option value="1024x1536">1024 × 1536</option>
                        </select>
                    </div>

                    <!-- Quality -->
                    <div class="mb-6">
                        <label class="mb-2 block font-medium text-gray-700">
                            Image Quality
                        </label>

                        <select
                            v-model="quality"
                            class="w-full rounded-md border-gray-300 shadow-sm"
                        >
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                        </select>
                    </div>

                    <!-- Reference Images -->
                    <div class="mb-6">
                        <label class="mb-2 block font-medium text-gray-700">
                            Reference Image URLs (Optional)
                        </label>

                        <textarea
                            v-model="referenceImagesText"
                            rows="3"
                            placeholder="Enter one image URL per line"
                            class="w-full rounded-md border-gray-300 shadow-sm"
                        ></textarea>

                        <p class="mt-1 text-sm text-gray-500">
                            Maximum 4 URLs. Separate multiple URLs using new lines.
                        </p>
                    </div>

                    <!-- Errors -->
                    <div
                        v-if="errorMessage"
                        class="mb-4 rounded-md bg-red-50 p-3 text-sm text-red-700"
                    >
                        {{ errorMessage }}
                    </div>

                    <!-- Success -->
                    <div
                        v-if="successMessage"
                        class="mb-4 rounded-md bg-green-50 p-3 text-sm text-green-700"
                    >
                        {{ successMessage }}
                    </div>

                    <!-- Estimated Price -->
                    <div
                        v-if="estimatedPrice"
                        class="mb-6 rounded-lg border border-indigo-200 bg-indigo-50 p-4"
                    >
                        <h4 class="font-semibold text-gray-900">
                            Estimated Price
                        </h4>

                        <p class="mt-2 text-sm text-gray-600">
                            Estimated provider cost:
                            ${{ estimatedPrice.estimated_provider_cost }}
                        </p>

                        <p class="mt-2 text-xl font-bold text-indigo-700">
                            Customer price:
                            ${{ estimatedPrice.estimated_customer_price }}
                        </p>

                        <p class="mt-1 text-xs text-gray-500">
                            Includes 25% markup. Final cost is based on
                            actual provider usage.
                        </p>
                    </div>

                    <!-- Generation result -->
                    <div
                        v-if="generation"
                        class="mb-6 rounded-lg border border-green-200 p-4"
                    >
                        <h4 class="font-semibold text-gray-900">
                            Generation Submitted
                        </h4>

                        <p class="mt-2 text-sm text-gray-600">
                            Generation ID:
                            {{ generation.id }}
                        </p>

                        <p class="mt-1 text-sm text-gray-600">
                            Status: {{ generation.status }}
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex flex-wrap gap-3">

                        <button
                            type="button"
                            @click="calculateEstimate"
                            :disabled="isEstimating || isSubmitting"
                            class="rounded-md bg-gray-800 px-5 py-2.5 text-white hover:bg-gray-700 disabled:opacity-50"
                        >
                            {{
                                isEstimating
                                    ? 'Calculating...'
                                    : 'Calculate Estimate'
                            }}
                        </button>

                        <button
                            type="button"
                            @click="generateImage"
                            :disabled="
                                isSubmitting ||
                                isEstimating ||
                                !estimatedPrice
                            "
                            class="rounded-md bg-indigo-600 px-5 py-2.5 text-white hover:bg-indigo-700 disabled:opacity-50"
                        >
                            {{
                                isSubmitting
                                    ? 'Submitting...'
                                    : 'Generate Image'
                            }}
                        </button>

                    </div>

                    <p class="mt-3 text-xs text-gray-500">
                        Calculate the estimate before submitting your request.
                    </p>

                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
