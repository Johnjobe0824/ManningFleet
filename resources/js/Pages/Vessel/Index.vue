
<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import Create from "@/Pages/Vessel/Create.vue";
import Edit from "@/Pages/Vessel/Edit.vue";
import { usePage, useForm } from '@inertiajs/vue3';

import { onMounted, reactive, ref, watch, computed } from "vue";
import pkg from "lodash";
import { router } from "@inertiajs/vue3";
const { _, debounce, pickBy } = pkg;
import { loadToast } from '@/composables/loadToast';

const props = defineProps({
    title: String,
    filters: Object,
    // permissions: Object,
    vessels: Object,
    perPage: Number,
});

loadToast();

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
    vessel: null,
});

const getSeverity = (vessel) => {
    switch (vessel.status) {
        case 'Ready For Sea':
            return 'success';

        case 'Not Ready For Sea':
            return 'danger';

        default:
            return null;
    }
};


const deleteData = () => {

    deleteDialog.value = false;
    
    form.delete(route("vessel.destroy", data.vessel?.id), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
}


const onPageChange = (event) => {
    router.get(route('vessel.index'), { page: event.page + 1 }, { preserveState: true });
};

watch(
    () => _.cloneDeep(data.params),
    debounce(() => {
        let params = pickBy(data.params);
        router.get(route("vessel.index"), params, {
            replace: true,
            preserveState: true,
            preserveScroll: true,
        });
    }, 150)
);

</script>

<template>
    <app-layout>             
        <div class="card"> 
            <div class="font-semibold text-xl mb-4">Vessel Management</div>             
            <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="props.title"
                :permissions="props.permissions"
            />
            <Edit
                :show="data.editOpen"
                @close="data.editOpen = false"
                :vessel="data.vessel"
                :title="props.title"
            />
            <Button v-show="can(['create vessel'])" label="Create" @click="data.createOpen = true" icon="pi pi-plus" />
            <DataTable lazy :value="vessels.data" paginator  :rows="vessels.per_page" :totalRecords="vessels.total" :first="(vessels.current_page - 1) * vessels.per_page"   @page="onPageChange"  tableStyle="min-width: 50rem">
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

                <Column field="vessel_name" header="Vessel Name"></Column>
                <Column field="vessel_bow_no" header="Vessel Bow No"></Column>
                <Column field="category" header="Category"></Column>
                <Column field="size" header="Size"></Column>
                <!-- <Column field="status" header="Status"></Column> -->
                <Column header="Status">
                    <template #body="slotProps">
                        <Tag :value="slotProps.data.status" :severity="getSeverity(slotProps.data)" />
                    </template>
                </Column>
                <!-- <Column field="created_at" header="Created"></Column>
                <Column field="updated_at" header="Updated"></Column> -->
                <Column :exportable="false" style="min-width: 12rem">
                    <template #body="slotProps">
                        <Button v-show="can(['update vessel'])" icon="pi pi-pencil" outlined rounded class="mr-2"  @click="
                                                    (data.editOpen = true),
                                                        (data.vessel = slotProps.data)" />
                        <Button v-show="can(['delete vessel'])" icon="pi pi-trash" outlined rounded severity="danger" @click="deleteDialog = true; data.vessel = slotProps.data" />
                    </template>
                </Column>
            </DataTable>

            <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                <div class="flex items-center gap-4">
                    <i class="pi pi-exclamation-triangle !text-3xl" />
                    <span v-if="data.vessel"
                        >Are you sure you want to delete <b>{{ data.vessel.vessel_name }}</b
                        >?</span
                    >
                </div>
                <template #footer>
                    <Button label="No" icon="pi pi-times" text @click="deleteDialog = false" />
                    <Button label="Yes" icon="pi pi-check" @click="deleteData()" />
                </template>
            </Dialog>

            <!-- <Dialog v-model:visible="permissionDialog" modal :header="'Permission ' + data.vessels?.name" :style="{ width: '50rem' }">
                <div class="grid grid-cols-2 sm:grid-cols-3">
                    <div
                        v-for="(permission, index) in data.vessel?.permissions"
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
