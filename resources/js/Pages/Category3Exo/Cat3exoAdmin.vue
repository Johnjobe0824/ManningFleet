<script setup>
import AppLayout  from "@/sakai/layout/AppLayout.vue";
import { ref, computed, onMounted } from "vue";
import { usePage } from "@inertiajs/vue3";
import Button     from "primevue/button";
import Tag        from "primevue/tag";
import Select     from "primevue/select";
import InputText  from "primevue/inputtext";
import IconField  from "primevue/iconfield";
import InputIcon  from "primevue/inputicon";
import DataTable  from "primevue/datatable";
import Column     from "primevue/column";
import Paginator  from "primevue/paginator";
import Dialog     from "primevue/dialog";
import Textarea   from "primevue/textarea";
import { useToast } from "primevue/usetoast";
import axios from "axios";

/* ============================================================
   PROPS
============================================================ */
defineProps({
    title:             { type: String, default: "CAT 3 EXO Promotion — Admin" },
    filters:           { type: Object, default: () => ({}) },
    cat3ExoPromotions: { type: Object, default: () => ({}) },
});

const toast = useToast();
const page  = usePage();
const can   = (perms) => perms.some((p) => page.props.auth?.permissions?.includes(p));

/* ============================================================
   STATUS / ACTION OPTIONS
============================================================ */
const statusOptions = [
    { label: "All",        value: "all"        },
    { label: "Pending",    value: "pending"    },
    { label: "For Review", value: "for_review" },
    { label: "Scheduled",  value: "scheduled"  },
    { label: "Approved",   value: "approved"   },
    { label: "Rejected",   value: "rejected"   },
    { label: "Completed",  value: "completed"  },
];

const actionOptions = [
    { label: "Set to Pending",  value: "pending"    },
    { label: "Mark For Review", value: "for_review" },
    { label: "Schedule Board",  value: "scheduled"  },
    { label: "Approve",         value: "approved"   },
    { label: "Reject",          value: "rejected"   },
    { label: "Mark Completed",  value: "completed"  },
];

const statusSeverity = (s) =>
    ({ pending: "warn", for_review: "info", scheduled: "info", approved: "success", rejected: "danger", completed: "success" }[s] ?? "secondary");

const statusLabel = (s) =>
    ({ pending: "Pending", for_review: "For Review", scheduled: "Scheduled", approved: "Approved", rejected: "Rejected", completed: "Completed" }[s] ?? s);

const formatDate = (val) =>
    val ? new Date(val).toLocaleDateString("en-PH", { year: "numeric", month: "short", day: "numeric", hour: "2-digit", minute: "2-digit" }) : "—";

/* ============================================================
   TABLE STATE
============================================================ */
const records       = ref([]);
const totalRecords  = ref(0);
const currentPage   = ref(1);
const rowsPerPage   = ref(10);
const loadingTable  = ref(false);
const searchQuery   = ref("");
const filterStatus  = ref("all");
const searchTimeout = ref(null);

const stats = computed(() => {
    const counts = { pending: 0, for_review: 0, scheduled: 0, approved: 0, rejected: 0, completed: 0 };
    records.value.forEach((r) => { if (counts[r.status] !== undefined) counts[r.status]++; });
    return counts;
});

/* ============================================================
   FETCH
============================================================ */
const fetchRecords = async (pg = 1) => {
    loadingTable.value = true;
    try {
        const { data } = await axios.get(route("cat3ExoPromotions.adminIndex"), {
            params: {
                page:     pg,
                per_page: rowsPerPage.value,
                search:   searchQuery.value || undefined,
                status:   filterStatus.value !== "all" ? filterStatus.value : undefined,
            },
            headers: { Accept: "application/json" },
        });
        records.value      = data.data;
        totalRecords.value = data.total;
        currentPage.value  = data.current_page;
    } catch {
        toast.add({ severity: "error", summary: "Load Failed", detail: "Unable to load applications.", life: 4000 });
    } finally {
        loadingTable.value = false;
    }
};

const onSearch     = () => { clearTimeout(searchTimeout.value); searchTimeout.value = setTimeout(() => fetchRecords(1), 300); };
const onFilter     = (val) => { filterStatus.value = val; fetchRecords(1); };
const onPageChange = (event) => fetchRecords(event.page + 1);

onMounted(() => fetchRecords(1));

/* ============================================================
   EXPANDED ROWS
============================================================ */
const expandedRows = ref({});
const toggleRow = (id) => {
    expandedRows.value = expandedRows.value[id]
        ? Object.fromEntries(Object.entries(expandedRows.value).filter(([k]) => k != id))
        : { ...expandedRows.value, [id]: true };
};

/* ============================================================
   MANAGE DIALOG
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
    if (dialog.value.status === "scheduled" && !dialog.value.scheduledAt) {
        toast.add({ severity: "warn", summary: "Date Required", detail: "Please set a board scheduled date.", life: 3000 });
        return;
    }
    submitting.value = true;
    try {
        await axios.patch(
            route("cat3ExoPromotions.updateStatus", dialog.value.record.id),
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
            detail:   `Application #${dialog.value.record.id} → "${statusLabel(dialog.value.status)}".`,
            life:     4000,
        });
        dialog.value.visible = false;
        await fetchRecords(currentPage.value);
    } catch (err) {
        toast.add({
            severity: "error",
            summary:  "Update Failed",
            detail:   err.response?.data?.message ?? "Could not update status.",
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
    if (!confirm("Delete this CAT 3 EXO Promotion application?")) return;
    try {
        await axios.delete(route("cat3ExoPromotions.destroy", id));
        toast.add({ severity: "info", summary: "Deleted", detail: "Application deleted.", life: 3000 });
        await fetchRecords(currentPage.value);
    } catch (err) {
        toast.add({
            severity: "error",
            summary:  "Delete Failed",
            detail:   err.response?.data?.message ?? "Unable to delete application.",
            life:     4000,
        });
    }
};
</script>

<template>
    <AppLayout>
    <div class="space-y-6">

        <!-- ══ PAGE HEADER ══════════════════════════════════════ -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <h2 class="text-xl font-bold text-gray-800">CAT 3 EXO Promotion — Admin</h2>
                <p class="text-sm text-gray-500 mt-0.5">Review applications and manage CAT 3 EXO Promotion status.</p>
            </div>
            <div class="flex flex-wrap gap-2 items-center">
                <Select
                    v-model="filterStatus"
                    :options="statusOptions"
                    option-label="label"
                    option-value="value"
                    class="w-40"
                    @change="onFilter(filterStatus)"
                />
                <IconField>
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText v-model="searchQuery" placeholder="Search name / email…" @input="onSearch" />
                </IconField>
            </div>
        </div>

        <!-- ══ STATS BAR ════════════════════════════════════════ -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
            <div
                v-for="s in statusOptions.filter(o => o.value !== 'all')"
                :key="s.value"
                class="rounded-xl border bg-white px-4 py-3 flex flex-col gap-1 shadow-sm cursor-pointer hover:border-blue-300 transition"
                :class="filterStatus === s.value ? 'ring-2 ring-blue-400 border-transparent' : 'border-gray-200'"
                @click="onFilter(s.value)"
            >
                <span class="text-xs font-medium text-gray-500">{{ s.label }}</span>
                <span class="text-2xl font-bold text-gray-800">{{ stats[s.value] ?? 0 }}</span>
                <Tag :severity="statusSeverity(s.value)" :value="s.label" class="text-xs w-fit" />
            </div>
        </div>

        <!-- ══ TABLE ════════════════════════════════════════════ -->
        <div class="card">
            <DataTable
                :value="records"
                :loading="loadingTable"
                lazy
                table-style="min-width: 70rem"
                striped-rows
                data-key="id"
            >
                <template #empty>
                    <div class="text-center py-8 text-gray-400">
                        <i class="pi pi-inbox text-3xl block mb-2 opacity-40" />
                        No CAT 3 EXO Promotion applications found.
                    </div>
                </template>
                <template #loading>
                    <div class="text-center py-8 text-gray-400">
                        <i class="pi pi-spin pi-spinner text-2xl block mb-2" />Loading…
                    </div>
                </template>

                <!-- Expand -->
                <Column style="width: 3rem">
                    <template #body="{ data }">
                        <Button
                            :icon="expandedRows[data.id] ? 'pi pi-chevron-down' : 'pi pi-chevron-right'"
                            text rounded size="small" severity="secondary"
                            @click="toggleRow(data.id)"
                        />
                    </template>
                </Column>

                <!-- ID -->
                <Column field="id" header="#" style="width: 4rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-400 font-mono">#{{ data.id }}</span>
                    </template>
                </Column>

                <!-- Applicant -->
                <Column header="Applicant" style="min-width: 12rem">
                    <template #body="{ data }">
                        <div class="flex flex-col">
                            <span class="text-sm font-medium text-gray-800">{{ data.user?.name ?? '—' }}</span>
                            <span class="text-xs text-gray-400">{{ data.user?.email ?? '' }}</span>
                        </div>
                    </template>
                </Column>

                <!-- Sea Duty Records -->
                <Column header="Sea Duty Records" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="text-xs text-gray-600">{{ data.assignment_orders?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Request Letter -->
                <Column header="Request Letter" style="min-width: 10rem">
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
                <Column header="Scheduled At" style="min-width: 10rem">
                    <template #body="{ data }">
                        <span class="text-xs" :class="data.scheduled_at ? 'text-blue-600 font-medium' : 'text-gray-400'">
                            {{ formatDate(data.scheduled_at) }}
                        </span>
                    </template>
                </Column>

                <!-- Status -->
                <Column header="Status" style="width: 9rem">
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
                                v-if="can(['delete cat3_exo'])"
                                icon="pi pi-trash"
                                size="small"
                                severity="danger"
                                text
                                :disabled="data.status !== 'pending'"
                                v-tooltip.top="data.status !== 'pending' ? 'Only pending applications can be deleted' : 'Delete'"
                                @click="deleteRecord(data.id)"
                            />
                        </div>
                    </template>
                </Column>
            </DataTable>

            <!-- Expanded rows -->
            <template v-for="record in records" :key="`exp-${record.id}`">
                <Transition name="expand">
                    <div v-if="expandedRows[record.id]" class="border-t border-dashed border-gray-200 px-6 py-4 bg-gray-50">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">

                            <!-- Checklist -->
                            <div>
                                <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Checklist (Step 1)</p>
                                <div class="space-y-1.5">
                                    <div
                                        v-for="item in [
                                            { dbKey: 'checklist_sea_duty_records', label: 'Updated Sea Duty Records' },
                                            { dbKey: 'checklist_min_residency',    label: 'Min. 6 Months Residency' },
                                            { dbKey: 'checklist_steaming_hours',   label: 'Min. 300 Hours Steaming' },
                                        ]"
                                        :key="item.dbKey"
                                        class="flex items-center gap-2"
                                    >
                                        <i class="pi text-sm" :class="record[item.dbKey] ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-400'" />
                                        <span class="text-sm text-gray-700 flex-1">{{ item.label }}</span>
                                        <Tag :severity="record[item.dbKey] ? 'success' : 'danger'" :value="record[item.dbKey] ? 'Yes' : 'No'" class="text-xs" />
                                    </div>
                                </div>
                            </div>

                            <!-- Documents -->
                            <div class="space-y-3">
                                <div>
                                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Submitted Documents</p>
                                    <div class="space-y-1">
                                        <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                                            <span class="text-gray-500">Sea Duty Records</span>
                                            <span class="font-medium text-gray-700">{{ record.assignment_orders?.name ?? '—' }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                                            <span class="text-gray-500">Request Letter</span>
                                            <span class="font-medium text-gray-700">{{ record.performance_eval?.name ?? '—' }}</span>
                                        </div>
                                        <div class="flex justify-between text-xs py-1">
                                            <span class="text-gray-500">Certification</span>
                                            <span class="font-medium text-gray-700">{{ record.certification?.name ?? '— none —' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div v-if="record.remarks" class="rounded-lg border border-gray-200 bg-white px-3 py-2">
                                    <p class="text-xs text-gray-400 font-medium mb-0.5">Remarks</p>
                                    <p class="text-sm text-gray-700">{{ record.remarks }}</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </Transition>
            </template>

            <Paginator
                v-if="totalRecords > rowsPerPage"
                :rows="rowsPerPage"
                :total-records="totalRecords"
                :first="(currentPage - 1) * rowsPerPage"
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
                <div class="rounded-lg bg-gray-50 border px-4 py-3 flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                        <i class="pi pi-user text-indigo-600 text-sm" />
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ dialog.record.user?.name ?? '—' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ dialog.record.user?.email ?? '' }}</p>
                    </div>
                    <Tag :severity="statusSeverity(dialog.record.status)" :value="statusLabel(dialog.record.status)" class="flex-shrink-0 text-xs" />
                </div>

                <!-- Documents summary -->
                <div>
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-2">Submitted Documents</p>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">Sea Duty Records</span>
                        <span class="font-medium text-gray-700">{{ dialog.record.assignment_orders?.name ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between text-xs py-1 border-b border-gray-100">
                        <span class="text-gray-500">Request Letter</span>
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
                        Board Date &amp; Time <span class="text-red-400">*</span>
                    </label>
                    <input
                        v-model="dialog.scheduledAt"
                        type="datetime-local"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                    />
                    <p class="text-xs text-blue-500">The applicant will see this date on their tracking view.</p>
                </div>

                <!-- Remarks -->
                <div class="space-y-1.5">
                    <label class="text-sm font-medium text-gray-700">Remarks</label>
                    <Textarea
                        v-model="dialog.remarks"
                        rows="3"
                        class="w-full"
                        :placeholder="dialog.status === 'rejected'
                            ? 'State reason for rejection…'
                            : dialog.status === 'scheduled'
                            ? 'Notes for the applicant about the board date…'
                            : 'Add remarks or instructions for the applicant…'"
                        auto-resize
                    />
                    <p v-if="dialog.status === 'rejected'" class="text-xs text-red-500 flex items-center gap-1">
                        <i class="pi pi-exclamation-triangle" />
                        Recommended: provide a reason so the applicant can address the deficiency.
                    </p>
                </div>

                <!-- Status change preview -->
                <div class="rounded-lg border border-dashed border-gray-200 bg-gray-50 px-4 py-3 flex items-center justify-between gap-3">
                    <div class="flex items-center gap-2">
                        <Tag :severity="statusSeverity(dialog.record.status)" :value="statusLabel(dialog.record.status)" class="text-xs" />
                        <i class="pi pi-arrow-right text-gray-400 text-xs" />
                        <Tag :severity="statusSeverity(dialog.status)" :value="statusLabel(dialog.status)" class="text-xs" />
                    </div>
                    <span class="text-xs text-gray-400">Status change preview</span>
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
                    :severity="dialog.status === 'rejected' ? 'danger' : ['approved','completed'].includes(dialog.status) ? 'success' : 'info'"
                    :loading="submitting"
                    :disabled="!dialog.status"
                    @click="submitAction"
                />
            </template>
        </Dialog>

    </div>
    </AppLayout>
</template>

<style scoped>
.expand-enter-active, .expand-leave-active { transition: all 0.25s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to       { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from       { opacity: 1; max-height: 600px; }
</style>