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

// import { NodeService } from '@/service/NodeService';
import { NodeService } from '@/sakai/service/NodeService';

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    ranks: Object
});

const user = usePage().props.auth.user;

const data = reactive({
    params: {
        createOpen: false,
        editOpen: false,

    },
    permission: null,
});

// const products = ref();
const expandedRows = ref({});
// const toast = useToast();

const sea_duty_records = [
            {
                id: '1000',
                code: 'f230fh0g3',
                vessel_name: 'FPB-2401',
                date_from: "12/01/2001",
                date_to: "12/01/2002",
                tmc: '5,843.1 NM',
                authority_no: "SHIP ORDER NR 001 DATED 21 SEP 2020",
                position_code: "DO1"
                
            },
            
            {
                id: '1001',
                code: 'f330fh0g3',
                vessel_name: 'FPB-2401',
                date_from: "12/01/2022",
                date_to: "12/01/2022",
                tmc: '5,843.1 NM',
                authority_no: "SHIP ORDER NR 001 DATED 21 APR 2022",
                position_code: "DO2"
            },
            
        ];
    
const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Service Records</h2>

            <!-- <p v-if="user.personal_info && Object.keys(user.personal_info).length > 0" class="mt-1 text-sm text-gray-600">

                Update your personal informations.
            </p> -->
        </header>

        <div class="mt-2 space-y-2">
            <PrimaryButton 
                v-if="!user.personal_info || user.personal_info.length === 0" 
                @click="data.createOpen = true"
            >
                Add Personal Info
            </PrimaryButton>

            <PrimaryButton 
                v-else 
                @click="data.editOpen = true"
            >
                Edit Personal Info
            </PrimaryButton>                                                                                
<!-- 
            <AddUserInformationForm
                :show="data.createOpen"
                :ranks="ranks"
                @close="data.createOpen = false"
            />
            
            <EditUserInformationForm
            :show="data.editOpen"
            :ranks="ranks"
            @close="data.editOpen = false"
            /> -->
            <div class="card">
                <DataTable v-model:expandedRows="expandedRows" :value="sea_duty_records" dataKey="id" tableStyle="min-width: 60rem">
                    <Column expander style="width: 5rem" />
                    <Column field="vessel_name" header="Vessel"></Column>
                    <Column field="position_code" header="Billet"></Column>
                    <Column field="date_from" header="From"></Column>
                    <Column field="date_to" header="To"></Column>

                    
                </DataTable>
            </div>
        </div>

    </section>
</template>
