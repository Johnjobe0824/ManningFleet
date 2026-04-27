<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import { ref } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import Button      from "primevue/button";
import Tag         from "primevue/tag";
import Select      from "primevue/select";
import InputText   from "primevue/inputtext";
import IconField   from "primevue/iconfield";
import InputIcon   from "primevue/inputicon";
import DataTable   from "primevue/datatable";
import Column      from "primevue/column";
import Paginator   from "primevue/paginator";
import Dialog      from "primevue/dialog";
import Textarea    from "primevue/textarea";
import { useToast } from "primevue/usetoast";
import axios from "axios";

/* ============================================================
   PROPS
============================================================ */
const props = defineProps({
    title:        { type: String, default: "EXO Reassignment — Admin" },
    filters:      { type: Object, default: () => ({}) },
    exoReassigns: { type: Object, required: true },
});

const toast = useToast();
const page  = usePage();
const can   = (perms) => perms.some((p) => page.props.auth?.permissions?.includes(p));

/* ============================================================
   FILTERS
============================================================ */
const search = ref(props.filters.search ?? "");
const status = ref(props.filters.status ?? "all");

const statusOptions = [
    { label: "All",        value: "all"        },
    { label: "Pending",    value: "pending"    },
    { label: "Scheduled",  value: "scheduled"  },
    { label: "Approved",   value: "approved"   },
    { label: "Rejected",   value: "rejected"   },
    { label: "Completed",  value: "completed"  },
];

const actionOptions = [
    { label: "Approve",    value: "approved"   },
    { label: "Reject",     value: "rejected"   },
    { label: "Schedule",   value: "scheduled"  },
    { label: "Pending",    value: "pending"    },
    { label: "Completed",  value: "completed"  },
];

let searchTimeout = null;
const applyFilters = () => {
    router.get(
        route("exoReassignments.adminIndex"),
        {
            search: search.value || undefined,
            status: status.value !== "all" ? status.value : undefined,
        },
        { preserveState: true, replace: true },
    );
};
const onSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 300);
};

/* ============================================================
   ACTION DIALOG
============================================================ */
const dialog = ref({
    visible:     false,
    record:      null,
    status:      null,
    remarks:     "",
    scheduledAt: "",
});

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
        await axios.patch(
            route("exoReassignments.updateStatus", dialog.value.record.id),
            {
                status:       dialog.value.status,
                remarks:      dialog.value.remarks || null,
                scheduled_at: dialog.value.status === "scheduled"
                    ? dialog.value.scheduledAt || null
                    : null,
            },
        );

        toast.add({
            severity: "success",
            summary:  "Updated",
            detail:   "Application status updated successfully.",
            life:     4000,
        });

        dialog.value.visible = false;
        router.reload({ only: ["exoReassigns"] });

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

/* ============================================================
   DELETE
============================================================ */
const deleteRecord = async (id) => {
    if (!confirm("Delete this EXO Reassignment application?")) return;
    try {
        await axios.delete(route("exoReassignments.destroy", id));
        toast.add({ severity: "info", summary: "Deleted", detail: "Application deleted.", life: 3000 });
        router.reload({ only: ["exoReassigns"] });
    } catch (err) {
        toast.add({
            severity: "error",
            summary:  "Delete Failed",
            detail:   err.response?.data?.message ?? "Unable to delete application.",
            life:     4000,
        });
    }
};

/* ============================================================
   PAGINATION
============================================================ */
const onPageChange = (event) => {
    router.get(
        route("exoReassignments.adminIndex"),
        {
            page:   event.page + 1,
            search: search.value  || undefined,
            status: status.value !== "all" ? status.value : undefined,
        },
        { preserveState: true },
    );
};

/* ============================================================
   HELPERS
============================================================ */
const statusSeverity = (s) => ({
    pending:    "warn",
    scheduled:  "info",
    approved:   "success",
    rejected:   "danger",
    completed:  "success",
}[s] ?? "secondary");

const statusLabel = (s) => ({
    pending:    "Pending",
    scheduled:  "Scheduled",
    approved:   "Approved",
    rejected:   "Rejected",
    completed:  "Completed",
}[s] ?? s);

const formatDate = (val) =>
    val
        ? new Date(val).toLocaleDateString("en-PH", {
              year: "numeric", month: "short", day: "numeric",
              hour: "2-digit", minute: "2-digit",
          })
        : "—";
</script>

<template>
    <AppLayout>
    <div class="space-y-6">

        <!-- ══ PAGE HEADER ══════════════════════════════════════ -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">EXO Reassignment — Admin</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Review applications and manage EXO Reassignment status.
                </p>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-2 items-center">
                <Select
                    v-model="status"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    class="w-40"
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

        <!-- ══ STATS BAR ════════════════════════════════════════ -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div
                v-for="opt in statusOptions.filter(o => ['pending','scheduled','approved','rejected'].includes(o.value))"
                :key="opt.value"
                class="rounded-xl border bg-white px-4 py-3 flex items-center gap-3 shadow-sm cursor-pointer hover:border-blue-300 transition"
                @click="status = opt.value; applyFilters()"
            >
                <Tag :severity="statusSeverity(opt.value)" :value="opt.label" class="text-xs" />
                <span class="text-lg font-bold text-gray-700">
                    {{ exoReassigns.data.filter(r => r.status === opt.value).length }}
                    <span class="text-xs font-normal text-gray-400">/ {{ exoReassigns.total }}</span>
                </span>
            </div>
        </div>

        <!-- ══ TABLE ════════════════════════════════════════════ -->
        <div class="card">
            <DataTable
                :value="exoReassigns.data"
                table-style="min-width: 70rem"
                striped-rows
            >
                <template #empty>
                    <div class="text-center py-8 text-gray-400">
                        <i class="pi pi-inbox text-3xl block mb-2 opacity-40" />
                        No EXO Reassignment applications found.
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

                <!-- Assignment Orders -->
                <Column header="Assignment Orders" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.assignment_orders?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Performance Eval -->
                <Column header="Performance Eval" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.performance_eval?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Certification -->
                <Column header="Certification" style="min-width: 9rem">
                    <template #body="{ data }">
                        <span class="text-xs" :class="data.certification ? 'text-gray-600' : 'text-gray-400 italic'">
                            {{ data.certification?.name ?? '— none —' }}
                        </span>
                    </template>
                </Column>

                <!-- Scheduled At -->
                <Column header="Scheduled At" style="min-width: 9rem">
                    <template #body="{ data }">
                        <span class="text-xs" :class="data.scheduled_at ? 'text-blue-600 font-medium' : 'text-gray-400'">
                            {{ formatDate(data.scheduled_at) }}
                        </span>
                    </template>
                </Column>

                <!-- Status -->
                <Column header="Status" style="width: 8rem">
                    <template #body="{ data }">
                        <Tag :severity="statusSeverity(data.status)" :value="statusLabel(data.status)" />
                    </template>
                </Column>

                <!-- Remarks -->
                <Column header="Remarks" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-500 italic">{{ data.remarks ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Submitted -->
                <Column header="Submitted" style="min-width: 9rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-400">{{ formatDate(data.created_at) }}</span>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions" style="width: 10rem">
                    <template #body="{ data }">
                        <div class="flex items-center gap-2">
                            <Button
                                icon="pi pi-pencil"
                                label="Manage"
                                size="small"
                                severity="info"
                                text
                                @click="openDialog(data)"
                            />
                            <Button
                                v-if="can(['delete exo_reassign'])"
                                icon="pi pi-trash"
                                size="small"
                                severity="danger"
                                text
                                v-tooltip.top="'Delete'"
                                @click="deleteRecord(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <Paginator
                v-if="exoReassigns.total > exoReassigns.per_page"
                :rows="exoReassigns.per_page"
                :total-records="exoReassigns.total"
                :first="(exoReassigns.current_page - 1) * exoReassigns.per_page"
                class="mt-4"
                @page="onPageChange"
            />
        </div>

        <!-- ══ MANAGE DIALOG ════════════════════════════════════ -->
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
                        <span class="text-gray-500">Assignment Orders</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.assignment_orders?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">Performance Eval</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.performance_eval?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1">
                        <span class="text-gray-500">Certification</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.certification?.name ?? '— none —' }}</span>
                    </div>
                </div>

                <!-- Decision selector -->
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

                <!-- Scheduled date — only when status = scheduled -->
                <div v-if="dialog.status === 'scheduled'" class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">
                        Scheduled Date &amp; Time <span class="text-red-400">*</span>
                    </label>
                    <input
                        v-model="dialog.scheduledAt"
                        type="datetime-local"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p class="text-xs text-blue-500">Set the date and time for the reassignment schedule.</p>
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
                    :disabled="submitting"
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