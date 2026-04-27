<script setup>
import { ref, computed, onMounted } from "vue";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import DataTable from "primevue/datatable";
import Column from "primevue/column";
import Paginator from "primevue/paginator";
import { useToast } from "primevue/usetoast";
import { usePage } from "@inertiajs/vue3";
import axios from "axios";

/* ============================================================
   PROPS
============================================================ */
const props = defineProps({
    title: { type: String, default: "EXO Reassignment" },
    user:  { type: Object, required: true },
});

const toast = useToast();

/* ============================================================
   PERMISSIONS
============================================================ */
const page = usePage();
const can  = (permissions) =>
    permissions.some((p) => page.props.auth?.permissions?.includes(p));

/* ============================================================
   PHASES
============================================================ */
const PHASES = [
    {
        phase:       1,
        key:         "eligibility",
        label:       "Eligibility Check",
        icon:        "pi pi-shield",
        description: "Verify that you meet the minimum requirement of being assigned outside the fleet for more than 1 year and satisfy EXO criteria.",
        badge:       "Steps 1",
        badgeColor:  "warn",
    },
    {
        phase:       2,
        key:         "documents",
        label:       "Document Submission",
        icon:        "pi pi-file-arrow-up",
        description: "Upload your Reassignment Request, Previous Assignment Orders (must show >1 year duration), Performance Evaluations, and any additional certifications.",
        badge:       "Steps 2–3",
        badgeColor:  "info",
    },
    {
        phase:       3,
        key:         "deliberation",
        label:       "Board Deliberation",
        icon:        "pi pi-users",
        description: "Your application is queued for board review. The panel will evaluate your qualifications and deliberate on the EXO Billet reassignment.",
        badge:       "Steps 4–5",
        badgeColor:  "secondary",
    },
    {
        phase:       4,
        key:         "decision",
        label:       "Decision & Orders",
        icon:        "pi pi-verified",
        description: "The board issues its decision. If approved, reassignment orders are generated and your personnel records are updated.",
        badge:       "Steps 6–7",
        badgeColor:  "success",
    },
];

/* ============================================================
   DROPDOWN DATA
============================================================ */
const assignmentOrders = ref([]);
const performanceEvals = ref([]);
const certifications   = ref([]);
const loadingIndex     = ref(false);

const fetchIndexData = async () => {
    loadingIndex.value = true;
    try {
        const { data } = await axios.get(route("exoReassignments.index"), {
            headers: { Accept: "application/json" },
        });
        assignmentOrders.value = data.assignmentOrders ?? [];
        performanceEvals.value = data.performanceEvals ?? [];
        certifications.value   = data.certifications   ?? [];
    } catch {
        toast.add({
            severity: "error",
            summary:  "Load Failed",
            detail:   "Unable to load EXO Reassignment data.",
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
        const { data } = await axios.get(route("exoReassignments.fetch"), {
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
    searchTimeout.value = setTimeout(
        () => fetchApplications(1, searchQuery.value),
        300,
    );
};

const onPageChange = (event) =>
    fetchApplications(event.page + 1, searchQuery.value);

onMounted(async () => {
    await fetchIndexData();
    await fetchApplications(1, "");
});

/* ============================================================
   STEPPER STATE
============================================================ */
const currentStep = ref(0);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep  = computed(() => currentStep.value === PHASES.length - 1);
const activePhase = computed(() => PHASES[currentStep.value]);

const nextStep = () => { if (!isLastStep.value)  currentStep.value++; };
const prevStep = () => { if (!isFirstStep.value) currentStep.value--; };

/* ============================================================
   PHASE 1 — ELIGIBILITY
============================================================ */
const eligibility = ref({
    outsideFleetMoreThan1Year: null,
    meetsExoCriteria:          null,
});

const eligibilityOptions = [
    { label: "Yes", value: "yes" },
    { label: "No",  value: "no"  },
];

const eligibilityValid = computed(() =>
    eligibility.value.outsideFleetMoreThan1Year === "yes" &&
    eligibility.value.meetsExoCriteria === "yes",
);

/* ============================================================
   PHASE 2 — DOCUMENTS
============================================================ */
const selected = ref({
    assignment_orders: null,
    performance_eval:  null,
    certification:     null,
});

const phase2Valid = computed(() =>
    selected.value.assignment_orders !== null &&
    selected.value.performance_eval  !== null,
);

const resetForm = () => {
    eligibility.value = { outsideFleetMoreThan1Year: null, meetsExoCriteria: null };
    selected.value    = { assignment_orders: null, performance_eval: null, certification: null };
};

/* ============================================================
   VALIDATION — per step
============================================================ */
const currentStepValid = computed(() => {
    if (currentStep.value === 0) return eligibilityValid.value;
    if (currentStep.value === 1) return phase2Valid.value;
    return true;
});

/* ============================================================
   SUBMIT
============================================================ */
const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    try {
        await axios.post(route("exoReassignments.store"), {
            user_id:              props.user.id,
            assignment_orders_id: selected.value.assignment_orders?.id ?? null,
            performance_eval_id:  selected.value.performance_eval?.id  ?? null,
            certification_id:     selected.value.certification?.id     ?? null,
        });

        toast.add({
            severity: "success",
            summary:  "Application Submitted",
            detail:   "Your EXO Reassignment application has been submitted for board deliberation.",
            life:     5000,
        });

        resetForm();
        currentStep.value = 0;
        await fetchIndexData();
        await fetchApplications(1, searchQuery.value);
    } catch (err) {
        toast.add({
            severity: "error",
            summary:  "Submission Error",
            detail:   err.response?.data?.message ?? "Submission failed. Please try again.",
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
        await fetchApplications(currentPage.value, searchQuery.value);
    } catch {
        toast.add({ severity: "error", summary: "Delete Failed", detail: "Unable to delete application.", life: 4000 });
    }
};

/* ============================================================
   HELPERS
============================================================ */
const statusSeverity = (s) => ({
    pending:    "warn",
    for_review: "info",
    scheduled:  "info",
    approved:   "success",
    rejected:   "danger",
    completed:  "success",
}[s] ?? "secondary");

const statusLabel = (s) => ({
    pending:    "Pending",
    for_review: "For Review",
    scheduled:  "Scheduled",
    approved:   "Approved",
    rejected:   "Rejected",
    completed:  "Completed",
}[s] ?? s);

const formatDate = (val) =>
    val
        ? new Date(val).toLocaleDateString("en-PH", {
              year:   "numeric",
              month:  "short",
              day:    "numeric",
              hour:   "2-digit",
              minute: "2-digit",
          })
        : "—";
</script>

<template>
    <div class="space-y-6">

        <!-- ══ PAGE HEADER ══════════════════════════════════════ -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <div>
                <h2 class="text-xl font-bold text-gray-800">EXO Reassignment</h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    Apply for Executive Officer (EXO) Billet reassignment after returning from outside-fleet assignment.
                </p>
            </div>
        </div>

        <!-- ══ PHASE PROGRESS TRACKER ═══════════════════════════ -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div
                v-for="(phase, idx) in PHASES"
                :key="idx"
                class="rounded-xl border px-4 py-3 flex items-center gap-3 transition-all"
                :class="[
                    idx === currentStep ? 'border-blue-400 bg-blue-50 shadow-sm'   : '',
                    idx <  currentStep  ? 'border-green-300 bg-green-50'            : '',
                    idx >  currentStep  ? 'border-gray-200 bg-white text-gray-400'  : '',
                ]"
            >
                <div
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 text-sm font-bold"
                    :class="[
                        idx === currentStep ? 'bg-blue-600 text-white'    : '',
                        idx <  currentStep  ? 'bg-green-500 text-white'   : '',
                        idx >  currentStep  ? 'bg-gray-200 text-gray-500' : '',
                    ]"
                >
                    <i v-if="idx < currentStep" class="pi pi-check text-xs" />
                    <span v-else>{{ idx + 1 }}</span>
                </div>
                <div class="min-w-0">
                    <p
                        class="text-xs font-semibold truncate"
                        :class="
                            idx === currentStep ? 'text-blue-700'  :
                            idx <  currentStep  ? 'text-green-700' : 'text-gray-400'
                        "
                    >
                        {{ phase.label }}
                    </p>
                    <p
                        class="text-xs"
                        :class="
                            idx === currentStep ? 'text-blue-500'  :
                            idx <  currentStep  ? 'text-green-500' : 'text-gray-300'
                        "
                    >
                        {{ phase.badge }}
                    </p>
                </div>
            </div>
        </div>

        <!-- ══ PROCESS BANNER ════════════════════════════════════ -->
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 flex items-start gap-3">
            <i class="pi pi-info-circle text-blue-500 text-lg mt-0.5 flex-shrink-0" />
            <div class="text-sm text-blue-800 leading-relaxed">
                <span class="font-semibold">EXO Reassignment Process:</span>
                Verify Eligibility (&gt;1 year outside fleet) → Submit Reassignment Request &amp; Documents →
                Await Board Deliberation → Receive Decision &amp; Orders.
            </div>
        </div>

        <!-- ══ STEPPER FORM ══════════════════════════════════════ -->
        <div class="border rounded-xl overflow-hidden bg-white shadow-sm">

            <!-- Step tab indicators -->
            <div class="flex border-b bg-gray-50">
                <div
                    v-for="(phase, idx) in PHASES"
                    :key="idx"
                    class="flex-1 flex items-center justify-center gap-2 py-3 text-sm font-medium transition-colors cursor-pointer"
                    :class="[
                        idx === currentStep ? 'bg-white border-b-2 border-blue-600 text-blue-600' : '',
                        idx <  currentStep  ? 'text-green-600 hover:bg-gray-100'                  : '',
                        idx >  currentStep  ? 'text-gray-400 cursor-not-allowed'                  : '',
                    ]"
                    @click="idx < currentStep ? (currentStep = idx) : null"
                >
                    <span
                        class="w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                        :class="[
                            idx === currentStep ? 'bg-blue-600 text-white'    : '',
                            idx <  currentStep  ? 'bg-green-500 text-white'   : '',
                            idx >  currentStep  ? 'bg-gray-200 text-gray-500' : '',
                        ]"
                    >
                        <i v-if="idx < currentStep" class="pi pi-check text-xs" />
                        <span v-else>{{ idx + 1 }}</span>
                    </span>
                    <span class="hidden sm:inline">{{ phase.label }}</span>
                </div>
            </div>

            <!-- Step body -->
            <div class="p-6">
                <div class="flex items-center gap-2 mb-1">
                    <i :class="[activePhase.icon, 'text-blue-500 text-lg']" />
                    <h3 class="text-lg font-semibold text-gray-800">
                        Phase {{ activePhase.phase }}: {{ activePhase.label }}
                    </h3>
                    <Tag :severity="activePhase.badgeColor" :value="activePhase.badge" class="text-xs ml-1" />
                </div>
                <p class="text-sm text-gray-500 mb-6">{{ activePhase.description }}</p>

                <!-- Loading -->
                <div v-if="loadingIndex" class="flex items-center justify-center py-8 text-gray-400">
                    <i class="pi pi-spin pi-spinner text-2xl mr-2" />
                    <span class="text-sm">Loading data…</span>
                </div>

                <!-- ── PHASE 1: Eligibility Check ── -->
                <div v-else-if="activePhase.key === 'eligibility'" class="space-y-5">

                    <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-2 text-sm text-amber-800">
                        <i class="pi pi-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0" />
                        <span>
                            You must answer <strong>Yes</strong> to both questions to proceed.
                            Ineligible applicants will be blocked from submitting.
                        </span>
                    </div>

                    <!-- Question 1 -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="
                            eligibility.outsideFleetMoreThan1Year === 'yes' ? 'border-green-300 bg-green-50' :
                            eligibility.outsideFleetMoreThan1Year === 'no'  ? 'border-red-300 bg-red-50'     :
                            'border-gray-200 bg-gray-50'
                        "
                    >
                        <div class="flex items-start gap-2 mb-3">
                            <i class="pi pi-clock text-gray-500 text-sm mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Have you been assigned outside the fleet for more than <strong>1 year</strong>?
                                </p>
                                <p class="text-xs text-amber-600 mt-0.5">
                                    This is the primary eligibility requirement for EXO Reassignment.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button
                                v-for="opt in eligibilityOptions"
                                :key="opt.value"
                                class="flex-1 py-2 rounded-lg border text-sm font-medium transition-all"
                                :class="
                                    eligibility.outsideFleetMoreThan1Year === opt.value
                                        ? (opt.value === 'yes' ? 'bg-green-600 text-white border-green-600' : 'bg-red-500 text-white border-red-500')
                                        : 'border-gray-300 bg-white text-gray-700 hover:border-blue-400'
                                "
                                @click="eligibility.outsideFleetMoreThan1Year = opt.value"
                            >
                                <i v-if="opt.value === 'yes'" class="pi pi-check mr-1" />
                                <i v-else class="pi pi-times mr-1" />
                                {{ opt.label }}
                            </button>
                        </div>
                        <div
                            v-if="eligibility.outsideFleetMoreThan1Year === 'no'"
                            class="mt-3 text-xs text-red-600 font-medium flex items-center gap-1"
                        >
                            <i class="pi pi-ban" />
                            You do not meet the minimum duration requirement. You cannot proceed with this application.
                        </div>
                    </div>

                    <!-- Question 2 -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="
                            eligibility.meetsExoCriteria === 'yes' ? 'border-green-300 bg-green-50' :
                            eligibility.meetsExoCriteria === 'no'  ? 'border-red-300 bg-red-50'     :
                            'border-gray-200 bg-gray-50'
                        "
                    >
                        <div class="flex items-start gap-2 mb-3">
                            <i class="pi pi-shield text-gray-500 text-sm mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Do you meet all required EXO criteria?
                                </p>
                                <p class="text-xs text-blue-500 mt-0.5">
                                    Includes rank, qualifications, and performance standards required for EXO Billet eligibility.
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <button
                                v-for="opt in eligibilityOptions"
                                :key="opt.value"
                                class="flex-1 py-2 rounded-lg border text-sm font-medium transition-all"
                                :class="
                                    eligibility.meetsExoCriteria === opt.value
                                        ? (opt.value === 'yes' ? 'bg-green-600 text-white border-green-600' : 'bg-red-500 text-white border-red-500')
                                        : 'border-gray-300 bg-white text-gray-700 hover:border-blue-400'
                                "
                                @click="eligibility.meetsExoCriteria = opt.value"
                            >
                                <i v-if="opt.value === 'yes'" class="pi pi-check mr-1" />
                                <i v-else class="pi pi-times mr-1" />
                                {{ opt.label }}
                            </button>
                        </div>
                        <div
                            v-if="eligibility.meetsExoCriteria === 'no'"
                            class="mt-3 text-xs text-red-600 font-medium flex items-center gap-1"
                        >
                            <i class="pi pi-ban" />
                            You must meet all EXO criteria before submitting an application.
                        </div>
                    </div>

                    <!-- Eligibility pass state -->
                    <div
                        v-if="eligibilityValid"
                        class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 flex items-center gap-2 text-sm text-green-800"
                    >
                        <i class="pi pi-check-circle text-green-600 flex-shrink-0" />
                        <span>
                            <strong>Eligibility Confirmed.</strong> You may proceed to document submission.
                        </span>
                    </div>
                </div>

                <!-- ── PHASE 2: Document Submission ── -->
                <div v-else-if="activePhase.key === 'documents'" class="space-y-4">

                    <div class="flex gap-2 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 font-medium">
                            <i class="pi pi-list-check text-xs" /> Steps 2–3
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs text-red-700 font-medium">
                            <i class="pi pi-asterisk text-xs" /> Required documents marked with *
                        </span>
                    </div>

                    <!-- Previous Assignment Orders -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected.assignment_orders ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i class="pi pi-file-edit text-gray-500 text-sm mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Previous Assignment Orders <span class="text-red-500">*</span>
                                </p>
                                <p class="text-xs text-amber-600 mt-0.5">
                                    Must clearly indicate an assignment duration of more than 1 year outside the fleet.
                                </p>
                            </div>
                        </div>
                        <Select
                            v-model="selected.assignment_orders"
                            :options="assignmentOrders"
                            option-label="name"
                            placeholder="— select assignment orders —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select assignment orders —</span>
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
                                    No assignment orders found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected.assignment_orders" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected.assignment_orders.name }}</span>
                        </div>
                    </div>

                    <!-- Performance Evaluations -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected.performance_eval ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i class="pi pi-star text-gray-500 text-sm mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Performance Evaluations <span class="text-red-500">*</span>
                                </p>
                                <p class="text-xs text-blue-500 mt-0.5">
                                    Latest performance evaluation report covering your outside-fleet assignment period.
                                </p>
                            </div>
                        </div>
                        <Select
                            v-model="selected.performance_eval"
                            :options="performanceEvals"
                            option-label="name"
                            placeholder="— select performance evaluation —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select performance evaluation —</span>
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
                                    No performance evaluations found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected.performance_eval" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected.performance_eval.name }}</span>
                        </div>
                    </div>

                    <!-- Additional Certifications (optional) -->
                    <div
                        class="p-4 rounded-lg border transition-colors"
                        :class="selected.certification ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'"
                    >
                        <div class="flex items-start gap-2 mb-2">
                            <i class="pi pi-graduation-cap text-gray-500 text-sm mt-0.5" />
                            <div>
                                <p class="text-sm font-medium text-gray-700">
                                    Additional Certifications
                                    <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span>
                                </p>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    Any additional certifications relevant to EXO Billet. Strengthens your application.
                                </p>
                            </div>
                        </div>
                        <Select
                            v-model="selected.certification"
                            :options="certifications"
                            option-label="name"
                            placeholder="— select certification (optional) —"
                            class="w-full"
                            show-clear
                        >
                            <template #value="{ value }">
                                <div v-if="value" class="flex items-center gap-2">
                                    <i class="pi pi-check-circle text-green-500 text-sm" />
                                    <span class="text-sm">{{ value.name }}</span>
                                </div>
                                <span v-else class="text-gray-400 text-sm">— select certification (optional) —</span>
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
                                    No certifications found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected.certification" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected.certification.name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── PHASE 3: Board Deliberation (informational) ── -->
                <div v-else-if="activePhase.key === 'deliberation'" class="space-y-4">
                    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-5 space-y-3">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-users text-blue-500 text-lg" />
                            <span class="text-sm font-semibold text-blue-800">What happens during deliberation?</span>
                        </div>
                        <ul class="text-sm text-blue-700 space-y-2 ml-6 list-disc">
                            <li>Your application is queued for the next convening of the board or panel.</li>
                            <li>The admin will mark your application as <strong>For Review</strong> once received.</li>
                            <li>The board evaluates your qualifications, documents, and performance history.</li>
                            <li>A deliberation schedule (<strong>scheduled_at</strong>) may be set and communicated to you.</li>
                            <li>You will be notified of the board's decision via system remarks.</li>
                        </ul>
                    </div>
                    <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                        <span class="font-semibold text-gray-700">Status Progression:</span>
                        <div class="flex flex-wrap items-center gap-2 mt-2">
                            <Tag severity="warn"    value="Pending"    class="text-xs" />
                            <i class="pi pi-arrow-right text-xs text-gray-400" />
                            <Tag severity="info"    value="For Review" class="text-xs" />
                            <i class="pi pi-arrow-right text-xs text-gray-400" />
                            <Tag severity="info"    value="Scheduled"  class="text-xs" />
                            <i class="pi pi-arrow-right text-xs text-gray-400" />
                            <Tag severity="success" value="Approved"   class="text-xs" />
                            <span class="text-gray-400 text-xs">or</span>
                            <Tag severity="danger"  value="Rejected"   class="text-xs" />
                        </div>
                    </div>
                </div>

                <!-- ── PHASE 4: Review & Submit ── -->
                <div v-else class="space-y-5">

                    <!-- Eligibility summary -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Phase 1 — Eligibility
                        </h4>
                        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-check-circle text-sm text-green-500" />
                                <span class="text-sm text-gray-700">Outside fleet &gt; 1 year</span>
                            </div>
                            <Tag severity="success" value="Confirmed" class="text-xs" />
                        </div>
                        <div class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5">
                            <div class="flex items-center gap-2">
                                <i class="pi pi-check-circle text-sm text-green-500" />
                                <span class="text-sm text-gray-700">Meets EXO Criteria</span>
                            </div>
                            <Tag severity="success" value="Confirmed" class="text-xs" />
                        </div>
                    </div>

                    <!-- Documents summary -->
                    <div>
                        <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">
                            Phase 2 — Documents
                        </h4>
                        <div
                            v-for="(label, key) in {
                                assignment_orders: 'Previous Assignment Orders',
                                performance_eval:  'Performance Evaluations',
                                certification:     'Additional Certifications',
                            }"
                            :key="key"
                            class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5"
                        >
                            <div class="flex items-center gap-2 min-w-0">
                                <i
                                    class="pi text-sm flex-shrink-0"
                                    :class="
                                        selected[key]
                                            ? 'pi-check-circle text-green-500'
                                            : key === 'certification'
                                            ? 'pi-minus-circle text-gray-300'
                                            : 'pi-times-circle text-red-400'
                                    "
                                />
                                <span class="text-sm text-gray-700 truncate">
                                    {{ label }}
                                    <span v-if="key === 'certification'" class="text-xs text-gray-400 ml-1">(optional)</span>
                                </span>
                            </div>
                            <span
                                class="text-xs font-medium ml-4 flex-shrink-0"
                                :class="
                                    selected[key]
                                        ? 'text-gray-700'
                                        : key === 'certification'
                                        ? 'text-gray-400 italic'
                                        : 'text-red-400 italic'
                                "
                            >
                                {{ selected[key]?.name ?? (key === 'certification' ? '— not provided —' : '— not selected —') }}
                            </span>
                        </div>
                    </div>

                    <!-- Board deliberation notice -->
                    <div class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 flex items-start gap-2 text-sm text-amber-800">
                        <i class="pi pi-send text-amber-500 mt-0.5 flex-shrink-0" />
                        <span>
                            Upon submission, your application will be queued for board deliberation
                            <strong>(Phase 3)</strong>. The board will review your qualifications and
                            issue reassignment orders if approved <strong>(Phase 4 — Steps 6–7)</strong>.
                        </span>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
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
                <span class="font-semibold text-lg text-gray-800">My EXO Reassignment Applications</span>
                <IconField>
                    <InputIcon><i class="pi pi-search" /></InputIcon>
                    <InputText
                        v-model="searchQuery"
                        placeholder="Search by status or document…"
                        @input="onSearch"
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
                    <div class="text-center py-6 text-gray-400">No EXO Reassignment applications found.</div>
                </template>
                <template #loading>
                    <div class="text-center py-6 text-gray-400">Loading…</div>
                </template>

              <!-- Assignment Orders -->
<Column header="Assignment Orders">
    <template #body="{ data }">
        <span class="text-sm">{{ data.assignment_orders?.name ?? '—' }}</span>
    </template>
</Column>

<!-- Performance Evaluation -->
<Column header="Performance Evaluation">
    <template #body="{ data }">
        <span class="text-sm">{{ data.performance_eval?.name ?? '—' }}</span>
    </template>
</Column>

<!-- Certifications -->
<Column header="Certifications">
    <template #body="{ data }">
        <span class="text-sm">{{ data.certification?.name ?? '—' }}</span>
    </template>
</Column>
                <Column header="Scheduled At">
                    <template #body="{ data }">
                        <span
                            class="text-xs"
                            :class="data.scheduled_at ? 'text-blue-600 font-medium' : 'text-gray-400'"
                        >
                            {{ formatDate(data.scheduled_at) }}
                        </span>
                    </template>
                </Column>
                <Column field="status" header="Status" sortable>
                    <template #body="{ data }">
                        <Tag :severity="statusSeverity(data.status)" :value="statusLabel(data.status)" />
                    </template>
                </Column>
                <Column header="Remarks">
                    <template #body="{ data }">
                        <span class="text-sm text-gray-500 italic">{{ data.remarks ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="Actions" style="width: 7rem">
                    <template #body="{ data }">
                        <!-- was: can(['delete exo_reassignment']) — wrong underscore placement -->
<Button
    v-if="can(['delete exo_reassign'])"
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
                class="mt-4"
                @page="onPageChange"
            />
        </div>
    </div>
</template>