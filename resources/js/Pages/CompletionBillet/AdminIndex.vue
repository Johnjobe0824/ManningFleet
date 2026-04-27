<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import { usePage, useForm } from "@inertiajs/vue3";
import { onMounted, reactive, ref, watch, computed } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
const { _, debounce, pickBy } = pkg;
import { loadToast } from "@/composables/loadToast";

import Button from "primevue/button";
import Tag from "primevue/tag";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Dialog from "primevue/dialog";
import Textarea from "primevue/textarea";

/* ── Props ── */
const props = defineProps({
    title:              { type: String, default: "Completion Billet Applications" },
    filters:            { type: Object, default: () => ({}) },
    completionBillets:  {
        type: Object,
        default: () => ({ data: [], total: 0, per_page: 10, current_page: 1 }),
    },
});

loadToast();

/* ── Reactive params ── */
const data = reactive({
    params: {
        search: props.filters?.search ?? "",
        status: props.filters?.status ?? "all",
    },
    billet: null,
});

/* ── Watch → Inertia GET (debounced) ── */
watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        router.get(
            route("completionBillets.adminIndex"),
            pickBy(data.params),
            { replace: true, preserveState: true, preserveScroll: true }
        );
    }, 150)
);

/* ── Pagination ── */
const onPageChange = (event) => {
    router.get(
        route("completionBillets.adminIndex"),
        { ...pickBy(data.params), page: event.page + 1 },
        { preserveState: true }
    );
};

/* ── Status update dialog ── */
const dialogVisible = ref(false);
const newStatus     = ref("");
const newRemarks    = ref("");
const form          = useForm({ status: "", remarks: "" });

const openStatusDialog = (record) => {
    data.billet         = record;
    newStatus.value     = record.status;
    newRemarks.value    = record.remarks ?? "";
    dialogVisible.value = true;
};

const submitStatusUpdate = () => {
    form.status  = newStatus.value;
    form.remarks = newRemarks.value;

    form.patch(
        route("completionBillets.updateStatus", data.billet?.id),
        {
            preserveScroll: true,
            onSuccess: () => {
                dialogVisible.value = false;
                form.reset();
            },
            onError: () => null,
        }
    );
};

/* ── Helpers ── */
const statusOptions = [
    { label: "All",      value: "all"      },
    { label: "Pending",  value: "pending"  },
    { label: "Approved", value: "approved" },
    { label: "Rejected", value: "rejected" },
];

const statusSeverity = (status) => {
    switch (status) {
        case "pending":  return "warn";
        case "approved": return "success";
        case "rejected": return "danger";
        default:         return "secondary";
    }
};
</script>

<template>
    <AppLayout>
        <div class="card">
            <div class="font-semibold text-xl mb-4">Completion Billet — Admin</div>

            <!-- Toolbar -->
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <IconField class="flex-1 min-w-[200px]">
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText
                        v-model="data.params.search"
                        placeholder="Search by name, document, status…"
                        class="w-full"
                    />
                </IconField>

                <Select
                    v-model="data.params.status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    placeholder="Filter by status"
                    class="w-44"
                />
            </div>

            <!-- DataTable -->
            <DataTable
                lazy
                :value="completionBillets.data"
                :rows="completionBillets.per_page"
                :total-records="completionBillets.total"
                :first="(completionBillets.current_page - 1) * completionBillets.per_page"
                paginator
                striped-rows
                table-style="min-width: 70rem"
                @page="onPageChange"
            >
                <template #empty>
                    <div class="text-center py-8 text-gray-400">
                        <i class="pi pi-inbox text-4xl block mb-2 opacity-30" />
                        No completion billet applications found.
                    </div>
                </template>
                <template #loading>
                    <div class="text-center py-8 text-gray-400">Loading…</div>
                </template>

                <!-- Applicant -->
                <Column header="Applicant">
                    <template #body="{ data: row }">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-800">{{ row.user?.name ?? '—' }}</span>
                            <span class="text-xs text-gray-400">{{ row.user?.email ?? '' }}</span>
                        </div>
                    </template>
                </Column>

                <!-- Sea Duty Record -->
                <Column header="Sea Duty Record">
                    <template #body="{ data: row }">
                        <span class="text-sm">{{ row.sea_duty_record?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Ships Order -->
                <Column header="Ships Order">
                    <template #body="{ data: row }">
                        <span class="text-sm">{{ row.ships_order?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- ENGRO / DGO Certificate -->
                <Column header="ENGRO / DGO Cert.">
                    <template #body="{ data: row }">
                        <span class="text-sm">{{ row.engro_dgo_cert?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Watch Stander Certificate -->
                <Column header="Watch Stander Cert.">
                    <template #body="{ data: row }">
                        <span class="text-sm text-gray-400 italic">{{ row.watchstander_cert?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Status -->
                <Column field="status" header="Status">
                    <template #body="{ data: row }">
                        <Tag :severity="statusSeverity(row.status)" :value="row.status" />
                    </template>
                </Column>

                <!-- Remarks -->
                <Column header="Remarks">
                    <template #body="{ data: row }">
                        <span class="text-sm text-gray-500 italic">{{ row.remarks ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions" style="width: 9rem">
                    <template #body="{ data: row }">
                        <Button
                            icon="pi pi-pencil"
                            label="Review"
                            size="small"
                            severity="info"
                            text
                            @click="openStatusDialog(row)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>

        <!-- ── Status Update Dialog ── -->
        <Dialog
            v-model:visible="dialogVisible"
            modal
            header="Update Completion Billet Status"
            :style="{ width: '30rem' }"
            :draggable="false"
        >
            <div v-if="data.billet" class="space-y-5 pt-1">

                <!-- Applicant info -->
                <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                    <p class="text-xs text-gray-500 mb-0.5">Applicant</p>
                    <p class="text-sm font-semibold text-gray-800">{{ data.billet.user?.name }}</p>
                    <p class="text-xs text-gray-400">{{ data.billet.user?.email }}</p>
                </div>

                <!-- Documents summary -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Sea Duty Record</p>
                        <p class="text-sm font-medium text-gray-700">
                            {{ data.billet.sea_duty_record?.name ?? '—' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Ships Order</p>
                        <p class="text-sm font-medium text-gray-700">
                            {{ data.billet.ships_order?.name ?? '—' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">ENGRO / DGO Cert.</p>
                        <p class="text-sm font-medium text-gray-700">
                            {{ data.billet.engro_dgo_cert?.name ?? '—' }}
                        </p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-3 border border-gray-100">
                        <p class="text-xs text-gray-500 mb-0.5">Watch Stander Cert.</p>
                        <p class="text-sm font-medium text-gray-700 italic">
                            {{ data.billet.watchstander_cert?.name ?? '—' }}
                        </p>
                    </div>
                </div>

                <!-- Status toggle buttons -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                    <div class="flex gap-2">
                        <button
                            v-for="opt in ['pending', 'approved', 'rejected']"
                            :key="opt"
                            @click="newStatus = opt"
                            class="flex-1 py-2 px-3 rounded-lg border text-sm font-medium capitalize transition-all"
                            :class="{
                                'border-yellow-400 bg-yellow-50 text-yellow-700': opt === 'pending'  && newStatus === opt,
                                'border-green-400  bg-green-50  text-green-700':  opt === 'approved' && newStatus === opt,
                                'border-red-400    bg-red-50    text-red-700':    opt === 'rejected' && newStatus === opt,
                                'border-gray-200 bg-white text-gray-500 hover:border-gray-300': newStatus !== opt,
                            }"
                        >
                            {{ opt }}
                        </button>
                    </div>
                </div>

                <!-- Remarks -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">
                        Remarks
                        <span class="font-normal text-gray-400">(optional)</span>
                    </label>
                    <Textarea
                        v-model="newRemarks"
                        rows="3"
                        class="w-full"
                        placeholder="Add a note for the applicant…"
                        auto-resize
                    />
                </div>
            </div>

            <template #footer>
                <div class="flex justify-end gap-2">
                    <Button
                        label="Cancel"
                        severity="secondary"
                        outlined
                        @click="dialogVisible = false"
                    />
                    <Button
                        label="Save Status"
                        icon="pi pi-check"
                        :loading="form.processing"
                        :disabled="!newStatus"
                        @click="submitStatusUpdate"
                    />
                </div>
            </template>
        </Dialog>
    </AppLayout>
</template>