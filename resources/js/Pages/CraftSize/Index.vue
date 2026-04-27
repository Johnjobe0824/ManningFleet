
<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import Create from "@/Pages/CraftSize/Create.vue";
import Edit from "@/Pages/CraftSize/Edit.vue";
import { usePage, useForm } from '@inertiajs/vue3';
import ConfirmDialog from "primevue/confirmdialog";
import { onMounted, reactive, ref, watch, computed } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
const { _, debounce, pickBy } = pkg;
import { loadToast } from '@/composables/loadToast';
import FilePreview from "@/Components/FilePreview.vue";
import { useConfirm } from 'primevue/useconfirm';
import { useToast } from 'primevue/usetoast';

const props = defineProps({
    title: String,
    filters: Object,
    craft_sizes: Object,
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
    craft_size: null,
});

const selectedRowId = ref(null);


const deleteData = () => {

    deleteDialog.value = false;
    
    form.delete(route("craft_size.destroy", data.craft_size?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
}


const onPageChange = (event) => {
    router.get(route('craft_size.index'), { page: event.page + 1 }, { preserveState: true });
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("craft_size.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

const startEditing = (rowData) => {
  selectedRowId.value = rowData.id;
  data.craft_size = rowData;
};


</script>

<template>
    <app-layout>  
        <Toast />
        <ConfirmDialog />           
        <div class="card"> 
            <div class="font-semibold text-xl mb-4">Craft Size Management</div>           
            <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="props.title"
                :permissions="props.permissions"
            />
            <Edit
                :show="data.editOpen"
                @close="data.editOpen = false"
                :craft_size="data.craft_size"
                :title="props.title"
            />
            <Button v-show="can(['create craft_size'])" label="Create" @click="data.createOpen = true" icon="pi pi-plus" />
            <DataTable lazy :value="craft_sizes.data" paginator  :rows="craft_sizes.per_page" :totalRecords="craft_sizes.total" :first="(craft_sizes.current_page - 1) * craft_sizes.per_page"   @page="onPageChange"  tableStyle="min-width: 50rem">
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

                <Column header="No">
                    <template #body="slotProps">
                        {{slotProps.index + 1}}
                    </template>
                </Column>

                <Column field="code" header="Code"></Column>
                <Column header="Description" field="description"></Column>
                <Column :exportable="false" style="min-width: 12rem">
                     <template #body="slotProps">
                        <Button v-show="can(['update craft_size'])" icon="pi pi-pencil" outlined rounded class="mr-2"  @click="
                                                    (data.editOpen = true), (data.craft_size = slotProps.data)" />
                        <Button v-show="can(['delete craft_size'])" icon="pi pi-trash" outlined rounded severity="danger" @click="deleteDialog = true; data.craft_size = slotProps.data" />
                    </template>
                </Column>
            </DataTable>
            

            <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.craft_size"
                        >Are you sure you want to delete <b>{{ data.craft_size.code }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button label="No" icon="pi pi-times" text @click="deleteDialog = false" />
                    <Button label="Yes" icon="pi pi-check" @click="deleteData()" />
                </template>
            </Dialog>

            <!-- <Dialog v-model:visible="permissionDialog" modal :header="'Permission ' + data.craft_sizes?.name" :style="{ width: '50rem' }">
                <div class="grid grid-cols-2 sm:grid-cols-3">
                    <div
                        v-for="(permission, index) in data.craft_size?.permissions"
                        :key="index"
                        class="flex justify-between w-full px-4 py-2"
                    >
                        {{ ++index + ". " + permission.name }}
                    </div>
                </div>
            </Dialog> -->
        </div>
    </app-layout>
</template>

<style scoped lang="scss">

</style>
