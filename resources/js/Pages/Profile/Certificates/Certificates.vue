<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage} from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';
import Divider from 'primevue/divider';
import TreeTable from 'primevue/treetable';
import DataTable from 'primevue/datatable';
import { onMounted, reactive, ref, watch, computed} from "vue";
import { useToast } from 'primevue/usetoast';

// import { NodeService } from '@/service/NodeService';
import { NodeService } from '@/sakai/service/NodeService';

import Create from './Create.vue';


const user = usePage().props.auth.user;

const certificates = computed(() => usePage().props.certificates);

const form = useForm({});

const toast = useToast();

const data = reactive({
    params: {
        // search: props.filters.search,
        // field: props.filters.field,
        // order: props.filters.order,
        createOpen: false,
        editOpen: false,
    },
    certificate: null,
});


    
const statusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'warn';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'warning';
    }
};

const statusBorderClass = (status) => {
    switch (status) {
        case 'approved': return 'border-green-500';
        case 'rejected': return 'border-red-500';
        case 'pending': return 'border-yellow-500';
        default: return 'border-gray-300';
    }
};

const title = 'Training and Certicate'
</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Training & Certification</h2>

        </header>

        <div class="mt-2 space-y-2">

            <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="title"
                :user="user"
            />
            <div class="card">
            <DataTable lazy :value="certificates" tableStyle="min-width: 50rem">
                <template #header>
                    
                    <div class="flex justify-end">
                        <!-- <IconField>
                            <InputIcon>
                                <i class="pi pi-search" />
                            </InputIcon>
                            <InputText v-model="data.params.search" placeholder="Keyword Search" />
                        </IconField> -->
                        <Button class="ml-2" label="Create" @click="data.createOpen = true" icon="pi pi-plus" />
                    </div>
                </template>
                <template #empty> No data found. </template>
                <template #loading> Loading data. Please wait. </template>

                <Column field="training_center" header="Training Center"></Column>
                <Column field="name" header="Training"></Column>
                <Column field="batch_no" header="Batch Number"></Column>
                <Column field="date" header="Date Completed"></Column>
            </DataTable>
    </div>
        </div>

    </section>
</template>
