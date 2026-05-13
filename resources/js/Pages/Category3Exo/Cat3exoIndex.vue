<script setup>
import { ref, computed, onMounted } from "vue";
import Button    from "primevue/button";
import Tag       from "primevue/tag";
import Select    from "primevue/select";
import InputText from "primevue/inputtext";
import IconField  from "primevue/iconfield";
import InputIcon  from "primevue/inputicon";
import DataTable  from "primevue/datatable";
import Column     from "primevue/column";
import Paginator  from "primevue/paginator";
import { useToast } from "primevue/usetoast";
import { usePage }  from "@inertiajs/vue3";
import axios from "axios";

/* ============================================================
   PROPS
============================================================ */
const props = defineProps({
    title: { type: String, default: "CAT 3 EXO Promotion" },
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
   STEPS  — CAT 3 EXO Process Flow
============================================================ */
const STEPS = [
    {
        step: 1, key: "prepare",
        label: "Prepare Documentation",
        icon: "pi pi-clipboard",
        description: "Gather and prepare required documents: Updated Sea Duty Records indicating compliance with CAT 2 CO minimum requirements, including minimum 6 months residency aboard ship and minimum 150 hours total steaming time.",
    },
    {
        step: 2, key: "submit",
        label: "Submit Promotion Request",
        icon: "pi pi-file-arrow-up",
        description: "Submit a formal request for promotion to Category 3 Executive Officer (CAT 3 EXO) along with your updated Sea Duty Records to the appropriate office or personnel.",
    },
    {
        step: 3, key: "verification",
        label: "Verification & Review",
        icon: "pi pi-search",
        description: "The submitted Sea Duty Records will be reviewed to ensure you meet the residency and steaming time requirements for promotion from CAT 2 CO to CAT 3 EXO.",
    },
    {
        step: 4, key: "deliberation",
        label: "Board Deliberation",
        icon: "pi pi-users",
        description: "Your application will be forwarded to the appropriate promotion board for deliberation on the promotion to CAT 3 EXO.",
    },
    {
        step: 5, key: "decision",
        label: "Board Decision",
        icon: "pi pi-verified",
        description: "Await the board's decision regarding your promotion to Category 3 Executive Officer (CAT 3 EXO).",
    },
    {
        step: 6, key: "orders",
        label: "Issuance of Promotion Orders",
        icon: "pi pi-file-pdf",
        description: "If approved, receive the official orders for promotion to Category 3 Executive Officer (CAT 3 EXO).",
    },
    {
        step: 7, key: "complete",
        label: "Update Personnel Records",
        icon: "pi pi-check-circle",
        description: "Ensure that personnel records are updated to reflect the promotion to CAT 3 EXO.",
    },
];

/* ============================================================
   STATUS → STEP MAP (0-based stepper index)
============================================================ */
const STATUS_STEP_MAP = {
    pending:    2,
    for_review: 3,
    scheduled:  3,
    approved:   4,
    rejected:   4,
    completed:  6,
};

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
        const { data } = await axios.get(route("cat3ExoPromotions.index"), {
            headers: { Accept: "application/json" },
        });
        assignmentOrders.value = data.assignmentOrders ?? [];
        performanceEvals.value = data.performanceEvals ?? [];
        certifications.value   = data.certifications   ?? [];
    } catch {
        toast.add({ severity: "error", summary: "Load Failed", detail: "Unable to load data.", life: 4000 });
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
const trackedRecord = ref(null);

const fetchApplications = async (pg = 1, search = "") => {
    loadingTable.value = true;
    try {
        const { data } = await axios.get(route("cat3ExoPromotions.fetch"), {
            params: { page: pg, search, per_page: rowsPerPage.value, user_id: props.user.id },
        });
        tableRecords.value = data.data;
        totalRecords.value = data.total;
        currentPage.value  = data.current_page;

        if (trackedRecord.value) {
            const refreshed = data.data.find(r => r.id === trackedRecord.value.id);
            if (refreshed) {
                trackedRecord.value = refreshed;
                currentStep.value   = STATUS_STEP_MAP[refreshed.status] ?? 2;
            }
        }
    } catch {
        toast.add({ severity: "error", summary: "Load Failed", detail: "Unable to load applications.", life: 4000 });
    } finally {
        loadingTable.value = false;
    }
};

const onSearch     = () => { clearTimeout(searchTimeout.value); searchTimeout.value = setTimeout(() => fetchApplications(1, searchQuery.value), 300); };
const onPageChange = (event) => fetchApplications(event.page + 1, searchQuery.value);

onMounted(async () => { await fetchIndexData(); await fetchApplications(1, ""); });

/* ============================================================
   STEPPER STATE
============================================================ */
const currentStep = ref(0);
const isFirstStep = computed(() => currentStep.value === 0);
const isLastStep  = computed(() => currentStep.value === STEPS.length - 1);
const activeStep  = computed(() => STEPS[currentStep.value]);

const nextStep = () => { if (!isLastStep.value)  currentStep.value++; };
const prevStep = () => { if (!isFirstStep.value) currentStep.value--; };

const isTrackingMode = computed(() => trackedRecord.value !== null);

const trackApplication = (record) => {
    trackedRecord.value = record;
    selected.value = {
        assignment_orders: record.assignment_orders ?? null,
        performance_eval:  record.performance_eval  ?? null,
        certification:     record.certification     ?? null,
    };
    currentStep.value = STATUS_STEP_MAP[record.status] ?? 2;
    setTimeout(() => document.getElementById("stepper-card")?.scrollIntoView({ behavior: "smooth", block: "start" }), 50);
};

const startNewApplication = () => { trackedRecord.value = null; resetForm(); currentStep.value = 0; };

/* ============================================================
   STEP 1 — PREPARE
   CAT 3 EXO checklist items
============================================================ */
const checklist = ref({
    seaDutyRecords: false,
    minResidency:   false,
    steamingHours:  false,
});
const checklistDone = computed(() => Object.values(checklist.value).every(Boolean));

/* ============================================================
   STEP 2 — SUBMIT
============================================================ */
const selected = ref({ assignment_orders: null, performance_eval: null, certification: null });
const step2Valid = computed(() => selected.value.assignment_orders !== null);
const resetForm  = () => {
    checklist.value = { seaDutyRecords: false, minResidency: false, steamingHours: false };
    selected.value  = { assignment_orders: null, performance_eval: null, certification: null };
};

/* ============================================================
   PER-STEP VALIDATION
============================================================ */
const currentStepValid = computed(() => {
    if (isTrackingMode.value)    return true;
    if (currentStep.value === 0) return checklistDone.value;
    if (currentStep.value === 1) return step2Valid.value;
    return true;
});

/* ============================================================
   TRACKING MODE HELPERS
============================================================ */
const decisionOutcome = computed(() => {
    if (!trackedRecord.value) return null;
    const s = trackedRecord.value.status;
    if (s === "approved" || s === "completed") return "approved";
    if (s === "rejected")  return "rejected";
    if (s === "scheduled") return "scheduled";
    return null;
});

const ordersAvailable = computed(() =>
    trackedRecord.value?.status === "approved" || trackedRecord.value?.status === "completed",
);

const completionStatus = computed(() => trackedRecord.value?.status === "completed" ? "success" : "secondary");
const completionLabel  = computed(() => trackedRecord.value?.status === "completed" ? "Completed" : "Pending Approval");

const trackingProgress = computed(() => {
    if (!trackedRecord.value) return 0;
    return Math.round(((STATUS_STEP_MAP[trackedRecord.value.status] ?? 0) / (STEPS.length - 1)) * 100);
});

/* ============================================================
   SUBMIT
============================================================ */
const submitting = ref(false);
const submit = async () => {
    submitting.value = true;
    try {
        await axios.post(route("cat3ExoPromotions.store"), {
            user_id:              props.user.id,
            assignment_orders_id: selected.value.assignment_orders?.id ?? null,
            performance_eval_id:  selected.value.performance_eval?.id  ?? null,
            certification_id:     selected.value.certification?.id     ?? null,
        });
        toast.add({ severity: "success", summary: "Application Submitted", detail: "Your CAT 3 EXO Promotion application has been submitted for board deliberation.", life: 5000 });
        resetForm(); currentStep.value = 0;
        await fetchIndexData(); await fetchApplications(1, searchQuery.value);
    } catch (err) {
        toast.add({ severity: "error", summary: "Submission Error", detail: err.response?.data?.message ?? "Submission failed. Please try again.", life: 5000 });
    } finally { submitting.value = false; }
};

/* ============================================================
   DELETE
============================================================ */
const deleteRecord = async (id) => {
    if (!confirm("Delete this CAT 3 EXO application?")) return;
    try {
        await axios.delete(route("cat3ExoPromotions.destroy", id));
        toast.add({ severity: "info", summary: "Deleted", detail: "Application deleted.", life: 3000 });
        if (trackedRecord.value?.id === id) startNewApplication();
        await fetchApplications(currentPage.value, searchQuery.value);
    } catch {
        toast.add({ severity: "error", summary: "Delete Failed", detail: "Unable to delete application.", life: 4000 });
    }
};

/* ============================================================
   HELPERS
============================================================ */
const statusSeverity = (s) => ({ pending:"warn", for_review:"info", scheduled:"info", approved:"success", rejected:"danger", completed:"success" }[s] ?? "secondary");
const statusLabel    = (s) => ({ pending:"Pending", for_review:"For Review", scheduled:"Scheduled", approved:"Approved", rejected:"Rejected", completed:"Completed" }[s] ?? s);
const formatDate     = (val) => val ? new Date(val).toLocaleDateString("en-PH", { year:"numeric", month:"short", day:"numeric", hour:"2-digit", minute:"2-digit" }) : "—";
</script>

<template>
<div class="space-y-6">

    <!-- ══ PAGE HEADER ══════════════════════════════════════════ -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
        <div>
            <h2 class="text-xl font-bold text-gray-800">CAT 3 EXO Promotion</h2>
            <p class="text-sm text-gray-500 mt-0.5">Apply for promotion to Category 3 Executive Officer (EXO) Billet. Complete all 7 steps below.</p>
        </div>
        <div class="flex items-center gap-2 self-start sm:self-auto">
            <Button v-if="isTrackingMode" label="+ New Application" icon="pi pi-plus" size="small" severity="secondary" outlined @click="startNewApplication" />
            <Tag severity="info" value="CAT 3 EXO" class="text-sm font-semibold px-3 py-1.5" />
        </div>
    </div>

    <!-- ══ TRACKING BANNER ══════════════════════════════════════ -->
    <Transition name="fade">
        <div v-if="isTrackingMode" class="rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-3 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0">
                    <i class="pi pi-eye text-indigo-600 text-sm" />
                </div>
                <div>
                    <p class="text-sm font-semibold text-indigo-800">Tracking Application #{{ trackedRecord.id }}</p>
                    <p class="text-xs text-indigo-500 mt-0.5 flex items-center gap-1.5 flex-wrap">
                        Current status:
                        <Tag :severity="statusSeverity(trackedRecord.status)" :value="statusLabel(trackedRecord.status)" class="text-xs" />
                        <span v-if="trackedRecord.scheduled_at" class="text-indigo-400">· Scheduled {{ formatDate(trackedRecord.scheduled_at) }}</span>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-4 flex-shrink-0">
                <div class="hidden sm:flex items-center gap-2">
                    <div class="w-28 bg-indigo-100 rounded-full h-1.5 overflow-hidden">
                        <div class="h-1.5 rounded-full transition-all duration-700"
                             :class="trackedRecord.status === 'rejected' ? 'bg-red-400' : 'bg-indigo-500'"
                             :style="{ width: `${trackingProgress}%` }" />
                    </div>
                    <span class="text-xs text-indigo-600 font-medium whitespace-nowrap">
                        Step {{ (STATUS_STEP_MAP[trackedRecord.status] ?? 2) + 1 }} / {{ STEPS.length }}
                    </span>
                </div>
                <Button label="Exit Tracking" icon="pi pi-times" size="small" severity="secondary" text @click="startNewApplication" />
            </div>
        </div>
    </Transition>

    <!-- ══ STEP PROGRESS TRACKER ════════════════════════════════ -->
    <div class="grid grid-cols-4 sm:grid-cols-7 gap-1.5">
        <div
            v-for="(s, idx) in STEPS"
            :key="idx"
            class="flex flex-col items-center gap-1"
            :class="idx < currentStep ? 'cursor-pointer' : 'cursor-default'"
            @click="idx < currentStep ? (currentStep = idx) : null"
        >
            <div
                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold transition-all border-2"
                :class="[
                    idx === currentStep && !isTrackingMode  ? 'bg-blue-600   text-white border-blue-600   shadow-md shadow-blue-200'   : '',
                    idx === currentStep &&  isTrackingMode  ? 'bg-indigo-600 text-white border-indigo-600 shadow-md shadow-indigo-200' : '',
                    idx <  currentStep                      ? 'bg-green-500  text-white border-green-500'                              : '',
                    idx >  currentStep                      ? 'bg-white      text-gray-400 border-gray-200'                           : '',
                ]"
            >
                <i v-if="idx < currentStep" class="pi pi-check text-xs" />
                <span v-else>{{ idx + 1 }}</span>
            </div>
            <p class="text-center text-[10px] font-medium leading-tight"
               :class="[
                   idx === currentStep && !isTrackingMode ? 'text-blue-700'   : '',
                   idx === currentStep &&  isTrackingMode ? 'text-indigo-700' : '',
                   idx <  currentStep                     ? 'text-green-600'  : '',
                   idx >  currentStep                     ? 'text-gray-400'   : '',
               ]">
                {{ s.label }}
            </p>
        </div>
    </div>

    <!-- Progress bar -->
    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
        <div class="h-1.5 rounded-full transition-all duration-500"
             :class="[
                 isTrackingMode && trackedRecord?.status === 'rejected' ? 'bg-red-400'    : '',
                 isTrackingMode && trackedRecord?.status !== 'rejected' ? 'bg-indigo-500' : '',
                 !isTrackingMode                                         ? 'bg-blue-500'   : '',
             ]"
             :style="{ width: `${(currentStep / (STEPS.length - 1)) * 100}%` }" />
    </div>

    <!-- ══ INFO BANNER ═══════════════════════════════════════════ -->
    <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 flex items-start gap-3">
        <i class="pi pi-info-circle text-blue-500 text-lg mt-0.5 flex-shrink-0" />
        <div class="text-sm text-blue-800 leading-relaxed">
            <span class="font-semibold">CAT 3 EXO Promotion Process:</span>
            Prepare Docs → Submit Request → Verification → Board Deliberation → Decision → Promotion Orders → Personnel Update.
        </div>
    </div>

    <!-- ══ STEP FORM CARD ════════════════════════════════════════ -->
    <div id="stepper-card" class="border rounded-xl overflow-hidden bg-white shadow-sm transition-all" :class="isTrackingMode ? 'ring-2 ring-indigo-300' : ''">

        <!-- Tab bar -->
        <div class="flex border-b bg-gray-50 overflow-x-auto">
            <div
                v-for="(s, idx) in STEPS"
                :key="idx"
                class="flex-shrink-0 flex items-center justify-center gap-1.5 px-3 py-3 text-xs font-medium transition-colors"
                :class="[
                    idx === currentStep ? (isTrackingMode ? 'bg-white border-b-2 border-indigo-600 text-indigo-600' : 'bg-white border-b-2 border-blue-600 text-blue-600') : '',
                    idx <  currentStep  ? 'text-green-600 cursor-pointer hover:bg-gray-100' : '',
                    idx >  currentStep  ? 'text-gray-400 cursor-not-allowed'               : '',
                ]"
                @click="idx < currentStep ? (currentStep = idx) : null"
            >
                <span class="w-5 h-5 rounded-full flex items-center justify-center text-xs font-bold flex-shrink-0"
                      :class="[
                          idx === currentStep ? (isTrackingMode ? 'bg-indigo-600 text-white' : 'bg-blue-600 text-white') : '',
                          idx <  currentStep  ? 'bg-green-500 text-white'   : '',
                          idx >  currentStep  ? 'bg-gray-200 text-gray-500' : '',
                      ]">
                    <i v-if="idx < currentStep" class="pi pi-check" style="font-size:0.6rem" />
                    <span v-else>{{ idx + 1 }}</span>
                </span>
                <span class="hidden md:inline whitespace-nowrap">{{ s.label }}</span>
            </div>
        </div>

        <!-- Step body -->
        <div class="p-6">

            <!-- Tracking read-only notice -->
            <Transition name="fade">
                <div v-if="isTrackingMode" class="mb-4 rounded-lg border border-indigo-100 bg-indigo-50 px-4 py-2.5 flex items-center gap-2 text-xs text-indigo-700">
                    <i class="pi pi-lock text-indigo-400 flex-shrink-0" />
                    <span>
                        Viewing application <strong>#{{ trackedRecord.id }}</strong> — read-only.
                        <button class="underline ml-1 font-medium hover:text-indigo-900" @click="startNewApplication">Start a new application instead</button>
                    </span>
                </div>
            </Transition>

            <!-- Step header -->
            <div class="flex items-center gap-2 mb-1">
                <i :class="[activeStep.icon, isTrackingMode ? 'text-indigo-500' : 'text-blue-500', 'text-lg']" />
                <h3 class="text-lg font-semibold text-gray-800">Step {{ activeStep.step }}: {{ activeStep.label }}</h3>
                <Tag v-if="isTrackingMode" :severity="statusSeverity(trackedRecord.status)" :value="statusLabel(trackedRecord.status)" class="ml-auto text-xs" />
            </div>
            <p class="text-sm text-gray-500 mb-6">{{ activeStep.description }}</p>

            <!-- Loading -->
            <div v-if="loadingIndex" class="flex items-center justify-center py-8 text-gray-400">
                <i class="pi pi-spin pi-spinner text-2xl mr-2" /><span class="text-sm">Loading data…</span>
            </div>

            <!-- ─── STEP 1: PREPARE ──────────────────────────────── -->
            <div v-else-if="activeStep.key === 'prepare'" class="space-y-4">
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 flex items-start gap-2 text-sm text-amber-800">
                    <i class="pi pi-exclamation-triangle text-amber-500 mt-0.5 flex-shrink-0" />
                    <span v-if="isTrackingMode">All checklist items were confirmed at the time of submission.</span>
                    <span v-else>Check all boxes to confirm you have the required documents ready before proceeding.</span>
                </div>

                <div class="space-y-3">
                    <div
                        v-for="item in [
                            { key:'seaDutyRecords', dbKey:'checklist_sea_duty_records', icon:'pi-book',  label:'Updated Sea Duty Records',                    desc:'Updated Sea Duty Records indicating compliance with the minimum requirements for a Category 2 Commanding Officer (CAT 2 CO).' },
                            { key:'minResidency',   dbKey:'checklist_min_residency',    icon:'pi-clock', label:'Minimum 6 Months Residency Aboard Ship',       desc:'Documentary evidence of continuous residency aboard the assigned vessel for a minimum of 6 months.' },
                            { key:'steamingHours',  dbKey:'checklist_steaming_hours',   icon:'pi-gauge', label:'Minimum 150 Hours Total Steaming Time',        desc:'Certified logbook entries or official records confirming at least 150 hours of total steaming time.' },
                        ]"
                        :key="item.key"
                        class="flex items-start gap-4 p-4 rounded-xl border transition-all"
                        :class="[
                            isTrackingMode
                                ? (trackedRecord[item.dbKey] ? 'border-green-300 bg-green-50' : 'border-red-200 bg-red-50')
                                : (checklist[item.key] ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50 hover:border-blue-300 cursor-pointer'),
                        ]"
                        @click="!isTrackingMode ? (checklist[item.key] = !checklist[item.key]) : null"
                    >
                        <div class="w-6 h-6 rounded border-2 flex items-center justify-center flex-shrink-0 mt-0.5 transition-all"
                             :class="(isTrackingMode ? trackedRecord[item.dbKey] : checklist[item.key]) ? 'bg-green-500 border-green-500' : (isTrackingMode ? 'bg-red-200 border-red-300' : 'border-gray-300 bg-white')">
                            <i v-if="isTrackingMode ? trackedRecord[item.dbKey] : checklist[item.key]" class="pi pi-check text-white text-xs" />
                            <i v-else-if="isTrackingMode" class="pi pi-times text-red-400 text-xs" />
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-0.5">
                                <i :class="['pi', item.icon, 'text-gray-500 text-sm']" />
                                <p class="text-sm font-semibold text-gray-800">{{ item.label }}</p>
                                <span class="text-xs text-red-500 font-medium">Required</span>
                            </div>
                            <p class="text-xs text-gray-500">{{ item.desc }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="isTrackingMode" class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 flex items-center gap-2 text-sm text-green-800">
                    <i class="pi pi-check-circle text-green-600 flex-shrink-0" />
                    <span><strong>Checklist confirmed</strong> when this application was submitted.</span>
                </div>
                <template v-else>
                    <div v-if="checklistDone" class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 flex items-center gap-2 text-sm text-green-800">
                        <i class="pi pi-check-circle text-green-600 flex-shrink-0" />
                        <span><strong>All documents confirmed.</strong> You may proceed to document submission.</span>
                    </div>
                    <div v-else class="text-xs text-gray-400 flex items-center gap-1">
                        <i class="pi pi-info-circle" />
                        {{ Object.values(checklist).filter(Boolean).length }} of {{ Object.values(checklist).length }} items confirmed
                    </div>
                </template>
            </div>

            <!-- ─── STEP 2: SUBMIT ───────────────────────────────── -->
            <div v-else-if="activeStep.key === 'submit'" class="space-y-4">
                <div class="flex gap-2 flex-wrap mb-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2.5 py-0.5 text-xs text-red-700 font-medium">
                        <i class="pi pi-asterisk text-xs" /> Required documents marked with *
                    </span>
                    <span v-if="isTrackingMode" class="inline-flex items-center gap-1 rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs text-indigo-700 font-medium">
                        <i class="pi pi-lock text-xs" /> Submitted — read-only
                    </span>
                </div>

                <!-- Sea Duty Records (required) -->
                <div class="p-4 rounded-xl border transition-colors" :class="selected.assignment_orders ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'">
                    <div class="flex items-start gap-2 mb-2">
                        <i class="pi pi-book text-gray-500 text-sm mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-gray-700">Updated Sea Duty Records <span class="text-red-500">*</span></p>
                            <p class="text-xs text-amber-600 mt-0.5">Sea Duty Records indicating compliance with CAT 2 CO minimum requirements (6 months residency, 150 hours steaming).</p>
                        </div>
                    </div>
                    <div v-if="isTrackingMode" class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-green-200 bg-white text-sm text-gray-700">
                        <i class="pi pi-check-circle text-green-500" /> {{ selected.assignment_orders?.name ?? '—' }}
                    </div>
                    <Select v-else v-model="selected.assignment_orders" :options="assignmentOrders" option-label="name" placeholder="— select sea duty record —" class="w-full" show-clear>
                        <template #value="{ value }">
                            <div v-if="value" class="flex items-center gap-2"><i class="pi pi-check-circle text-green-500 text-sm" /><span class="text-sm">{{ value.name }}</span></div>
                            <span v-else class="text-gray-400 text-sm">— select sea duty record —</span>
                        </template>
                        <template #option="{ option }"><div class="flex items-center gap-2"><i class="pi pi-file text-gray-400 text-sm" /><span class="text-sm">{{ option.name }}</span></div></template>
                        <template #empty><div class="text-center py-3 text-gray-400 text-sm"><i class="pi pi-inbox block text-2xl mb-1 opacity-40" />No records found for your account.</div></template>
                    </Select>
                </div>

                <!-- Formal Promotion Request Letter (optional but shown) -->
                <div class="p-4 rounded-xl border transition-colors" :class="selected.performance_eval ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'">
                    <div class="flex items-start gap-2 mb-2">
                        <i class="pi pi-envelope text-gray-500 text-sm mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-gray-700">Formal Promotion Request Letter <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span></p>
                            <p class="text-xs text-blue-500 mt-0.5">Official letter formally requesting promotion to CAT 3 EXO Billet.</p>
                        </div>
                    </div>
                    <div v-if="isTrackingMode" class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 bg-white text-sm" :class="selected.performance_eval ? 'text-gray-700' : 'text-gray-400 italic'">
                        <i :class="selected.performance_eval ? 'pi pi-check-circle text-green-500' : 'pi pi-minus-circle text-gray-300'" />
                        {{ selected.performance_eval?.name ?? '— not provided —' }}
                    </div>
                    <Select v-else v-model="selected.performance_eval" :options="performanceEvals" option-label="name" placeholder="— select promotion request letter (optional) —" class="w-full" show-clear>
                        <template #value="{ value }">
                            <div v-if="value" class="flex items-center gap-2"><i class="pi pi-check-circle text-green-500 text-sm" /><span class="text-sm">{{ value.name }}</span></div>
                            <span v-else class="text-gray-400 text-sm">— select promotion request letter (optional) —</span>
                        </template>
                        <template #option="{ option }"><div class="flex items-center gap-2"><i class="pi pi-file text-gray-400 text-sm" /><span class="text-sm">{{ option.name }}</span></div></template>
                        <template #empty><div class="text-center py-3 text-gray-400 text-sm"><i class="pi pi-inbox block text-2xl mb-1 opacity-40" />No documents found for your account.</div></template>
                    </Select>
                </div>

                <!-- Supporting Certification (optional) -->
                <div class="p-4 rounded-xl border transition-colors" :class="selected.certification ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-gray-50'">
                    <div class="flex items-start gap-2 mb-2">
                        <i class="pi pi-graduation-cap text-gray-500 text-sm mt-0.5" />
                        <div>
                            <p class="text-sm font-medium text-gray-700">Supporting Certifications <span class="ml-1 text-xs font-normal text-gray-400">(optional)</span></p>
                            <p class="text-xs text-gray-500 mt-0.5">Additional certifications to strengthen your CAT 3 EXO promotion application.</p>
                        </div>
                    </div>
                    <div v-if="isTrackingMode" class="flex items-center gap-2 px-3 py-2.5 rounded-lg border border-gray-200 bg-white text-sm" :class="selected.certification ? 'text-gray-700' : 'text-gray-400 italic'">
                        <i :class="selected.certification ? 'pi pi-check-circle text-green-500' : 'pi pi-minus-circle text-gray-300'" />
                        {{ selected.certification?.name ?? '— not provided —' }}
                    </div>
                    <Select v-else v-model="selected.certification" :options="certifications" option-label="name" placeholder="— select certification (optional) —" class="w-full" show-clear>
                        <template #value="{ value }">
                            <div v-if="value" class="flex items-center gap-2"><i class="pi pi-check-circle text-green-500 text-sm" /><span class="text-sm">{{ value.name }}</span></div>
                            <span v-else class="text-gray-400 text-sm">— select certification (optional) —</span>
                        </template>
                        <template #option="{ option }"><div class="flex items-center gap-2"><i class="pi pi-file text-gray-400 text-sm" /><span class="text-sm">{{ option.name }}</span></div></template>
                        <template #empty><div class="text-center py-3 text-gray-400 text-sm"><i class="pi pi-inbox block text-2xl mb-1 opacity-40" />No certifications found for your account.</div></template>
                    </Select>
                </div>
            </div>

            <!-- ─── STEP 3: VERIFICATION ─────────────────────────── -->
            <div v-else-if="activeStep.key === 'verification'" class="space-y-4">
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-5 space-y-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="pi pi-search text-blue-500 text-lg" />
                        <span class="text-sm font-semibold text-blue-800">Sea Duty Records Verification Status</span>
                    </div>
                    <div class="space-y-3">
                        <div v-for="item in [
                                { label:'Residency Requirement Verification (Min. 6 months)', icon:'pi-clock',  dbKey:'checklist_min_residency'   },
                                { label:'Steaming Hours Validation (Min. 150 hours)',         icon:'pi-gauge',  dbKey:'checklist_steaming_hours'  },
                                { label:'CAT 2 CO Compliance Review',                         icon:'pi-shield', dbKey:'checklist_sea_duty_records' },
                             ]"
                             :key="item.dbKey"
                             class="flex items-center justify-between bg-white rounded-lg px-4 py-3 border border-blue-100"
                        >
                            <div class="flex items-center gap-2">
                                <i :class="['pi', item.icon, 'text-sm', isTrackingMode && trackedRecord[item.dbKey] ? 'text-green-500' : 'text-amber-500']" />
                                <span class="text-sm text-gray-700 font-medium">{{ item.label }}</span>
                            </div>
                            <Tag v-if="isTrackingMode" :severity="trackedRecord[item.dbKey] ? 'success' : 'warn'" :value="trackedRecord[item.dbKey] ? 'Verified' : 'Pending'" class="text-xs" />
                            <Tag v-else severity="warn" value="Pending" class="text-xs" />
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500 flex items-start gap-2">
                    <i class="pi pi-info-circle text-gray-400 mt-0.5 flex-shrink-0" />
                    <span>
                        The admin office will update your application status as verification progresses.
                        <span v-if="isTrackingMode && trackedRecord.remarks"><br /><strong>Remarks:</strong> {{ trackedRecord.remarks }}</span>
                    </span>
                </div>
            </div>

            <!-- ─── STEP 4: DELIBERATION ─────────────────────────── -->
            <div v-else-if="activeStep.key === 'deliberation'" class="space-y-4">
                <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-5 space-y-3">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="pi pi-users text-blue-500 text-lg" />
                        <span class="text-sm font-semibold text-blue-800">Board Review Timeline</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-3 bg-white rounded-lg px-4 py-3 border border-blue-100">
                            <div class="w-7 h-7 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i class="pi pi-send text-blue-500 text-xs" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">Application forwarded to promotion board</p>
                                <p class="text-xs text-gray-400">Submitted documents are transmitted to the board panel</p>
                            </div>
                            <Tag v-if="isTrackingMode" severity="success" value="Done" class="text-xs flex-shrink-0" />
                        </div>
                        <div class="flex items-center gap-3 bg-white rounded-lg px-4 py-3 border border-blue-100">
                            <div class="w-7 h-7 rounded-full bg-amber-100 flex items-center justify-center flex-shrink-0">
                                <i class="pi pi-spin pi-spinner text-amber-500 text-xs" />
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-700">Under deliberation</p>
                                <p class="text-xs text-gray-400">Panel evaluates qualifications, sea duty records, and steaming hours</p>
                            </div>
                            <Tag v-if="isTrackingMode"
                                 :severity="trackedRecord.status === 'for_review' || trackedRecord.status === 'scheduled' ? 'info' : 'secondary'"
                                 :value="trackedRecord.status === 'for_review' ? 'In Progress' : trackedRecord.status === 'scheduled' ? 'Scheduled' : 'Pending'"
                                 class="text-xs flex-shrink-0" />
                        </div>
                        <div v-if="isTrackingMode && trackedRecord.scheduled_at" class="flex items-center gap-3 bg-blue-50 rounded-lg px-4 py-3 border border-blue-200">
                            <div class="w-7 h-7 rounded-full bg-blue-200 flex items-center justify-center flex-shrink-0">
                                <i class="pi pi-calendar text-blue-600 text-xs" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-blue-800">Board Date Scheduled</p>
                                <p class="text-xs text-blue-600 font-semibold">{{ formatDate(trackedRecord.scheduled_at) }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 bg-white rounded-lg px-4 py-3 border border-blue-100">
                            <div class="w-7 h-7 rounded-full bg-gray-100 flex items-center justify-center flex-shrink-0">
                                <i class="pi pi-hourglass text-gray-400 text-xs" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-400">Pending decision</p>
                                <p class="text-xs text-gray-400">Board will issue its final decision upon completion of deliberation</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    <span class="font-semibold text-gray-700">Status Progression:</span>
                    <div class="flex flex-wrap items-center gap-2 mt-2">
                        <Tag severity="warn"    value="Pending"    class="text-xs" /><i class="pi pi-arrow-right text-xs text-gray-400" />
                        <Tag severity="info"    value="For Review" class="text-xs" /><i class="pi pi-arrow-right text-xs text-gray-400" />
                        <Tag severity="info"    value="Scheduled"  class="text-xs" /><i class="pi pi-arrow-right text-xs text-gray-400" />
                        <Tag severity="success" value="Approved"   class="text-xs" />
                        <span class="text-gray-400 text-xs">or</span>
                        <Tag severity="danger"  value="Rejected"   class="text-xs" />
                    </div>
                </div>
            </div>

            <!-- ─── STEP 5: BOARD DECISION ───────────────────────── -->
            <div v-else-if="activeStep.key === 'decision'" class="space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <!-- Approved -->
                    <div class="rounded-xl border-2 px-5 py-5 flex flex-col items-center gap-3 text-center transition-all"
                         :class="isTrackingMode ? (decisionOutcome === 'approved' ? 'border-green-400 bg-green-50 ring-2 ring-green-300 shadow-md' : 'border-green-200 bg-green-50 opacity-40') : 'border-green-200 bg-green-50'">
                        <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center">
                            <i class="pi pi-check-circle text-green-600 text-2xl" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-green-800">Approved</p>
                            <p class="text-xs text-green-600 mt-1">Promotion recommended. Proceed to issuance of official CAT 3 EXO promotion orders.</p>
                        </div>
                        <Tag severity="success" value="Approved" class="text-xs" />
                        <p v-if="isTrackingMode && decisionOutcome === 'approved'" class="text-xs text-green-700 font-semibold flex items-center gap-1">
                            <i class="pi pi-check-circle" /> Board's decision
                        </p>
                    </div>
                    <!-- Deferred -->
                    <div class="rounded-xl border-2 px-5 py-5 flex flex-col items-center gap-3 text-center transition-all"
                         :class="isTrackingMode ? (decisionOutcome === 'scheduled' ? 'border-amber-400 bg-amber-50 ring-2 ring-amber-300 shadow-md' : 'border-amber-200 bg-amber-50 opacity-40') : 'border-amber-200 bg-amber-50'">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center">
                            <i class="pi pi-clock text-amber-600 text-2xl" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-amber-800">Deferred</p>
                            <p class="text-xs text-amber-600 mt-1">Promotion deferred pending additional requirements or a future board convening.</p>
                        </div>
                        <Tag severity="warn" value="Scheduled" class="text-xs" />
                        <p v-if="isTrackingMode && decisionOutcome === 'scheduled'" class="text-xs text-amber-700 font-semibold flex items-center gap-1">
                            <i class="pi pi-clock" /> Board's decision
                        </p>
                    </div>
                    <!-- Disapproved -->
                    <div class="rounded-xl border-2 px-5 py-5 flex flex-col items-center gap-3 text-center transition-all"
                         :class="isTrackingMode ? (decisionOutcome === 'rejected' ? 'border-red-400 bg-red-50 ring-2 ring-red-300 shadow-md' : 'border-red-200 bg-red-50 opacity-40') : 'border-red-200 bg-red-50'">
                        <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                            <i class="pi pi-times-circle text-red-500 text-2xl" />
                        </div>
                        <div>
                            <p class="text-sm font-bold text-red-800">Disapproved</p>
                            <p class="text-xs text-red-600 mt-1">Promotion not recommended at this time. Refer to board remarks for guidance.</p>
                        </div>
                        <Tag severity="danger" value="Rejected" class="text-xs" />
                        <p v-if="isTrackingMode && decisionOutcome === 'rejected'" class="text-xs text-red-700 font-semibold flex items-center gap-1">
                            <i class="pi pi-times-circle" /> Board's decision
                        </p>
                    </div>
                </div>
                <div v-if="isTrackingMode && trackedRecord.remarks" class="rounded-lg border border-gray-200 bg-white px-4 py-3 space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Board Remarks</p>
                    <p class="text-sm text-gray-700">{{ trackedRecord.remarks }}</p>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500 flex items-center gap-2">
                    <i class="pi pi-info-circle text-gray-400" />
                    Check the remarks field in your application record below for the board's specific decision and notes.
                </div>
            </div>

            <!-- ─── STEP 6: PROMOTION ORDERS ─────────────────────── -->
            <div v-else-if="activeStep.key === 'orders'" class="space-y-4">
                <div class="rounded-xl border bg-green-50 px-5 py-5 space-y-4" :class="ordersAvailable ? 'border-green-300' : 'border-green-200'">
                    <div class="flex items-center gap-2">
                        <i class="pi pi-file-pdf text-green-600 text-lg" />
                        <span class="text-sm font-semibold text-green-800">Official CAT 3 EXO Promotion Orders</span>
                        <Tag v-if="isTrackingMode" :severity="ordersAvailable ? 'success' : 'secondary'" :value="ordersAvailable ? 'Ready' : 'Pending'" class="text-xs ml-auto" />
                    </div>
                    <div class="bg-white rounded-lg border px-4 py-4 flex items-center justify-between gap-3" :class="ordersAvailable ? 'border-green-200' : 'border-green-100'">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center flex-shrink-0">
                                <i class="pi pi-file-pdf text-red-500 text-lg" />
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">CAT 3 EXO Promotion Orders</p>
                                <p class="text-xs text-gray-400">{{ ordersAvailable ? 'Available for download' : 'Official document — issued upon board approval' }}</p>
                            </div>
                        </div>
                        <Button label="Download PDF" icon="pi pi-download" severity="success" size="small" :outlined="!ordersAvailable" :disabled="!ordersAvailable" />
                    </div>
                    <div class="flex items-center justify-between text-xs text-gray-500 px-1">
                        <span class="flex items-center gap-1"><i class="pi pi-calendar" /> Date Issued:</span>
                        <span class="font-medium" :class="ordersAvailable ? 'text-green-700' : 'text-gray-700'">
                            {{ ordersAvailable && trackedRecord?.scheduled_at ? formatDate(trackedRecord.scheduled_at) : 'Pending board approval' }}
                        </span>
                    </div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-gray-50 px-4 py-3 text-xs text-gray-500 flex items-center gap-2">
                    <i class="pi pi-info-circle text-gray-400" />
                    Promotion orders will be available for download once the board approves your application and orders are issued.
                </div>
            </div>

            <!-- ─── STEP 7: PERSONNEL RECORDS ────────────────────── -->
            <div v-else class="space-y-5">
                <div class="rounded-xl border bg-green-50 px-5 py-5 space-y-3" :class="completionStatus === 'success' ? 'border-green-300' : 'border-green-200'">
                    <div class="flex items-center gap-2 mb-1">
                        <i class="pi pi-check-circle text-xl" :class="completionStatus === 'success' ? 'text-green-600' : 'text-green-400'" />
                        <span class="text-sm font-semibold text-green-800">Personnel Records Update</span>
                        <Tag v-if="isTrackingMode" :severity="completionStatus" :value="completionLabel" class="ml-auto text-xs" />
                    </div>
                    <div class="space-y-2">
                        <div v-for="item in [
                                { icon:'pi-id-card', label:'Rank updated to CAT 3 EXO' },
                                { icon:'pi-folder',  label:'Personnel file updated with promotion records' },
                                { icon:'pi-trophy',  label:'Promotion to CAT 3 EXO completed successfully' },
                             ]"
                             :key="item.icon"
                             class="flex items-center gap-3 bg-white rounded-lg px-4 py-3 border border-green-100"
                        >
                            <i :class="['pi', item.icon, 'text-sm', completionStatus === 'success' ? 'text-green-500' : 'text-gray-400']" />
                            <span class="text-sm text-gray-700 flex-1">{{ item.label }}</span>
                            <Tag :severity="completionStatus" :value="completionLabel" class="text-xs" />
                        </div>
                    </div>
                </div>

                <!-- Summary: Checklist -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Step 1 — Documentation Checklist</h4>
                    <div v-for="item in [
                            { key:'seaDutyRecords', label:'Updated Sea Duty Records (CAT 2 CO compliance)' },
                            { key:'minResidency',   label:'Min. 6 Months Residency Aboard Ship' },
                            { key:'steamingHours',  label:'Min. 150 Hours Total Steaming Time' },
                         ]"
                         :key="item.key"
                         class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5">
                        <div class="flex items-center gap-2">
                            <i class="pi pi-check-circle text-sm text-green-500" />
                            <span class="text-sm text-gray-700">{{ item.label }}</span>
                        </div>
                        <Tag severity="success" value="Confirmed" class="text-xs" />
                    </div>
                </div>

                <!-- Summary: Documents -->
                <div>
                    <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest mb-3">Step 2 — Submitted Documents</h4>
                    <div v-for="(label, key) in { assignment_orders:'Updated Sea Duty Records', performance_eval:'Formal Promotion Request Letter', certification:'Supporting Certifications' }"
                         :key="key"
                         class="flex items-center justify-between py-2.5 px-3 rounded-lg border border-gray-100 bg-gray-50 mb-1.5">
                        <div class="flex items-center gap-2 min-w-0">
                            <i class="pi text-sm flex-shrink-0"
                               :class="selected[key] ? 'pi-check-circle text-green-500'
                                   : key === 'assignment_orders' ? 'pi-times-circle text-red-400'
                                   : 'pi-minus-circle text-gray-300'" />
                            <span class="text-sm text-gray-700 truncate">{{ label }}
                                <span v-if="key !== 'assignment_orders'" class="text-xs text-gray-400 ml-1">(optional)</span>
                            </span>
                        </div>
                        <span class="text-xs font-medium ml-4 flex-shrink-0"
                              :class="selected[key] ? 'text-gray-700'
                                  : key === 'assignment_orders' ? 'text-red-400 italic'
                                  : 'text-gray-400 italic'">
                            {{ selected[key]?.name ?? (key === 'assignment_orders' ? '— not selected —' : '— not provided —') }}
                        </span>
                    </div>
                </div>

                <!-- Remarks when tracking -->
                <div v-if="isTrackingMode && trackedRecord.remarks" class="rounded-lg border border-gray-200 bg-white px-4 py-3 space-y-1">
                    <p class="text-xs font-semibold text-gray-500 uppercase tracking-widest">Admin Remarks</p>
                    <p class="text-sm text-gray-700">{{ trackedRecord.remarks }}</p>
                </div>

                <!-- Submit notice -->
                <div v-if="!isTrackingMode" class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 flex items-start gap-2 text-sm text-amber-800">
                    <i class="pi pi-send text-amber-500 mt-0.5 flex-shrink-0" />
                    <span>Submitting will queue your application for <strong>board deliberation (Step 4)</strong>. After approval, promotion orders are issued <strong>(Step 6)</strong> and personnel records updated <strong>(Step 7)</strong>.</span>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex justify-between items-center px-6 py-4 border-t bg-gray-50">
            <Button label="Back" icon="pi pi-chevron-left" severity="secondary" outlined :disabled="isFirstStep" @click="prevStep" />
            <span class="text-xs text-gray-400">Step {{ currentStep + 1 }} of {{ STEPS.length }}</span>
            <Button v-if="!isLastStep" label="Next" icon="pi pi-chevron-right" icon-pos="right" :disabled="!currentStepValid" @click="nextStep" />
            <Button v-else-if="!isTrackingMode" label="Submit Application" icon="pi pi-send" icon-pos="right" severity="success" :loading="submitting" @click="submit" />
            <Button v-else label="Exit Tracking" icon="pi pi-times" icon-pos="right" severity="secondary" outlined @click="startNewApplication" />
        </div>
    </div>

    <!-- ══ MY APPLICATIONS TABLE ═════════════════════════════════ -->
    <div class="card">
        <div class="flex items-center justify-between mb-4">
            <span class="font-semibold text-lg text-gray-800">My CAT 3 EXO Applications</span>
            <IconField>
                <InputIcon><i class="pi pi-search" /></InputIcon>
                <InputText v-model="searchQuery" placeholder="Search by status or document…" @input="onSearch" />
            </IconField>
        </div>

        <DataTable
            :value="tableRecords"
            :loading="loadingTable"
            :row-class="(row) => row.id === trackedRecord?.id ? 'bg-indigo-50' : ''"
            lazy
            table-style="min-width: 60rem"
            striped-rows
        >
            <template #empty><div class="text-center py-6 text-gray-400">No CAT 3 EXO applications found.</div></template>
            <template #loading><div class="text-center py-6 text-gray-400">Loading…</div></template>

            <Column header="Sea Duty Records">
                <template #body="{ data }"><span class="text-sm">{{ data.assignment_orders?.name ?? '—' }}</span></template>
            </Column>
            <Column header="Request Letter">
                <template #body="{ data }"><span class="text-sm">{{ data.performance_eval?.name ?? '—' }}</span></template>
            </Column>
            <Column header="Certifications">
                <template #body="{ data }"><span class="text-sm">{{ data.certification?.name ?? '—' }}</span></template>
            </Column>
            <Column header="Scheduled At">
                <template #body="{ data }">
                    <span class="text-xs" :class="data.scheduled_at ? 'text-blue-600 font-medium' : 'text-gray-400'">{{ formatDate(data.scheduled_at) }}</span>
                </template>
            </Column>
            <Column field="status" header="Status" sortable>
                <template #body="{ data }"><Tag :severity="statusSeverity(data.status)" :value="statusLabel(data.status)" /></template>
            </Column>
            <Column header="Progress">
                <template #body="{ data }">
                    <div class="flex items-center gap-2">
                        <div class="w-20 bg-gray-100 rounded-full h-1.5 overflow-hidden flex-shrink-0">
                            <div class="h-1.5 rounded-full transition-all"
                                 :class="data.status === 'rejected' ? 'bg-red-400' : 'bg-blue-500'"
                                 :style="{ width: `${Math.round(((STATUS_STEP_MAP[data.status] ?? 0) / (STEPS.length - 1)) * 100)}%` }" />
                        </div>
                        <span class="text-xs text-gray-500 whitespace-nowrap">
                            Step {{ (STATUS_STEP_MAP[data.status] ?? 0) + 1 }}/{{ STEPS.length }}
                        </span>
                    </div>
                </template>
            </Column>
            <Column header="Remarks">
                <template #body="{ data }"><span class="text-sm text-gray-500 italic">{{ data.remarks ?? '—' }}</span></template>
            </Column>
            <Column header="Actions" style="width: 13rem">
                <template #body="{ data }">
                    <div class="flex items-center gap-1 flex-wrap">
                        <Button
                            :label="trackedRecord?.id === data.id ? 'Tracking' : 'Track'"
                            :icon="trackedRecord?.id === data.id ? 'pi pi-eye' : 'pi pi-map-marker'"
                            size="small"
                            :severity="trackedRecord?.id === data.id ? 'contrast' : 'info'"
                            text
                            @click="trackApplication(data)"
                        />
                        <Button
                            v-if="can(['delete cat3_exo'])"
                            icon="pi pi-trash"
                            label="Delete"
                            size="small"
                            severity="danger"
                            text
                            :disabled="data.status !== 'pending'"
                            v-tooltip.top="data.status !== 'pending' ? 'Only pending applications can be deleted' : ''"
                            @click="deleteRecord(data.id)"
                        />
                    </div>
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

<style scoped>
.fade-enter-active, .fade-leave-active { transition: opacity 0.25s ease, transform 0.25s ease; }
.fade-enter-from, .fade-leave-to       { opacity: 0; transform: translateY(-4px); }
</style>