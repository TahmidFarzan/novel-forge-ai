<script setup>
import layout from "@/pages/layouts/PublicLayout.vue";
import AuthCard from "@/components/common/layout/public-layout/AuthCard.vue";

import { ref } from "vue";
import { Head, router as inertiaJsRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library } from "@fortawesome/fontawesome-svg-core";
import { faSpinner } from "@fortawesome/free-solid-svg-icons";

library.add(faSpinner);

defineOptions({ layout: layout });

const appName = import.meta.env.VITE_APP_NAME || "Novel Forge AI";
const appFavicon = import.meta.env.VITE_APP_FAVICON || "/uploads/icons/app/favicon.png";

const resending = ref(false);

function handleResendVerification() {
    if (resending.value) return;

    resending.value = true;

    inertiaJsRoute.post(
        route("email-verify.resend"),
        {},
        {
            onFinish: () => {
                resending.value = false;
            },
        },
    );
}
</script>

<template>
    <Head title="Email Verification" />

    <AuthCard
        :logo="appFavicon"
        :alt="appName"
        eyebrow="Email verification"
        :subtitle="`Secure your ${appName} workspace`"
        title="Verify Your Email"
        description="Please verify your email address to continue."
    >
        <template #story>
            <div class="flex items-center justify-center rounded-2xl bg-blue-100 p-6">
                <img
                    :src="'/uploads/icons/auth/user-check.png'"
                    alt="User verification"
                    class="w-3/4 max-w-xs object-contain"
                />
            </div>
        </template>

        <form class="mt-8" @submit.prevent="handleResendVerification">
            <button
                type="submit"
                :disabled="resending"
                class="flex w-full items-center justify-center gap-2 rounded bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 disabled:opacity-50"
            >
                <FontAwesomeIcon v-if="resending" icon="spinner" spin />

                {{
                    resending
                        ? "Sending..."
                        : "Resend Verification Email"
                }}
            </button>
        </form>
    </AuthCard>
</template>