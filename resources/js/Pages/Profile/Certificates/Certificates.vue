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
const toast = useToast();

const products = [
            {
                id: '1000',
                code: 'f230fh0g3',
                name: 'Bamboo Watch',
                description: 'Product Description',
                image: 'bamboo-watch.jpg',
                price: 65,
                category: 'Accessories',
                quantity: 24,
                inventoryStatus: 'INSTOCK',
                rating: 5,
                orders: [
                    {
                        id: '1000-0',
                        productCode: 'f230fh0g3',
                        date: '2020-09-13',
                        amount: 65,
                        quantity: 1,
                        customer: 'David James',
                        status: 'PENDING'
                    },
                    {
                        id: '1000-1',
                        productCode: 'f230fh0g3',
                        date: '2020-05-14',
                        amount: 130,
                        quantity: 2,
                        customer: 'Leon Rodrigues',
                        status: 'DELIVERED'
                    },
                    {
                        id: '1000-2',
                        productCode: 'f230fh0g3',
                        date: '2019-01-04',
                        amount: 65,
                        quantity: 1,
                        customer: 'Juan Alejandro',
                        status: 'RETURNED'
                    },
                    {
                        id: '1000-3',
                        productCode: 'f230fh0g3',
                        date: '2020-09-13',
                        amount: 195,
                        quantity: 3,
                        customer: 'Claire Morrow',
                        status: 'CANCELLED'
                    }
                ]
            },
            
            
            {
                id: '1003',
                code: '244wgerg2',
                name: 'Blue T-Shirt',
                description: 'Product Description',
                image: 'blue-t-shirt.jpg',
                price: 29,
                category: 'Clothing',
                quantity: 25,
                inventoryStatus: 'INSTOCK',
                rating: 5,
                orders: []
            },
            
            {
                id: '1005',
                code: 'av2231fwg',
                name: 'Brown Purse',
                description: 'Product Description',
                image: 'brown-purse.jpg',
                price: 120,
                category: 'Accessories',
                quantity: 0,
                inventoryStatus: 'OUTOFSTOCK',
                rating: 4,
                orders: [
                    {
                        id: '1005-0',
                        productCode: 'av2231fwg',
                        date: '2020-01-25',
                        amount: 120,
                        quantity: 1,
                        customer: 'Isabel Sinclair',
                        status: 'RETURNED'
                    },
                    {
                        id: '1005-1',
                        productCode: 'av2231fwg',
                        date: '2019-03-12',
                        amount: 240,
                        quantity: 2,
                        customer: 'Lionel Clifford',
                        status: 'DELIVERED'
                    },
                    {
                        id: '1005-2',
                        productCode: 'av2231fwg',
                        date: '2019-05-05',
                        amount: 120,
                        quantity: 1,
                        customer: 'Cody Chavez',
                        status: 'DELIVERED'
                    }
                ]
            },
        ];
    
const formatCurrency = (value) => {
    return value.toLocaleString('en-US', { style: 'currency', currency: 'USD' });
};

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Training & Certification</h2>

        </header>

        <div class="mt-2 space-y-2">
            <!-- <PrimaryButton 
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
            </PrimaryButton>                                                                                 -->
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
                <DataTable v-model:expandedRows="expandedRows" :value="products" dataKey="id" tableStyle="min-width: 60rem">
                <Column expander style="width: 5rem" />
                <Column field="name" header="Name"></Column>
                <Column header="Image">
                    <template #body="slotProps">
                        <img :src="`https://primefaces.org/cdn/primevue/images/product/${slotProps.data.image}`" :alt="slotProps.data.image" class="shadow-lg" width="64" />
                    </template>
                </Column>
                <Column field="price" header="Price">
                    <template #body="slotProps">
                        {{ formatCurrency(slotProps.data.price) }}
                    </template>
                </Column>
                <Column field="category" header="Category"></Column>
                <Column field="rating" header="Reviews">
                    <template #body="slotProps">
                        <Rating :modelValue="slotProps.data.rating" readonly />
                    </template>
                </Column>
                <template #expansion="slotProps">
                    <div class="p-4">
                        <h5>Orders for {{ slotProps.data.name }}</h5>
                        <DataTable :value="slotProps.data.orders">
                            <Column field="id" header="Id" sortable></Column>
                            <Column field="customer" header="Customer" sortable></Column>
                            <Column field="date" header="Date" sortable></Column>
                            <Column headerStyle="width:4rem">
                                <template #body>
                                    <Button icon="pi pi-search" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>
                </template>
            </DataTable>
    </div>
        </div>

    </section>
</template>
