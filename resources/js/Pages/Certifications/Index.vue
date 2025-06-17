
<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import Create from "@/Pages/Certifications/Create.vue";
import Edit from "@/Pages/Certifications/Edit.vue";
import { usePage, useForm } from '@inertiajs/vue3';

import { onMounted, reactive, ref, watch, computed } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
const { _, debounce, pickBy } = pkg;
import { loadToast } from '@/composables/loadToast';

import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    title: String,
    filters: Object,
    // permissions: Object,
    certificates: Object,
    perPage: Number,
});

loadToast();

const confirm = useConfirm();
const toast = useToast();

const deleteDialog = ref(false);
const permissionDialog = ref(false);
const form = useForm({});

const data = reactive({
    params: {
        search: props.filters.search,
        field: props.filters.field,
        order: props.filters.order,
        createOpen: false,
        editOpen: false,
    },
    certificate: null,
});


const deleteData = () => {

    deleteDialog.value = false;
    
    form.delete(route("certificate.destroy", data.certificate?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
}

const statusOptions = [
  { label: 'Pending', value: 'pending' },
  { label: 'Approved', value: 'approved' },
  { label: 'Rejected', value: 'rejected' },
];

const statusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'warn';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'warning';
    }
};

const onPageChange = (event) => {
    router.get(route('certificate.index'), { page: event.page + 1 }, { preserveState: true });
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("certificate.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

const selectedRowId = ref(null);

const startEditing = (rowData) => {
  selectedRowId.value = rowData.id;
};

const confirmStatusUpdate = (rowData) => {
  confirm.require({
    message: `Are you sure you want to update the status to "${rowData.status}"?`,
    header: 'Confirm Status Change',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Yes',
    rejectLabel: 'No',
    accept: () => {
      updateStatus(rowData);
    },
    reject: () => {
      // Optional: reset status if cancelled
    }
  });
};

const updateStatus = async (rowData) => {
  try {
    await axios.patch(route('certificates.updateStatus', rowData.id), {
      status: rowData.status,
    });

    toast.add({
      severity: 'success',
      summary: 'Status Updated',
      detail: `Status set to ${rowData.status}`,
      life: 3000,
    });

    selectedRowId.value = null;
  } catch (error) {
    toast.add({
      severity: 'error',
      summary: 'Update Failed',
      detail: error.response?.data?.message || 'Could not update status.',
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
            <div class="font-semibold text-xl mb-4">Training and Certification Management</div>           
            <!-- <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="props.title"
                :permissions="props.permissions"
            />
            <Edit
                :show="data.editOpen"
                @close="data.editOpen = false"
                :certificate="data.certificate"
                :title="props.title"
            /> -->
            <!-- <Button v-show="can(['create certificate'])" label="Create" @click="data.createOpen = true" icon="pi pi-plus" /> -->
            <DataTable lazy :value="certificates.data" paginator  :rows="certificates.per_page" :totalRecords="certificates.total" :first="(certificates.current_page - 1) * certificates.per_page"   @page="onPageChange"  tableStyle="min-width: 50rem">
                
                <template #header>
                    <div class="flex justify-end">
                        <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="data.params.search" placeholder="Keyword Search" />
                        </IconField>
                    </div>
                </template>
                <template #empty> No data found. </template>
                <template #loading> Loading data. Please wait. </template>

                <Column field="training_center" header="Training Center"></Column>
                <Column field="name" header="Training"></Column>
                <Column field="batch_no" header="Batch Number"></Column>
                <Column field="date" header="Date Completed"></Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <div>
                            <div v-if="slotProps.data.id === selectedRowId">
                            <Dropdown
                                v-model="slotProps.data.status"
                                :options="statusOptions"
                                optionLabel="label"
                                optionValue="value"
                                @change="confirmStatusUpdate(slotProps.data)"
                                class="w-full"
                            />
                            </div>
                            <div v-else>
                            <Tag 
                                :value="slotProps.data.status" 
                                :severity="statusSeverity(slotProps.data.status)" 
                            />
                            </div>
                        </div>
                        </template>
                    </Column>   
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button 
                            v-show="can(['update document'])" 
                            icon="pi pi-pencil" 
                            rounded class="mr-2" 
                            @click="startEditing(slotProps.data)" 
                            outlined 
                        />
                    </template>
                </Column>
                
            </DataTable>

            <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.certificate"
                        >Are you sure you want to delete <b>{{ data.certificate.name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button label="No" icon="pi pi-times" text @click="deleteDialog = false" />
                    <Button label="Yes" icon="pi pi-check" @click="deleteData()" />
                </template>
            </Dialog>

        </div>
    </app-layout>
</template>

<style scoped lang="scss">

</style>
