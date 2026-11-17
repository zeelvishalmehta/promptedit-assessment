
<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
});
</script>

<template>
    <Head title="Image Generation" />

    <div class="min-h-screen bg-slate-950 text-white">
        <!-- Navigation -->
        <header class="border-b border-white/10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between px-6 py-5">
                <Link href="/" class="text-2xl font-bold tracking-tight">
                    <span class="text-violet-400">Pixel</span>forge
                </Link>

                <div class="flex items-center gap-3">
                    <template v-if="canLogin">
                        <template v-if="$page.props.auth.user">
                            <Link
                                :href="route('dashboard')"
                                class="rounded-lg px-4 py-2 text-sm text-slate-300 hover:text-white"
                            >
                                Dashboard
                            </Link>
                        </template>

                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="rounded-lg px-4 py-2 text-sm text-slate-300 hover:text-white"
                            >
                                Login
                            </Link>

                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="rounded-lg bg-violet-600 px-5 py-2.5 text-sm font-semibold hover:bg-violet-500"
                            >
                                Get Started
                            </Link>
                        </template>
                    </template>
                </div>
            </nav>
        </header>

        <!-- Hero -->
        <main>
            <section class="mx-auto max-w-7xl px-6 py-24 text-center sm:py-32">
                <div class="mx-auto max-w-3xl">
                    <span class="rounded-full border border-violet-400/30 bg-violet-400/10 px-4 py-2 text-sm text-violet-300">
                        Image Generation Platform
                    </span>

                    <h1 class="mt-8 text-4xl font-extrabold tracking-tight sm:text-6xl">
                        Turn Your Ideas Into
                        <span class="text-violet-400"> Stunning Images</span>
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-slate-400">
                         Bring your imagination to life. Describe your idea, choose your image
                         quality, and generate beautiful images with Pixelforge.
                    </p>

                    <div class="mt-10 flex flex-wrap justify-center gap-4">
                        <Link
                            :href="canLogin ? route('register') : route('login')"
                            class="rounded-xl bg-violet-600 px-8 py-4 font-semibold shadow-lg shadow-violet-900/30 transition hover:bg-violet-500"
                        >
                            Start Creating
                            <span aria-hidden="true"> →</span>
                        </Link>

                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="rounded-xl border border-white/15 px-8 py-4 font-semibold text-slate-300 transition hover:bg-white/5"
                        >
                            Sign In
                        </Link>
                    </div>
                </div>

                <!-- Decorative preview -->
                <div class="mx-auto mt-20 max-w-4xl rounded-2xl border border-white/10 bg-gradient-to-br from-violet-900/30 via-slate-900 to-blue-900/30 p-3 shadow-2xl">
                    <div class="rounded-xl border border-white/10 bg-slate-900/80 px-5 py-12 sm:py-16">
                        <div class="mx-auto max-w-2xl">
                            <div class="mb-6 text-left text-sm text-slate-400">
                                Imagine something amazing...
                            </div>

                            <div class="flex flex-col gap-3 rounded-xl border border-white/10 bg-slate-800 p-3 sm:flex-row">
                                <div class="flex-1 px-3 py-3 text-left text-sm text-slate-400">
                                    A beautiful sunset over the mountains...
                                </div>
                                <Link
                                    :href="canLogin ? route('register') : route('login')"
                                    class="rounded-lg bg-violet-600 px-6 py-3 text-center text-sm font-semibold hover:bg-violet-500"
                                >
                                    Generate Image
                                </Link>
                            </div>

                            <p class="mt-4 text-xs text-slate-500">
                                Your creativity. Powered by AI.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Features -->
            <section class="border-t border-white/10 bg-slate-900/50 px-6 py-20">
                <div class="mx-auto max-w-7xl">
                    <div class="mb-12 text-center">
                        <h2 class="text-3xl font-bold">
                            Everything You Need to Create
                        </h2>
                        <p class="mt-3 text-slate-400">
                            Simple tools to turn your imagination into reality.
                        </p>
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                            <div class="text-3xl">🎨</div>
                            <h3 class="mt-4 font-semibold">AI Image Generation</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Create images from simple text descriptions.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                            <div class="text-3xl">💰</div>
                            <h3 class="mt-4 font-semibold">Transparent Pricing</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Estimate your generation costs before submitting.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                            <div class="text-3xl">⚙️</div>
                            <h3 class="mt-4 font-semibold">Flexible Options</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Choose image dimensions and generation quality.
                            </p>
                        </div>

                        <div class="rounded-2xl border border-white/10 bg-slate-900 p-6">
                            <div class="text-3xl">🖼️</div>
                            <h3 class="mt-4 font-semibold">Generation History</h3>
                            <p class="mt-2 text-sm leading-6 text-slate-400">
                                Track your previous requests and generated images.
                            </p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- How it works -->
            <section class="mx-auto max-w-7xl px-6 py-20 text-center">
                <h2 class="text-3xl font-bold">How It Works</h2>

                <div class="mt-12 grid gap-10 sm:grid-cols-3">
                    <div>
                        <div class="text-3xl">✍️</div>
                        <h3 class="mt-4 font-semibold">1. Enter Your Prompt</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Describe the image you want to create.
                        </p>
                    </div>

                    <div>
                        <div class="text-3xl">🎛️</div>
                        <h3 class="mt-4 font-semibold">2. Choose Your Settings</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Select image dimensions and quality.
                        </p>
                    </div>

                    <div>
                        <div class="text-3xl">✨</div>
                        <h3 class="mt-4 font-semibold">3. Generate Your Image</h3>
                        <p class="mt-2 text-sm text-slate-400">
                            Submit your request and track its progress.
                        </p>
                    </div>
                </div>
            </section>
        </main>

        <!-- Footer -->
        <footer class="border-t border-white/10 px-6 py-8 text-center text-sm text-slate-500">
            <p>Powered by Pixelforge API</p>
            <p class="mt-2">Built with Laravel, Vue.js and Inertia.js</p>
        </footer>
    </div>
</template>
