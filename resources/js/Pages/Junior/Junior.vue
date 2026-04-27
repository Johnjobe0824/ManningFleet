<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import JuniorVesselAssignmentView from "@/Components/JuniorVesselAssignmentView.vue";
import { Head } from "@inertiajs/vue3";

defineProps({
    user:                    { type: Object, required: true },
    juniorVesselAssignments: { type: Object, default: () => ({ data: [], total: 0, current_page: 1 }) },
    carryMorningReports:     { type: Array,  default: () => [] },
    certifications:          { type: Array,  default: () => [] },
    vessels:                 { type: Array,  default: () => [] },
    positions:               { type: Array,  default: () => [] },
});
</script>

<template>
    <Head title="Junior Vessel Assignment" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <i class="pi pi-send text-blue-600 text-xl" />
                <h2 class="text-xl font-semibold text-gray-800 leading-tight">
                    Junior Vessel Assignment
                </h2>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <JuniorVesselAssignmentView
                    :user="user"
                    :junior-vessel-assignments="juniorVesselAssignments"
                    :carry-morning-reports="carryMorningReports"
                    :certifications="certifications"
                    :vessels="vessels"
                    :positions="positions"
                />
            </div>
        </div>
    </AuthenticatedLayout>
</template>