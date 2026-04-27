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
    title: { type: String, default: "EXO Deliberation" },
    user:  { type: Object, required: true },
});

const toast = useToast();

/* ============================================================
   DROPDOWN DATA
============================================================ */
const juniorBilletCompletions = ref([]);
const slmcEndorsements        = ref([]);
const slmcOrders              = ref([]);
const slmcGraduationCerts     = ref([]);
const loadingIndex            = ref(false);

const fetchIndexData = async () => {
    loadingIndex.value = true;
    try {
        const { data } = await axios.get(route("exoDeliberations.index"), {
            headers: { Accept: "application/json" },
        });
        juniorBilletCompletions.value = data.juniorBilletCompletions ?? [];
        slmcEndorsements.value        = data.slmcEndorsements        ?? [];
        slmcOrders.value              = data.slmcOrders              ?? [];
        slmcGraduationCerts.value     = data.slmcGraduationCerts     ?? [];
    } catch {
        toast.add({
            severity: "error",
            summary:  "Load Failed",
            detail:   "Unable to load EXO Deliberation data.",
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
        const { data } = await axios.get(route("exoDeliberations.fetch"), {
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
   Maps to the 15-step process flow:
     Step 1  → Eligibility & Junior Billet (Steps 1–2)
     Step 2  → SLMC Endorsement & Orders   (Steps 3–8)
     Step 3  → EXO Application             (Steps 9–11)
     Step 4  → Review & Submit             (Steps 12–15)
============================================================ */
const STEPS = [
    { step: 1, label: "Junior Billet",       type: "junior_billet" },
    { step: 2, label: "SLMC Endorsement",    type: "slmc_endorsement" },
    { step: 3, label: "EXO Application",     type: "exo_application" },
    { step: 4, label: "Review & Submit",     type: "review" },
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
    junior_billet_completion: null,   // Step 1–2: Proof of Junior Billet completion
    slmc_endorsement:         null,   // Step 3–5: Approved SLMC Endorsement
    slmc_orders:              null,   // Step 7:   Orders to attend SLMC
    slmc_graduation_cert:     null,   // Step 9:   Proof of SLMC Graduation
});

const resetForm = () => {
    selected.value = {
        junior_billet_completion: null,
        slmc_endorsement:         null,
        slmc_orders:              null,
        slmc_graduation_cert:     null,
    };
};

/* ── Step 1: Junior Billet ── */
const step1Fields = computed(() => [
    {
        key:      "junior_billet_completion",
        label:    "Proof of Junior Billet Completion",
        hint:     "Certificate or official orders confirming completion of the Junior Billet is required before proceeding.",
        options:  juniorBilletCompletions.value,
        icon:     "pi pi-verified",
        required: true,
    },
]);

/* ── Step 2: SLMC Endorsement & Orders ── */
const step2Fields = computed(() => [
    {
        key:      "slmc_endorsement",
        label:    "Approved SLMC Endorsement",
        hint:     "The endorsement request for SLMC must have been reviewed and approved by the relevant authority.",
        options:  slmcEndorsements.value,
        icon:     "pi pi-file-check",
        required: true,
    },
    {
        key:      "slmc_orders",
        label:    "Orders to Attend SLMC",
        hint:     "Official orders issued after slot confirmation on a first come, first serve basis.",
        options:  slmcOrders.value,
        icon:     "pi pi-file-edit",
        required: true,
    },
]);

/* ── Step 3: EXO Application ── */
const step3Fields = computed(() => [
    {
        key:      "slmc_graduation_cert",
        label:    "SLMC Graduation Certificate",
        hint:     "Proof of successful graduation from the Senior Leadership and Management Course (SLMC) is mandatory.",
        options:  slmcGraduationCerts.value,
        icon:     "pi pi-graduation-cap",
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

const step3Valid = computed(() =>
    step3Fields.value.filter(f => f.required).every(f => selected.value[f.key] !== null)
);

const currentStepValid = computed(() => {
    if (currentStep.value === 0) return step1Valid.value;
    if (currentStep.value === 1) return step2Valid.value;
    if (currentStep.value === 2) return step3Valid.value;
    return true;
});

/* ============================================================
   SUBMIT
============================================================ */
const submitting = ref(false);

const submit = async () => {
    submitting.value = true;
    try {
        await axios.post(route("exoDeliberations.store"), {
            user_id:                      props.user.id,
            junior_billet_completion_id:  selected.value.junior_billet_completion?.id ?? null,
            slmc_endorsement_id:          selected.value.slmc_endorsement?.id         ?? null,
            slmc_orders_id:               selected.value.slmc_orders?.id              ?? null,
            slmc_graduation_cert_id:      selected.value.slmc_graduation_cert?.id     ?? null,
        });

        toast.add({
            severity: "success",
            summary:  "Submitted",
            detail:   "EXO Deliberation application submitted successfully.",
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
const deleteRecord = async (id) => {
    if (!confirm("Delete this EXO Deliberation application?")) return;

    await axios.delete(route("exoDeliberations.destroy", id));

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
    const map = {
        pending:  "warn",
        approved: "success",
        rejected: "danger",
    };
    return map[status] ?? "secondary";
};

/* Step description copy */
const stepDescription = computed(() => {
    const descriptions = {
        junior_billet:    "Verify eligibility by confirming completion of the Junior Billet. Provide the certificate or official orders as proof before proceeding.",
        slmc_endorsement: "Select your approved SLMC Endorsement and the official orders issued for attending the Senior Leadership and Management Course.",
        exo_application:  "Attach your SLMC Graduation Certificate as proof of successful completion. Your application will then be submitted for board deliberation.",
        review:           "Review all selected documents carefully. Once submitted, your application will await board deliberation for EXO Billet assignment.",
    };
    return descriptions[activeStep.value.type] ?? "";
});
</script>

<template>
    <div class="space-y-6">

        <!-- ══ PROCESS FLOW BANNER ═══════════════════════════════ -->
        <div class="rounded-xl border border-blue-100 bg-blue-50 px-5 py-4 flex items-start gap-3">
            <i class="pi pi-info-circle text-blue-500 text-lg mt-0.5 flex-shrink-0" />
            <div class="text-sm text-blue-800 leading-relaxed">
                <span class="font-semibold">EXO Deliberation Process:</span>
                Complete your Junior Billet → Obtain SLMC Endorsement & Orders →
                Graduate from SLMC → Apply for EXO Billet → Await Board Deliberation → Receive Orders.
            </div>
        </div>

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
                <p class="text-sm text-gray-500 mb-6">{{ stepDescription }}</p>

                <!-- Loading state -->
                <div v-if="loadingIndex" class="flex items-center justify-center py-8 text-gray-400">
                    <i class="pi pi-spin pi-spinner text-2xl mr-2" />
                    <span class="text-sm">Loading your documents…</span>
                </div>

                <!-- ── STEP 1: Junior Billet Completion ── -->
                <div v-else-if="activeStep.type === 'junior_billet'" class="space-y-4">

                    <!-- Process reference badge -->
                    <div class="flex gap-2 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 font-medium">
                            <i class="pi pi-list-check text-xs" /> Process Steps 1–2
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2.5 py-0.5 text-xs text-amber-700 font-medium">
                            <i class="pi pi-exclamation-triangle text-xs" /> Eligibility Requirement
                        </span>
                    </div>

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
                                    No approved Junior Billet records found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected[field.key]" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected[field.key].name }}</span>
                        </div>
                    </div>
                </div>

                <!-- ── STEP 2: SLMC Endorsement & Orders ── -->
                <div v-else-if="activeStep.type === 'slmc_endorsement'" class="space-y-4">

                    <div class="flex gap-2 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 font-medium">
                            <i class="pi pi-list-check text-xs" /> Process Steps 3–8
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-0.5 text-xs text-blue-700 font-medium">
                            <i class="pi pi-clock text-xs" /> Slot availability: First come, first serve
                        </span>
                    </div>

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

                <!-- ── STEP 3: EXO Application (SLMC Graduation) ── -->
                <div v-else-if="activeStep.type === 'exo_application'" class="space-y-4">

                    <div class="flex gap-2 flex-wrap mb-2">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-0.5 text-xs text-gray-600 font-medium">
                            <i class="pi pi-list-check text-xs" /> Process Steps 9–11
                        </span>
                        <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-0.5 text-xs text-green-700 font-medium">
                            <i class="pi pi-graduation-cap text-xs" /> SLMC Graduate
                        </span>
                    </div>

                    <div
                        v-for="field in step3Fields"
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
                                    No SLMC Graduation Certificates found for your account.
                                </div>
                            </template>
                        </Select>
                        <div v-if="selected[field.key]" class="mt-2 flex items-center gap-1 text-xs text-green-700">
                            <i class="pi pi-check-circle" />
                            <span class="font-medium">{{ selected[field.key].name }}</span>
                        </div>
                    </div>

                    <!-- Deliberation notice -->
                    <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-3 flex items-start gap-2 text-sm text-blue-800">
                        <i class="pi pi-users text-blue-500 mt-0.5 flex-shrink-0" />
                        <span>
                            After submission, your application will await the convening of the board or
                            panel for EXO Billet deliberation. <span class="font-semibold">(Process Steps 12–15)</span>
                        </span>
                    </div>
                </div>

                <!-- ── STEP 4: Review & Submit ── -->
                <div v-else class="space-y-5">

                    <!-- Section: Junior Billet -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                                Junior Billet
                            </h4>
                            <span class="text-xs text-gray-400">(Steps 1–2)</span>
                        </div>
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

                    <!-- Section: SLMC Endorsement & Orders -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                                SLMC Endorsement &amp; Orders
                            </h4>
                            <span class="text-xs text-gray-400">(Steps 3–8)</span>
                        </div>
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
                                {{ selected[field.key]?.name ?? '— not selected —' }}
                            </span>
                        </div>
                    </div>

                    <!-- Section: EXO Application -->
                    <div>
                        <div class="flex items-center gap-2 mb-3">
                            <h4 class="text-xs font-semibold text-gray-500 uppercase tracking-widest">
                                EXO Application
                            </h4>
                            <span class="text-xs text-gray-400">(Steps 9–11)</span>
                        </div>
                        <div
                            v-for="field in step3Fields"
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

                    <!-- Submission notice -->
                    <div class="rounded-lg border border-amber-100 bg-amber-50 px-4 py-3 flex items-start gap-2 text-sm text-amber-800">
                        <i class="pi pi-send text-amber-500 mt-0.5 flex-shrink-0" />
                        <span>
                            Upon submission, your application will be queued for board deliberation
                            <span class="font-semibold">(Step 12)</span>. The board will review your
                            qualifications and issue orders for EXO Billet reassignment if approved
                            <span class="font-semibold">(Steps 13–15)</span>.
                        </span>
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
                    label="Submit for Deliberation"
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
                <span class="font-semibold text-lg text-gray-800">My EXO Deliberation Applications</span>
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
                        No EXO Deliberation applications found.
                    </div>
                </template>
                <template #loading>
                    <div class="text-center py-6 text-gray-400">Loading…</div>
                </template>

                <Column header="Junior Billet Completion">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.junior_billet_completion?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="SLMC Endorsement">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.slmc_endorsement?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="SLMC Orders">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.slmc_orders?.name ?? '—' }}</span>
                    </template>
                </Column>
                <Column header="SLMC Graduation Cert.">
                    <template #body="{ data }">
                        <span class="text-sm">{{ data.slmc_graduation_cert?.name ?? '—' }}</span>
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
                            v-if="can(['delete exo_deliberation'])"
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