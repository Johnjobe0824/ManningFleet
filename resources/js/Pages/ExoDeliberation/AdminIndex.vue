<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import { ref, computed } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button from "primevue/button";
import Tag from "primevue/tag";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import Select from "primevue/select";
import Textarea from "primevue/textarea";
import Dialog from "primevue/dialog";
import { useToast } from "primevue/usetoast";
import axios from "axios";

/* ── Props ── */
const props = defineProps({
    title:            { type: String,  default: "EXO Deliberation — Admin" },
    exoDeliberations: { type: Object,  required: true },
    filters:          { type: Object,  default: () => ({}) },
});

const toast = useToast();

/* ── Filters ── */
const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "all");

const statusOptions = [
    { label: "All",       value: "all"       },
    { label: "Pending",   value: "pending"   },
    { label: "Scheduled", value: "scheduled" },
    { label: "Approved",  value: "approved"  },
    { label: "Rejected",  value: "rejected"  },
];

let searchTimeout = null;
const applyFilters = () => {
    router.get(route("exoDeliberations.adminIndex"), {
        search: search.value || undefined,
        status: status.value !== "all" ? status.value : undefined,
    }, { preserveState: true, replace: true });
};

const onSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
};

/* ── Action Dialog ── */
const dialog = ref({
    visible:     false,
    record:      null,
    status:      null,
    remarks:     "",
    scheduledAt: "",
});

const actionOptions = [
    { label: "Approve",   value: "approved"  },
    { label: "Reject",    value: "rejected"  },
    { label: "Schedule",  value: "scheduled" },
    { label: "Pending",   value: "pending"   },
];

const openDialog = (record) => {
    dialog.value = {
        visible:     true,
        record,
        status:      record.status,
        remarks:     record.remarks ?? "",
        scheduledAt: record.scheduled_at
            ? new Date(record.scheduled_at).toISOString().slice(0, 16)
            : "",
    };
};

const submitting = ref(false);

const submitAction = async () => {
    submitting.value = true;
    try {
        await axios.patch(route("exoDeliberations.updateStatus", dialog.value.record.id), {
            status:       dialog.value.status,  
            remarks:      dialog.value.remarks || null,
            scheduled_at: dialog.value.status === "scheduled"
                ? dialog.value.scheduledAt || null
                : null,
        });

        toast.add({
            severity: "success",
            summary:  "Updated",
            detail:   "Application status updated successfully.",
            life:     4000,
        });

        dialog.value.visible = false;
        router.reload({ only: ["exoDeliberations"] });

    } catch (err) {
        toast.add({
            severity: "error",
            summary:  "Error",
            detail:   err.response?.data?.message ?? "Update failed.",
            life:     5000,
        });
    } finally {
        submitting.value = false;
    }
};

/* ── Helpers ── */
const statusSeverity = (s) => ({
    pending:   "warn",
    approved:  "success",
    rejected:  "danger",
    scheduled: "info",
}[s] ?? "secondary");

const formatDate = (val) =>
    val ? new Date(val).toLocaleDateString("en-PH", {
        year: "numeric", month: "short", day: "numeric",
        hour: "2-digit", minute: "2-digit",
    }) : "—";

const onPageChange = (event) => {
    router.get(route("exoDeliberations.adminIndex"), {
        page:   event.page + 1,
        search: search.value  || undefined,
        status: status.value !== "all" ? status.value : undefined,
    }, { preserveState: true });
};
</script>

<template>
    <AppLayout>
    <div class="space-y-6">

        <!-- ── Header ── -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">EXO Deliberation</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Review applications and schedule SLMC schooling.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-2 items-center">
                <Select
                    v-model="status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    class="w-36"
                    @change="applyFilters"
                />
                <IconField>
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText
                        v-model="search"
                        placeholder="Search name / email…"
                        @input="onSearch"
                    />
                </IconField>
            </div>
        </div>

        <!-- ── Stats Bar ── -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div v-for="opt in statusOptions.slice(1)" :key="opt.value"
                class="rounded-xl border bg-white px-4 py-3 flex items-center gap-3 shadow-sm cursor-pointer hover:border-blue-300 transition"
                @click="status = opt.value; applyFilters()"
            >
                <Tag :severity="statusSeverity(opt.value)" :value="opt.label" class="text-xs" />
                <span class="text-lg font-bold text-gray-700">
                    {{ exoDeliberations.data.filter(r => r.status === opt.value).length }}
                    <span class="text-xs font-normal text-gray-400">/ {{ exoDeliberations.total }}</span>
                </span>
            </div>
        </div>

        <!-- ── Table ── -->
        <div class="card">
            <DataTable
                :value="exoDeliberations.data"
                table-style="min-width: 70rem"
                striped-rows
            >
                <template #empty>
                    <div class="text-center py-8 text-gray-400">
                        No EXO Deliberation applications found.
                    </div>
                </template>

                <!-- Applicant -->
                <Column header="Applicant" style="min-width: 12rem">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-800">{{ data.user?.name ?? '—' }}</span>
                            <span class="text-xs text-gray-400">{{ data.user?.email ?? '' }}</span>
                        </div>
                    </template>
                </Column>

                <!-- Documents -->
                <Column header="Junior Billet">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.junior_billet_completion?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="SLMC Endorsement">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.slmc_endorsement?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="SLMC Orders">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.slmc_orders?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Graduation Cert.">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.slmc_graduation_cert?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Schedule -->
                <Column header="Scheduled At">
                    <template #body="{ data }">
                        <span class="text-xs" :class="data.scheduled_at ? 'text-blue-600 font-medium' : 'text-gray-400'">
                            {{ formatDate(data.scheduled_at) }}
                        </span>
                    </template>
                </Column>

                <!-- Status -->
                <Column header="Status" style="width: 8rem">
                    <template #body="{ data }">
                        <Tag :severity="statusSeverity(data.status)" :value="data.status" />
                    </template>
                </Column>

                <!-- Remarks -->
                <Column header="Remarks">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-500 italic">{{ data.remarks ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions" style="width: 7rem">
                    <template #body="{ data }">
                        <Button
                            icon="pi pi-pencil"
                            label="Manage"
                            size="small"
                            severity="info"
                            text
                            @click="openDialog(data)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Paginator
                v-if="exoDeliberations.total > exoDeliberations.per_page"
                :rows="exoDeliberations.per_page"
                :total-records="exoDeliberations.total"
                :first="(exoDeliberations.current_page - 1) * exoDeliberations.per_page"
                @page="onPageChange"
                class="mt-4"
            />
        </div>

        <!-- ── Action Dialog ── -->
        <Dialog
            v-model:visible="dialog.visible"
            modal
            header="Manage Application"
            :style="{ width: '480px' }"
            :draggable="false"
        >
            <div v-if="dialog.record" class="space-y-5 pt-1">

                <!-- Applicant info -->
                <div class="rounded-lg bg-gray-50 border px-4 py-3">
                    <p class="text-sm font-semibold text-gray-800">{{ dialog.record.user?.name }}</p>
                    <p class="text-xs text-gray-400">{{ dialog.record.user?.email }}</p>
                </div>

                <!-- Documents summary -->
                <div class="space-y-1.5">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Submitted Documents</p>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">Junior Billet</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.junior_billet_completion?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">SLMC Endorsement</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.slmc_endorsement?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">SLMC Orders</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.slmc_orders?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1">
                        <span class="text-gray-500">Graduation Cert.</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.slmc_graduation_cert?.name ?? '—' }}</span>
                    </div>
                </div>

                <!-- Status selector -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Decision</label>
                    <Select
                        v-model="dialog.status"
                        :options="actionOptions"
                        option-label="label"
                        option-value="value"
                        class="w-full"
                    />
                </div>

                <!-- Schedule date — only shown when status is 'scheduled' -->
                <div v-if="dialog.status === 'scheduled'" class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">
                        SLMC Schooling Date <span class="text-red-400">*</span>
                    </label>
                    <input
                        v-model="dialog.scheduledAt"
                        type="datetime-local"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p class="text-xs text-blue-500">Set the date and time for the applicant's SLMC schooling.</p>
                </div>

                <!-- Remarks -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Remarks</label>
                    <Textarea
                        v-model="dialog.remarks"
                        rows="3"
                        class="w-full"
                        placeholder="Add remarks or instructions for the applicant…"
                        auto-resize
                    />
                </div>
            </div>

            <template #footer>
                <Button
                    label="Cancel"
                    severity="secondary"
                    outlined
                    @click="dialog.visible = false"
                />
                <Button
                    label="Save Decision"
                    icon="pi pi-check"
                    severity="success"
                    :loading="submitting"
                    @click="submitAction"
                />
            </template>
        </Dialog>
    </div>
    </AppLayout>
</template>