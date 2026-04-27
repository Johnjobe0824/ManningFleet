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
   PROPS (READ-ONLY)
============================================================ */
const props = defineProps({
    title: { type: String, default: "Junior Higher Billet" },
    user: { type: Object, required: true },
    juniorHigherBillets: {
        type: Object,
        default: () => ({ data: [], total: 0, current_page: 1 }),
    },
    seaDutyRecords:       { type: Array, default: () => [] },
    shipsOrders:          { type: Array, default: () => [] },
    endorsements:         { type: Array, default: () => [] },
    dutyPerformanceCerts: { type: Array, default: () => [] },
    jsolcCerts:           { type: Array, default: () => [] },
    watchstanderCerts:    { type: Array, default: () => [] },
});

const toast = useToast();

/* ============================================================
   LOCAL REACTIVE STATE
============================================================ */
const seaDutyRecords        = ref([]);
const shipsOrders           = ref([]);
const endorsements          = ref([]);
const dutyPerformanceCerts  = ref([]);
const jsolcCerts            = ref([]);
const watchstanderCerts     = ref([]);

const tableRecords  = ref([]);
const totalRecords  = ref(0);
const currentPage   = ref(1);
const rowsPerPage   = ref(10);
const loadingTable  = ref(false);
const loadingIndex  = ref(false);

/* ============================================================
   FETCH INDEX DATA
   Calls juniorHigherBillets.index with X-Inertia header stripped
   so Laravel returns JSON instead of an Inertia page.
============================================================ */
const fetchIndexData = async () => {
    loadingIndex.value = true;

    try {
        const { data } = await axios.get(route("juniorHigherBillets.index"), {
            headers: { Accept: "application/json" },
        });

        seaDutyRecords.value        = data.seaDutyRecords       ?? [];
        shipsOrders.value           = data.shipsOrders          ?? [];
        endorsements.value          = data.endorsements         ?? [];
        dutyPerformanceCerts.value  = data.dutyPerformanceCerts ?? [];
        jsolcCerts.value            = data.jsolcCerts           ?? [];
        watchstanderCerts.value     = data.watchstanderCerts    ?? [];

    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Load Failed",
            detail: "Unable to load billet data.",
            life: 4000,
        });
    } finally {
        loadingIndex.value = false;
    }
};

/* ============================================================
   INITIALIZE (SSR fallback OR API fetch)
============================================================ */
onMounted(() => {
    fetchAssignments(currentPage.value, ""); // initial table fetch

    if (props.juniorHigherBillets?.data?.length) {
        totalRecords.value = props.juniorHigherBillets.total;
        currentPage.value  = props.juniorHigherBillets.current_page;

        seaDutyRecords.value        = props.seaDutyRecords;
        shipsOrders.value           = props.shipsOrders;
        endorsements.value          = props.endorsements;
        dutyPerformanceCerts.value  = props.dutyPerformanceCerts;
        jsolcCerts.value            = props.jsolcCerts;
        watchstanderCerts.value     = props.watchstanderCerts;
    } else {
        fetchIndexData();
    }
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

/* ============================================================
   FORM STATE
============================================================ */
const selected = ref({
    sea_duty_record:       null,
    ships_order:           null,
    endorsement:           null,
    duty_performance_cert: null,
    jsolc_cert:            null,
    watchstander_cert:     null, // optional
});

/* ── STEP 1 fields ── */
const step1Fields = [
    {
        key:      "sea_duty_record",
        label:    "Sea Duty Record",
        hint:     "Minimum of 18 months residency required.",
        options:  seaDutyRecords,
        icon:     "pi pi-book",
        required: true,
    },
    {
        key:      "ships_order",
        label:    "Ships Order as Deck Officer 1 or Engineering Officer 1",
        hint:     null,
        options:  shipsOrders,
        icon:     "pi pi-file-edit",
        required: true,
    },
    {
        key:      "endorsement",
        label:    "Endorsement for Promotion from Commanding Officer",
        hint:     null,
        options:  endorsements,
        icon:     "pi pi-star",
        required: true,
    },
];

/* ── STEP 2 fields ── */
const step2Fields = [
    {
        key:      "duty_performance_cert",
        label:    "Certification of Actual Duty Performance from Commanding Officer",
        hint:     null,
        options:  dutyPerformanceCerts,
        icon:     "pi pi-verified",
        required: true,
    },
    {
        key:      "jsolc_cert",
        label:    "Certificate of Graduation from JSOLC",
        hint:     "Junior Subaltern Officers Leadership Course.",
        options:  jsolcCerts,
        icon:     "pi pi-graduation-cap",
        required: true,
    },
    {
        key:      "watchstander_cert",
        label:    "Watch Stander Certificate",
        hint:     "Optional — attach if available.",
        options:  watchstanderCerts,
        icon:     "pi pi-eye",
        required: false,
    },
];

/* ============================================================
   VALIDATION
============================================================ */
const step1Valid = computed(() =>
    step1Fields.filter(f => f.required).every(f => selected.value[f.key] !== null)
);

const step2Valid = computed(() =>
    step2Fields.filter(f => f.required).every(f => selected.value[f.key] !== null)
);

const currentStepValid = computed(() => {
    if (currentStep.value === 0) return step1Valid.value;
    if (currentStep.value === 1) return step2Valid.value;
    return true;
});

/* ============================================================
   NAVIGATION
============================================================ */
const nextStep = () => { if (!isLastStep.value) currentStep.value++; };
const prevStep = () => { if (!isFirstStep.value) currentStep.value--; };

/* ============================================================
   SUBMIT
============================================================ */
const submitting = ref(false);

const resetForm = () => {
    selected.value = {
        sea_duty_record:       null,
        ships_order:           null,
        endorsement:           null,
        duty_performance_cert: null,
        jsolc_cert:            null,
        watchstander_cert:     null,
    };
};

const submit = async () => {
    submitting.value = true;
    try {
        await axios.post(route("juniorHigherBillets.store"), {
            user_id:                  props.user.id,
            sea_duty_record_id:       selected.value.sea_duty_record?.id       ?? null,
            ships_order_id:           selected.value.ships_order?.id           ?? null,
            endorsement_id:           selected.value.endorsement?.id           ?? null,
            duty_performance_cert_id: selected.value.duty_performance_cert?.id ?? null,
            jsolc_cert_id:            selected.value.jsolc_cert?.id            ?? null,
            watchstander_cert_id:     selected.value.watchstander_cert?.id     ?? null,
        });

        toast.add({ severity: "success", summary: "Submitted", detail: "Billet application submitted." });
        resetForm();
        currentStep.value = 0;
        await fetchIndexData();
        await fetchAssignments(1, searchQuery.value);
    } catch {
        toast.add({ severity: "error", summary: "Error", detail: "Submission failed." });
    } finally {
        submitting.value = false;
    }
};

/* ============================================================
   SEARCH + PAGINATION
============================================================ */
const searchQuery   = ref("");
const searchTimeout = ref(null);

const fetchAssignments = async (page = 1, search = "") => {
    loadingTable.value = true;
    try {
        const { data } = await axios.get(route("juniorHigherBillets.fetch"), {
            params: { page, search, per_page: rowsPerPage.value, user_id: props.user.id },
        });
        tableRecords.value = data.data;
        totalRecords.value = data.total;
        currentPage.value  = data.current_page;
    } finally {
        loadingTable.value = false;
    }
};

const onSearch = () => {
    clearTimeout(searchTimeout.value);
    if (!searchQuery.value.trim()) { fetchAssignments(1, searchQuery.value); return; }
    searchTimeout.value = setTimeout(() => fetchAssignments(1, searchQuery.value), 300);
};

const onPageChange = (event) => fetchAssignments(event.page + 1, searchQuery.value);

/* ============================================================
   DELETE
============================================================ */
const deleteRecord = async (id) => {
    if (!confirm("Delete this billet application?")) return;

    await axios.delete(route("juniorHigherBillets.destroy", id));

    toast.add({ severity: "info", summary: "Deleted", detail: "Billet application deleted." });

    await fetchIndexData();
    await fetchAssignments(currentPage.value, searchQuery.value);
};

/* ============================================================
   HELPERS
============================================================ */
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
                        idx < currentStep   ? 'text-green-600 cursor-pointer hover:bg-gray-100'  : '',
                        idx > currentStep   ? 'text-gray-400 cursor-not-allowed'                 : '',
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
                        Select your sea duty record, ships order, and endorsement documents.
                    </template>
                    <template v-else-if="activeStep.type === 'certs'">
                        Attach your duty performance certification, JSOLC graduation certificate, and optional watch stander certificate.
                    </template>
                    <template v-else>
                        Review all selections carefully before submitting your application.
                    </template>
                </p>

                <!-- ── STEP 1: Service Documents ── -->
                <div v-if="activeStep.type === 'service'" class="space-y-4">
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
                        :class="[
                            selected[field.key]
                                ? 'border-green-300 bg-green-50'
                                : field.required ? 'border-gray-200 bg-gray-50' : 'border-dashed border-gray-300 bg-gray-50/60',
                        ]"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i :class="[field.icon, 'text-gray-500 text-sm mt-0.5']" />
                            <div>
                                <p class="text-sm font-medium text-gray-700 leading-snug">
                                    {{ field.label }}
                                    <span v-if="!field.required" class="ml-1 text-xs font-normal text-gray-400">(Optional)</span>
                                </p>
                                <p v-if="field.hint" class="text-xs text-blue-500 mt-0.5">{{ field.hint }}</p>
                            </div>
                        </div>
                        <Select
                            v-model="selected[field.key]"
                            :options="field.options.value"
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
                                    No certificates found for your account.
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
                                    :class="
                                        selected[field.key]
                                            ? 'pi-check-circle text-green-500'
                                            : field.required ? 'pi-times-circle text-red-400' : 'pi-minus-circle text-gray-300'
                                    "
                                />
                                <span class="text-sm text-gray-700 truncate">
                                    {{ field.label }}
                                    <span v-if="!field.required" class="text-gray-400 text-xs ml-1">(optional)</span>
                                </span>
                            </div>
                            <span
                                class="text-xs font-medium ml-4 flex-shrink-0"
                                :class="
                                    selected[field.key] ? 'text-gray-700' :
                                    field.required ? 'text-red-400 italic' : 'text-gray-400 italic'
                                "
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
                <span class="font-semibold text-lg text-gray-800">My Billet Applications</span>
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
                    <div class="text-center py-6 text-gray-400">No billet applications found.</div>
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
                <Column header="Endorsement">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.endorsement?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Duty Performance Cert.">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.duty_performance_cert?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="JSOLC Certificate">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.jsolc_cert?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Watch Stander Cert.">
                    <template #body="{ data }">
                        <span class="text-sm text-gray-400 italic">{{ data.watchstander_cert?.name ?? '—' }}</span>
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
                            v-if="can(['delete junior_higher_billet'])"
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