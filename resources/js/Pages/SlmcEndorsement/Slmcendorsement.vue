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

/* ============================================================
   PROPS
============================================================ */
const props = defineProps({
    title: { type: String, default: "SLMC Endorsement" },
    user:  { type: Object, required: true },
});

const toast = useToast();

/* ============================================================
   DROPDOWN DATA
============================================================ */
const seaDutyRecords    = ref([]);
const shipsOrders       = ref([]);
const graduationCerts   = ref([]);
const watchstanderCerts = ref([]);
const loadingIndex      = ref(false);

const fetchIndexData = async () => {
    loadingIndex.value = true;
    try {
        const { data } = await axios.get(route("slmcEndorsements.index"), {
            headers: { Accept: "application/json" },
        });
        seaDutyRecords.value    = data.seaDutyRecords    ?? [];
        shipsOrders.value       = data.shipsOrders       ?? [];
        graduationCerts.value   = data.graduationCerts   ?? [];
        watchstanderCerts.value = data.watchstanderCerts ?? [];
    } catch {
        toast.add({
            severity: "error",
            summary:  "Load Failed",
            detail:   "Unable to load SLMC Endorsement data.",
            life:     4000,
        });
    } finally {
        loadingIndex.value = false;
    }
};

/* ============================================================
   TABLE DATA
============================================================ */
const tableRecords  = ref([]);
const totalRecords  = ref(0);
const currentPage   = ref(1);
const rowsPerPage   = ref(10);
const loadingTable  = ref(false);
const searchQuery   = ref("");
const searchTimeout = ref(null);

const fetchApplications = async (page = 1, search = "") => {
    loadingTable.value = true;
    try {
        const { data } = await axios.get(route("slmcEndorsements.fetch"), {
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
        toast.add({
            severity: "error",
            summary:  "Load Failed",
            detail:   "Unable to load applications.",
            life:     4000,
        });
    } finally {
        loadingTable.value = false;
    }
};

const onSearch = () => {
    clearTimeout(searchTimeout.value);
    searchTimeout.value = setTimeout(() => fetchApplications(1, searchQuery.value), 300);
};

const onPageChange = (event) => fetchApplications(event.page + 1, searchQuery.value);

/* ============================================================
   INIT
============================================================ */
onMounted(async () => {
    await fetchIndexData();
    await fetchApplications(1, "");
});

/* ============================================================
   STEPPER
============================================================ */
const STEPS = [
    { step: 1, label: "Service Documents", type: "service" },
    { step: 2, label: "Certificates",      type: "certs"   },
    { step: 3, label: "Review & Submit",   type: "review"  },
];

const currentStep = ref(0);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep  = computed(() => currentStep.value === STEPS.length - 1);
const activeStep  = computed(() => STEPS[currentStep.value]);

const nextStep = () => { if (!isLastStep.value)  currentStep.value++; };
const prevStep = () => { if (!isFirstStep.value) currentStep.value--; };

/* ============================================================
   FORM
============================================================ */
const selected = ref({
    sea_duty_record:   null,
    ships_order:       null,
    graduation_cert:   null,
    watchstander_cert: null,
});

const resetForm = () => {
    selected.value = {
        sea_duty_record:   null,
        ships_order:       null,
        graduation_cert:   null,
        watchstander_cert: null,
    };
};

// Step 1 fields — options bound to reactive refs
const step1Fields = computed(() => [
    {
        key:      "sea_duty_record",
        label:    "Sea Duty Record",
        hint:     "Minimum of 36 months (3 years) residency required.",
        options:  seaDutyRecords.value,
        icon:     "pi pi-book",
        required: true,
    },
    {
        key:      "ships_order",
        label:    "Relevant Ships Orders",
        hint:     null,
        options:  shipsOrders.value,
        icon:     "pi pi-file-edit",
        required: true,
    },
]);

// Step 2 fields — options bound to reactive refs
const step2Fields = computed(() => [
    {
        key:      "graduation_cert",
        label:    "Graduation Certificate from Applicable Courses",
        hint:     "Certificate of graduation from all applicable courses required for SLMC Endorsement.",
        options:  graduationCerts.value,
        icon:     "pi pi-graduation-cap",
        required: true,
    },
    {
        key:      "watchstander_cert",
        label:    "Watch Stander Certificate",
        hint:     "Mandatory for SLMC Endorsement.",
        options:  watchstanderCerts.value,
        icon:     "pi pi-eye",
        required: true,
    },
]);

/* ============================================================
   VALIDATION
============================================================ */
const step1Valid = computed(() =>
    step1Fields.value.filter(f => f.required).every(f => selected.value[f.key] !== null)
);

const step2Valid = computed(() =>
    step2Fields.value.filter(f => f.required).every(f => selected.value[f.key] !== null)
);

const currentStepValid = computed(() => {
    if (currentStep.value === 0) return step1Valid.value;
    if (currentStep.value === 1) return step2Valid.value;
    return true;
});

/* ============================================================
   SUBMIT
============================================================ */
const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    try {
        await axios.post(route("slmcEndorsements.store"), {
            user_id:              props.user.id,
            sea_duty_record_id:   selected.value.sea_duty_record?.id   ?? null,
            ships_order_id:       selected.value.ships_order?.id       ?? null,
            graduation_cert_id:   selected.value.graduation_cert?.id   ?? null,
            watchstander_cert_id: selected.value.watchstander_cert?.id ?? null,
        });

        toast.add({
            severity: "success",
            summary:  "Submitted",
            detail:   "SLMC Endorsement application submitted.",
            life:     4000,
        });

        resetForm();
        currentStep.value = 0;
        await fetchIndexData();
        await fetchApplications(1, searchQuery.value);

    } catch (err) {
        const detail = err.response?.data?.message ?? "Submission failed.";
        toast.add({ severity: "error", summary: "Error", detail, life: 5000 });
    } finally {
        submitting.value = false;
    }
};

/* ============================================================
   DELETE
============================================================ */
/* ============================================================
   DELETE
============================================================ */
const deleteRecord = async (id) => {
    if (!confirm("Delete this SLMC Endorsement application?")) return;

    await axios.delete(route("slmcEndorsements.destroy", id));

    toast.add({
        severity: "info",
        summary:  "Deleted",
        detail:   "Application deleted.",
    });

    await fetchIndexData();
    await fetchApplications(currentPage.value, searchQuery.value);
};

/* ============================================================
   HELPERS
============================================================ */
const statusSeverity = (status) => {
    const map = { pending: "warn", approved: "success", rejected: "danger" };
    return map[status] ?? "secondary";
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
                        idx < currentStep   ? 'text-green-600 cursor-pointer hover:bg-gray-100'   : '',
                        idx > currentStep   ? 'text-gray-400 cursor-not-allowed'                  : '',
                    ]"
                    @click="idx < currentStep ? (currentStep = idx) : null"
                >
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                        :class="
                            idx === currentStep ? 'bg-blue-600 text-white'  :
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
                    <template v-if="activeStep.type === 'service'">
                        Select your sea duty record (minimum 3 years) and relevant ships orders
                        for SLMC Endorsement.
                    </template>
                    <template v-else-if="activeStep.type === 'certs'">
                        Attach your graduation certificate from applicable courses and your mandatory
                        Watch Stander Certificate.
                    </template>
                    <template v-else>
                        Review all selections carefully before submitting your SLMC Endorsement application.
                    </template>
                </p>

                <!-- Loading state -->
                <div v-if="loadingIndex" class="flex items-center justify-center py-8 text-gray-400">
                    <i class="pi pi-spin pi-spinner text-2xl mr-2" />
                    <span class="text-sm">Loading your documents…</span>
                </div>

                <!-- ── STEP 1: Service Documents ── -->
                <div v-else-if="activeStep.type === 'service'" class="space-y-4">
                    <div
                        v-for="field in step1Fields"
                        :key="field.key"
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected[field.key] ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i :class="[field.icon, 'text-gray-500 text-sm mt-0.5']" />
                            <div>
                                <p class="text-sm font-medium text-gray-700 leading-snug">{{ field.label }}</p>
                                <p v-if="field.hint" class="text-xs text-amber-600 mt-0.5">{{ field.hint }}</p>
                            </div>
                        </div>
                        <Select
                            v-model="selected[field.key]"
                            :options="field.options"
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
                                    No approved records found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected[field.key]" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected[field.key].name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── STEP 2: Certificates ── -->
                <div v-else-if="activeStep.type === 'certs'" class="space-y-4">
                    <div
                        v-for="field in step2Fields"
                        :key="field.key"
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected[field.key] ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i :class="[field.icon, 'text-gray-500 text-sm mt-0.5']" />
                            <div>
                                <p class="text-sm font-medium text-gray-700 leading-snug">{{ field.label }}</p>
                                <p v-if="field.hint" class="text-xs text-blue-500 mt-0.5">{{ field.hint }}</p>
                            </div>
                        </div>
                        <Select
                            v-model="selected[field.key]"
                            :options="field.options"
                            option-label="name"
                            placeholder="— select a certificate —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select a certificate —</span>
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
                                    No approved certificates found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected[field.key]" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected[field.key].name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── STEP 3: Review ── -->
                <div v-else class="space-y-5">
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Service Documents
                        </h4>
                        <div
                            v-for="field in step1Fields"
                            :key="field.key"
                            class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <i
                                    class="pi text-sm flex-shrink-0"
                                    :class="selected[field.key] ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-400'"
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

                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Certificates
                        </h4>
                        <div
                            v-for="field in step2Fields"
                            :key="field.key"
                            class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <i
                                    class="pi text-sm flex-shrink-0"
                                    :class="selected[field.key] ? 'pi-check-circle text-green-500' : 'pi-times-circle text-red-400'"
                                />
                                <span class="text-sm text-gray-700 truncate">{{ field.label }}</span>
                            </div>
                            <span
                                class="text-xs font-medium ml-4 flex-shrink-0"
                                :class="selected[field.key] ? 'text-gray-700' : 'text-red-400 italic'"
                            >
                                {{ selected[field.key]?.name ?? '— not attached —' }}
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

        <!-- ══ RECORDS TABLE ═════════════════════════════════════ -->
        <div class="card">
            <div class="flex items-center justify-between mb-4">
                <span class="font-semibold text-lg text-gray-800">My SLMC Endorsement Applications</span>
                <IconField>
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText
                        v-model="searchQuery"
                        @input="onSearch"
                        placeholder="Search by status or document…"
                    />
                </IconField>
            </div>

            <DataTable
                :value="tableRecords"
                :loading="loadingTable"
                lazy
                table-style="min-width: 60rem"
                striped-rows
            >
                <template #empty>
                    <div class="text-center py-6 text-gray-400">
                        No SLMC Endorsement applications found.
                    </div>
                </template>
                <template #loading>
                    <div class="text-center py-6 text-gray-400">Loading…</div>
                </template>

                <Column header="Sea Duty Record">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.sea_duty_record?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Ships Order">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.ships_order?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Graduation Certificate">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.graduation_cert?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Watch Stander Cert.">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.watchstander_cert?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column field="status" header="Status" sortable>
                    <template #body="{ data }">
                        <Tag :severity="statusSeverity(data.status)" :value="data.status" />
                    </template>
                </Column>
                <Column header="Remarks">
                    <template #body="{ data }">
                        <span class="text-sm text-gray-500">{{ data.remarks ?? '—' }}</span>
                    </template>
                </Column>
               <Column header="Actions" style="width: 7rem">
                    <template #body="{ data }">
                        <Button
                            v-if="can(['delete slmc_endorsement']) "
                            icon="pi pi-trash"
                            label="Delete"
                            size="small"
                            severity="danger"
                            text
                            @click="deleteRecord(data.id)"
                        />
                    </template>
                </Column>
            </DataTable>

            <Paginator
                v-if="totalRecords > rowsPerPage"
                :rows="rowsPerPage"
                :total-records="totalRecords"
                :first="(currentPage - 1) * rowsPerPage"
                @page="onPageChange"
                class="mt-4"
            />
        </div>
    </div>
</template>