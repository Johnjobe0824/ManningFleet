<script setup>
import { ref, computed, onMounted } from "vue";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import { useToast } from "primevue/usetoast";
import axios from "axios";

// ── Props passed from JuniorVesselAssignmentController@index ──
const props = defineProps({
    title:                   { type: String,  default: "Junior Vessel Assignment" },
    user:                    { type: Object,  required: true },
    juniorVesselAssignments: { type: Object,  default: () => ({ data: [], total: 0, current_page: 1 }) },

    // Step 1a — documents scoped to user: [{ id, user_id, name }]
    documents:      { type: Array,   default: () => [] },

    // Step 1b — certifications scoped to user: [{ id, user_id, name }]
    certifications: { type: Array,   default: () => [] },

    // Step 2 — global: [{ id, name }]
    vessels:        { type: Array,   default: () => [] },
    positions:      { type: Array,   default: () => [] },
});

const toast = useToast();

// ── Steps definition ──────────────────────────────────────────
const STEPS = [
    { step: 1, label: "Primary Documents", type: "select" },
    { step: 2, label: "Vessel & Position",  type: "vessel" },
    { step: 3, label: "Review & Submit",    type: "review" },
];

const currentStep = ref(0);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep  = computed(() => currentStep.value === STEPS.length - 1);
const activeStep  = computed(() => STEPS[currentStep.value]);

// ── Step 1 selections ─────────────────────────────────────────
const selected = ref({
    carry_morning_report: null,  // ChipsReport  { id, user_id, name }
    certificate_mso_dco:  null,  // Certification { id, user_id, name }
    certificate_jsolc:    null,  // Certification { id, user_id, name }
});

const step1Fields = [
    {
        key:     "carry_morning_report",
        label:   "Carry Morning Report",
        options: computed(() => props.documents),
        icon:    "pi pi-file",
    },
    {
        key:     "certificate_mso_dco",
        label:   "Certificate of Completion (MSO or DCO package course)",
        options: computed(() => props.certifications),
        icon:    "pi pi-verified",
    },
    {
        key:     "certificate_jsolc",
        label:   "Certificate of Graduation from JSOLC",
        options: computed(() => props.certifications),
        icon:    "pi pi-graduation-cap",
    },
];

// ── Step 2 selections ─────────────────────────────────────────
const selectedVessel   = ref(null);
const selectedPosition = ref(null);

// ── Validation ────────────────────────────────────────────────
const step1Valid = computed(() =>
    step1Fields.every((f) => selected.value[f.key] !== null)
);
const step2Valid = computed(() =>
    selectedVessel.value !== null && selectedPosition.value !== null
);
const currentStepValid = computed(() => {
    if (currentStep.value === 0) return step1Valid.value;
    if (currentStep.value === 1) return step2Valid.value;
    return true;
});

// ── Navigation ────────────────────────────────────────────────
const nextStep = () => { if (!isLastStep.value) currentStep.value++; };
const prevStep = () => { if (!isFirstStep.value) currentStep.value--; };

// ── Submit ────────────────────────────────────────────────────
const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    try {
        const { data } = await axios.post(route("juniorvesselassignment.store"), {
            user_id:                  props.user.id,
            carry_morning_report_id:  selected.value.carry_morning_report?.id,
            certificate_mso_dco_id:   selected.value.certificate_mso_dco?.id,
            certificate_jsolc_id:     selected.value.certificate_jsolc?.id,
            vessel_id:                selectedVessel.value?.id,
            position_id:              selectedPosition.value?.id,
        });

        toast.add({
            severity: "success",
            summary:  "Submitted",
            detail:   data.message,
            life:     3000,
        });

        // Push new row into table and reset form
        tableRecords.value.unshift(data.assignment);
        totalRecords.value++;
        resetForm();
        currentStep.value = 0;
    } catch (err) {
        const msg = err.response?.data?.message ?? "Submission failed. Please try again.";
        toast.add({ severity: "error", summary: "Error", detail: msg, life: 4000 });
    } finally {
        submitting.value = false;
    }
};

const resetForm = () => {
    selected.value = { carry_morning_report: null, certificate_mso_dco: null, certificate_jsolc: null };
    selectedVessel.value   = null;
    selectedPosition.value = null;
};

// ── Search / DataTable ────────────────────────────────────────
const searchQuery   = ref("");
const searchTimeout = ref(null);
const tableRecords  = ref([]);
const loadingTable  = ref(false);
const totalRecords  = ref(0);
const currentPage   = ref(1);
const rowsPerPage   = ref(10);

// Load initial data from Inertia prop on mount
onMounted(() => {
    tableRecords.value = props.juniorVesselAssignments.data        ?? [];
    totalRecords.value = props.juniorVesselAssignments.total       ?? 0;
    currentPage.value  = props.juniorVesselAssignments.current_page ?? 1;
});

const hasSearched = computed(() => searchQuery.value.trim().length > 0);

const fetchAssignments = async (page = 1, search = "") => {
    loadingTable.value = true;
    try {
        const { data } = await axios.get(route("juniorvesselassignment.fetch"), {
            params: {
                page,
                search,
                per_page: rowsPerPage.value,
                user_id:  props.user.id,
            },
        });
        tableRecords.value = data.data;
        totalRecords.value = data.total;
        currentPage.value  = data.current_page;
    } catch {
        toast.add({ severity: "error", summary: "Fetch Failed", detail: "Could not load assignments.", life: 4000 });
    } finally {
        loadingTable.value = false;
    }
};

const onSearch = () => {
    clearTimeout(searchTimeout.value);
    if (!searchQuery.value.trim()) {
        // Reset back to the original prop data when search is cleared
        tableRecords.value = props.juniorVesselAssignments.data        ?? [];
        totalRecords.value = props.juniorVesselAssignments.total       ?? 0;
        currentPage.value  = props.juniorVesselAssignments.current_page ?? 1;
        return;
    }
    searchTimeout.value = setTimeout(() => fetchAssignments(1, searchQuery.value), 300);
};

const onPageChange = (event) => fetchAssignments(event.page + 1, searchQuery.value);

// ── Delete ────────────────────────────────────────────────────
const deleteAssignment = async (id) => {
    if (!confirm("Are you sure you want to delete this assignment?")) return;
    try {
        await axios.delete(route("juniorvesselassignment.destroy", id));
        tableRecords.value = tableRecords.value.filter((r) => r.id !== id);
        totalRecords.value--;
        toast.add({ severity: "info", summary: "Deleted", detail: "Assignment deleted.", life: 3000 });
    } catch {
        toast.add({ severity: "error", summary: "Delete Failed", detail: "Could not delete assignment.", life: 5000 });
    }
};

// ── Helpers ───────────────────────────────────────────────────
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
    <div class="space-y-6">

        <!-- ══ STEPPER FORM ══════════════════════════════════════ -->
        <div class="border rounded-xl overflow-hidden bg-white shadow-sm">

            <!-- Step indicators -->
            <div class="flex border-b bg-gray-50">
                <div
                    v-for="(step, idx) in STEPS"
                    :key="idx"
                    class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors"
                    :class="[
                        idx === currentStep ? 'bg-white border-b-2 border-blue-600 text-blue-600' : '',
                        idx < currentStep  ? 'text-green-600 cursor-pointer hover:bg-gray-100' : '',
                        idx > currentStep  ? 'text-gray-400 cursor-not-allowed' : '',
                    ]"
                    @click="idx < currentStep ? (currentStep = idx) : null"
                >
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                        :class="
                            idx === currentStep ? 'bg-blue-600 text-white' :
                            idx < currentStep   ? 'bg-green-500 text-white' :
                            'bg-gray-200 text-gray-500'
                        "
                    >
                        <i v-if="idx < currentStep" class="pi pi-check text-xs" />
                        <span v-else>{{ idx + 1 }}</span>
                    </span>
                    <span class="hidden sm:inline">{{ step.label }}</span>
                </div>
            </div>

            <!-- Step body -->
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-800 mb-1">{{ activeStep.label }}</h3>
                <p class="text-sm text-gray-500 mb-6">
                    <template v-if="activeStep.type === 'select'">
                        Select your existing documents for this application.
                    </template>
                    <template v-else-if="activeStep.type === 'vessel'">
                        Select the vessel and position you are applying for.
                    </template>
                    <template v-else>
                        Review your selections carefully before submitting.
                    </template>
                </p>

                <!-- ── STEP 1: Document selectors ── -->
                <div v-if="activeStep.type === 'select'" class="space-y-4">
                    <div
                        v-for="field in step1Fields"
                        :key="field.key"
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected[field.key]
                            ? 'border-green-300 bg-green-50'
                            : 'border-gray-200 bg-gray-50'"
                    >
                        <!-- Label row -->
                        <div class="flex items-center gap-2 mb-2">
                            <i :class="[field.icon, 'text-gray-500 text-sm']" />
                            <p class="text-sm font-medium text-gray-700">{{ field.label }}</p>
                        </div>

                        <!-- Dropdown -->
                        <Select
                            v-model="selected[field.key]"
                            :options="field.options.value"
                            option-label="name"
                            placeholder="— select a record —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select a record —</span>
                            </template>
                            <template #option="{ option }">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-file text-gray-400 text-sm" />
                                    <span class="text-sm">{{ option.name }}</span>
                                </div>
                            </template>
                            <template #empty>
                                <div class="text-center py-3 text-gray-400 text-sm">
                                    <i class="pi pi-inbox block text-2xl mb-1 opacity-40" />
                                    No records found for your account.
                                </div>
                            </template>
                        </Select>

                        <!-- Selected name badge -->
                        <div v-if="selected[field.key]" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected[field.key].name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── STEP 2: Vessel & Position ── -->
                <div v-else-if="activeStep.type === 'vessel'" class="space-y-4">

                    <!-- Vessel -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="selectedVessel ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <i class="pi pi-send text-gray-500 text-sm" />
                            <p class="text-sm font-medium text-gray-700">Vessel</p>
                        </div>
                        <Select
                            v-model="selectedVessel"
                            :options="vessels"
                            option-label="name"
                            placeholder="— select a vessel —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select a vessel —</span>
                            </template>
                            <template #option="{ option }">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-send text-gray-400 text-sm" />
                                    <span class="text-sm">{{ option.name }}</span>
                                </div>
                            </template>
                        </Select>
                        <div v-if="selectedVessel" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selectedVessel.name }}</span>
                        </div>
                    </div>

                    <!-- Position -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="selectedPosition ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-center gap-2 mb-2">
                            <i class="pi pi-id-card text-gray-500 text-sm" />
                            <p class="text-sm font-medium text-gray-700">Position</p>
                        </div>
                        <Select
                            v-model="selectedPosition"
                            :options="positions"
                            option-label="name"
                            placeholder="— select a position —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select a position —</span>
                            </template>
                            <template #option="{ option }">
                                <div class="flex items-center gap-2">
                                    <i class="pi pi-id-card text-gray-400 text-sm" />
                                    <span class="text-sm">{{ option.name }}</span>
                                </div>
                            </template>
                        </Select>
                        <div v-if="selectedPosition" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selectedPosition.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── STEP 3: Review ── -->
                <div v-else class="space-y-5">

                    <!-- Primary Documents review -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Primary Documents
                        </h4>
                        <div
                            v-for="field in step1Fields"
                            :key="field.key"
                            class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <i
                                    class="pi text-sm flex-shrink-0"
                                    :class="selected[field.key]
                                        ? 'pi-check-circle text-green-500'
                                        : 'pi-times-circle text-red-400'"
                                />
                                <span class="text-sm text-gray-700 truncate">{{ field.label }}</span>
                            </div>
                            <span
                                class="text-xs font-medium ml-4 flex-shrink-0"
                                :class="selected[field.key] ? 'text-gray-700' : 'text-red-400 italic'"
                            >
                                {{ selected[field.key]?.name ?? '— not selected —' }}
                            </span>
                        </div>
                    </div>

                    <!-- Vessel & Position review -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Vessel & Position
                        </h4>

                        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-send text-sm"
                                   :class="selectedVessel ? 'text-green-500' : 'text-red-400'" />
                                <span class="text-sm text-gray-700">Vessel</span>
                            </div>
                            <span
                                class="text-xs font-medium"
                                :class="selectedVessel ? 'text-gray-700' : 'text-red-400 italic'"
                            >
                                {{ selectedVessel?.name ?? '— not selected —' }}
                            </span>
                        </div>

                        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-id-card text-sm"
                                   :class="selectedPosition ? 'text-green-500' : 'text-red-400'" />
                                <span class="text-sm text-gray-700">Position</span>
                            </div>
                            <span
                                class="text-xs font-medium"
                                :class="selectedPosition ? 'text-gray-700' : 'text-red-400 italic'"
                            >
                                {{ selectedPosition?.name ?? '— not selected —' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Step navigation -->
            <div class="flex justify-between items-center px-6 py-4 border-t bg-gray-50">
                <Button
                    label="Back"
                    icon="pi pi-chevron-left"
                    severity="secondary"
                    outlined
                    :disabled="isFirstStep"
                    @click="prevStep"
                />
                <Button
                    v-if="!isLastStep"
                    label="Next"
                    icon="pi pi-chevron-right"
                    icon-pos="right"
                    :disabled="!currentStepValid"
                    @click="nextStep"
                />
                <Button
                    v-else
                    label="Submit Application"
                    icon="pi pi-send"
                    icon-pos="right"
                    severity="success"
                    :loading="submitting"
                    @click="submit"
                />
            </div>
        </div>

        <!-- ══ ASSIGNMENTS TABLE ═════════════════════════════════ -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <span class="font-semibold text-lg text-gray-800">My Assignments</span>
                <IconField>
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText
                        v-model="searchQuery"
                        @input="onSearch"
                        placeholder="Search by vessel, position or status…"
                    />
                </IconField>
            </div>

            <!-- DataTable — always visible, search filters in place -->
            <DataTable
                :value="tableRecords"
                :loading="loadingTable"
                lazy
                paginator
                :rows="rowsPerPage"
                :total-records="totalRecords"
                :first="(currentPage - 1) * rowsPerPage"
                :rows-per-page-options="[5, 10, 25]"
                table-style="min-width: 48rem"
                striped-rows
                @page="onPageChange"
            >
                <template #empty>
                    <div class="text-center py-6 text-gray-400">No assignments found.</div>
                </template>
                <template #loading>
                    <div class="text-center py-6 text-gray-400">Loading…</div>
                </template>

                <!-- # -->
                <Column header="#" style="width: 3rem">
                    <template #body="{ index }">
                        {{ (currentPage - 1) * rowsPerPage + index + 1 }}
                    </template>
                </Column>

                <!-- Carry Morning Report -->
                <Column header="Carry Morning Report">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.carry_morning_report?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- MSO / DCO Certificate -->
                <Column header="MSO/DCO Certificate">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.certificate_mso_dco?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- JSOLC Certificate -->
                <Column header="JSOLC Certificate">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.certificate_jsolc?.name ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Vessel -->
                <Column field="vessel.name" header="Vessel" sortable />

                <!-- Position -->
                <Column field="position.name" header="Position" sortable />

                <!-- Status -->
                <Column field="status" header="Status" sortable>
                    <template #body="{ data }">
                        <Tag :severity="statusSeverity(data.status)" :value="data.status" />
                    </template>
                </Column>

                <!-- Remarks -->
                <Column header="Remarks">
                    <template #body="{ data }">
                        <span class="text-sm text-gray-500">{{ data.remarks ?? '—' }}</span>
                    </template>
                </Column>

                <!-- Actions -->
                <Column header="Actions" style="width: 7rem">
                    <template #body="{ data }">
                        <Button
                            v-if="can(['delete juniorvesselassignment'])"
                            icon="pi pi-trash"
                            label="Delete"
                            size="small"
                            severity="danger"
                            text
                            @click="deleteAssignment(data.id)"
                        />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>