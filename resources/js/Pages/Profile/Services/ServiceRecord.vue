<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';
import Divider from 'primevue/divider';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import { onMounted, reactive, ref } from "vue";
import Tag from 'primevue/tag';


import Create from './Create.vue'; // Modal form to create new Sea Duty Record

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    ranks: Object,
    seaDutyRecords: Array,
    vessels: Array,
});

const user = usePage().props.auth.user;


const statusSeverity = (status) => {
  switch (status) {
    case 'Pending':
      return 'warning';
    case 'Approved':
      return 'success';
    case 'Rejected':
      return 'danger';
    default:
      return null;
  }
};


const data = reactive({
    params: {
        createOpen: false,
        editOpen: false,
    },
    seaDutyRecords: [], // sample structure: { id, vessel, billet, date_from, date_to, authority, steaming_time, miles }
});

const title = 'Sea Duty Record';

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Service Records</h2>
            <p class="mt-1 text-sm text-gray-600">
                Update your Sea Duty Records.
            </p>
        </header>

        <div class="mt-4">
            <div class="card">
                <!-- Create Modal Component -->
                <Create
                    :show="data.createOpen"
                    @close="data.createOpen = false"
                    :title="title"
                    :user="user"
                    :vessels="vessels"
                />

                <DataTable
                    :value="props.seaDutyRecords"
                    tableStyle="min-width: 60rem"
                    dataKey="id"
                    :lazy="true"
                    paginator
                    :rows="5"
                    stripedRows
                >
                    <template #header>
                        <div class="flex justify-between items-center">
                            <span class="text-xl font-semibold">Sea Duty Records</span>
                            <Button class="ml-2" label="Add Record" @click="data.createOpen = true" icon="pi pi-plus" />
                        </div>
                    </template>

                    <template #empty>No sea duty records found.</template>
                    <template #loading>Loading sea duty records...</template>

                <Column header="Vessel">
                    <template #body="slotProps">
                        {{ slotProps.data.vessel?.vessel_name }}
                    </template>
                </Column>

                <Column header="Billet/Position">
                    <template #body="slotProps">
                        {{ slotProps.data.position?.name }}
                    </template>
                </Column>

                <Column header="Inclusive Dates">
                    <template #body="slotProps">
                        {{ slotProps.data.date_from }} - {{ slotProps.data.date_to }}
                    </template>
                </Column>
                <Column field="authority" header="Authority"></Column>
                <Column field="tst" header="Total Steaming Time"></Column>
                <Column field="tmc" header="Total Miles Covered"></Column>
                <Column header="Is Active">
                <template #body="slotProps">
                    <Tag
                    :value="slotProps.data.is_active ? 'Active' : 'Inactive'"
                    :severity="slotProps.data.is_active ? 'success' : 'secondary'"
                    />
                </template>
                </Column>
                <Column header="Status">
                    <template #body="slotProps">
                        <div>
                                <Tag
                                    :value="slotProps.data.status"
                                    :severity="statusSeverity(slotProps.data.status)"
                                />
                        </div>
                    </template>
                </Column>
                </DataTable>
            </div>
        </div>
    </section>
</template>
