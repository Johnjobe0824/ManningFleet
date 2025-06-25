<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import Create from "@/Pages/ChipsReport/Create.vue";
import { usePage, useForm } from "@inertiajs/vue3";
import ConfirmDialog from "primevue/confirmdialog";
import { onMounted, reactive, ref, watch, computed } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
const { _, debounce, pickBy } = pkg;
import { loadToast } from "@/composables/loadToast";
import FilePreview from "@/Components/FilePreview.vue";
import { useConfirm } from "primevue/useconfirm";
import { useToast } from "primevue/usetoast";

const props = defineProps({
    title: String,
    filters: Object,
    chipsReports: Object,
    perPage: Number,
});

loadToast();

const confirm = useConfirm();
const toast = useToast();

const deleteDialog = ref(false);
const permissionDialog = ref(false);
const form = useForm({});
const previewRef = ref(null);

// Preview handler
const previewChipsReport = (path, name) => {
    if (previewRef.value) {
        previewRef.value.show(`/storage/${path}`, name);
    }
};

const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        createOpen: false,
        editOpen: false,
    },
    chipsReport: null,
});

const selectedRowId = ref(null);

const types = [
    {
        id: 1,
        name: "Officer",
    },
    {
        id: 2,
        name: "Non-Officer",
    },
    {
        id: 3,
        name: "NUP-Civilian",
    },
];

const statusOptions = [
    { label: "Pending", value: "pending" },
    { label: "Approved", value: "approved" },
    { label: "Rejected", value: "rejected" },
];

const statusSeverity = (status) => {
    switch (status) {
        case "pending":
            return "warn";
        case "approved":
            return "success";
        case "rejected":
            return "danger";
        default:
            return "warning";
    }
};

const deleteData = () => {
    deleteDialog.value = false;

    form.delete(route("chipsReport.destroy", data.chipsReport?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

const onPageChange = (event) => {
    router.get(
        route("chipsReport.index"),
        { page: event.page + 1 },
        { preserveState: true },
    );
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("chipsReport.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150),
);

const startEditing = (rowData) => {
    selectedRowId.value = rowData.id;
};

const confirmStatusUpdate = (rowData) => {
    confirm.require({
        message: `Are you sure you want to update the status to "${rowData.status}"?`,
        header: "Confirm Status Change",
        icon: "pi pi-exclamation-triangle",
        acceptLabel: "Yes",
        rejectLabel: "No",
        accept: () => {
            updateStatus(rowData);
        },
        reject: () => {
            // Optional: reset status if cancelled
        },
    });
};

const updateStatus = async (rowData) => {
    try {
        await axios.patch(route("chipsReports.updateStatus", rowData.id), {
            status: rowData.status,
        });

        toast.add({
            severity: "success",
            summary: "Status Updated",
            detail: `Status set to ${rowData.status}`,
            life: 3000,
        });

        selectedRowId.value = null;
    } catch (error) {
        toast.add({
            severity: "error",
            summary: "Update Failed",
            detail: error.response?.data?.message || "Could not update status.",
            life: 3000,
        });
    }
};
</script>

<template>
    <app-layout>
        <Toast />
        <ConfirmDialog />
        <div class="card">
            <div class="font-semibold text-xl mb-4">
                Chips Report Management
            </div>
            <!-- <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="props.title"
                :permissions="props.permissions"
            />
            <Edit
                :show="data.editOpen"
                @close="data.editOpen = false"
                :chipsReport="data.chipsReport"
                :title="props.title"
            /> -->
            <!-- <Button v-show="can(['create chipsReport'])" label="Create" @click="data.createOpen = true" icon="pi pi-plus" /> -->
            <DataTable
                lazy
                :value="chipsReports.data"
                paginator
                :rows="chipsReports.per_page"
                :totalRecords="chipsReports.total"
                :first="(chipsReports.current_page - 1) * chipsReports.per_page"
                @page="onPageChange"
                tableStyle="min-width: 50rem"
            >
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText
                                v-model="data.params.search"
                                placeholder="Keyword Search"
                            />
                        </IconField>
                    </div>
                </template>
                <template #empty> No data found. </template>
                <template #loading> Loading data. Please wait. </template>

                <Column header="No">
                    <template #body="slotProps">
                        {{ slotProps.index + 1 }}
                    </template>
                </Column>

                <Column field="name" header="Name"></Column>
                <Column header="Officer Name">
                    <template #body="slotProps">
                        {{ slotProps.data.user?.name }}
                    </template>
                </Column>
                <Column header="File">
                    <template #body="slotProps">
                        <Button
                            icon="pi pi-eye"
                            label="Preview"
                            @click="
                                previewChipsReport(
                                    slotProps.data.path,
                                    slotProps.data.name,
                                )
                            "
                            outlined
                            size="small"
                        />
                    </template>
                </Column>

                <Column header="Status">
                    <template #body="slotProps">
                        <div>
                            <div v-if="slotProps.data.id === selectedRowId">
                                <Dropdown
                                    v-model="slotProps.data.status"
                                    :options="statusOptions"
                                    optionLabel="label"
                                    optionValue="value"
                                    @change="
                                        confirmStatusUpdate(slotProps.data)
                                    "
                                    class="w-full"
                                />
                            </div>
                            <div v-else>
                                <Tag
                                    :value="slotProps.data.status"
                                    :severity="
                                        statusSeverity(slotProps.data.status)
                                    "
                                />
                            </div>
                        </div>
                    </template>
                </Column>
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button
                            v-show="can(['update chipsReport'])"
                            icon="pi pi-pencil"
                            rounded
                            class="mr-2"
                            @click="startEditing(slotProps.data)"
                            outlined
                        />
                    </template>
                </Column>
            </DataTable>

            <Dialog
                v-model:visible="deleteDialog"
                :style="{ width: '450px' }"
                header="Confirm"
                :modal="true"
            >
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.chipsReport"
                        >Are you sure you want to delete
                        <b>{{ data.chipsReport.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button
                        label="No"
                        icon="pi pi-times"
                        text
                        @click="deleteDialog = false"
                    />
                    <Button
                        label="Yes"
                        icon="pi pi-check"
                        @click="deleteData()"
                    />
                </template>
            </Dialog>

            <!-- <Dialog v-model:visible="permissionDialog" modal :header="'Permission ' + data.chipsReports?.name" :style="{ width: '50rem' }">
                <div class="grid grid-cols-2 sm:grid-cols-3">
                    <div
                        v-for="(permission, index) in data.chipsReport?.permissions"
                        :key="index"
                        class="flex justify-between w-full px-4 py-2"
                    >
                        {{ ++index + ". " + permission.name }}
                    </div>
                </div>
            </Dialog> -->
        </div>
        <FilePreview ref="previewRef" />
    </app-layout>
</template>

<style scoped lang="scss"></style>
