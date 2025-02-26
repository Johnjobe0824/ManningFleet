
<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
// import Create from "@/Pages/Rank/Create.vue";
// import Edit from "@/Pages/Rank/Edit.vue";
import Record from "@/Pages/Personnel/Record.vue";
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
    // ranks: Object,
    perPage: Number,
});

loadToast();

const deleteDialog = ref(false);
const permissionDialog = ref(false);
const form = useForm({});

// const data = reactive({
//     params: {
//         search: props.filters.search,
//         field: props.filters.field,
//         order: props.filters.order,
//         createOpen: false,
//         editOpen: false,
//     },
//     // rank: null,
// });

// const types = [
//   {
//     id: 1,
//     name: "Officer"
//   },
//   {
//     id: 2,
//     name: "Non-Officer"
//   },
//   {
//     id: 3,
//     name: "NUP-Civilian"
//   }
// ]

// const deleteData = () => {

//     deleteDialog.value = false;
    
//     form.delete(route("rank.destroy", data.rank?.id), {
//         preserveScroll: true,
//         onSuccess: () => {
//             form.reset();
//         },
//         onError: () => null,
//         onFinish: () => null,
//     });
// }


// const onPageChange = (event) => {
//     router.get(route('rank.index'), { page: event.page + 1 }, { preserveState: true });
// };

// watch(
//     () => _.cloneDeep(data.params),
//     debounce(() => {
//         let params = pickBy(data.params);
//         router.get(route("rank.index"), params, {
//             replace: true,
//             preserveState: true,
//             preserveScroll: true,
//         });
//     }, 150)
// );

</script>

<template>
    <app-layout>             
        <div class="card">  
            <div class="flex flex-col md:flex-row">
                <div class="w-full md:w-3/12 flex flex-col items-center justify-center py-5 px-2">
                    <Image src="https://primefaces.org/cdn/primevue/images/galleria/galleria10.jpg" alt="Image" width="250" />
                </div>
                <div class="w-full md:w-2/12">
                    <Divider layout="vertical" />
                </div>
                <div class="w-full md:w-7/12 flex items-center justify-center py-5">
                    <p class="m-0">
                    Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam
                    voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed quia non numquam eius modi.
                    </p>
                </div>
            </div>
            <div class="card">
                <Record />
            </div>
          <!--   <Create
                :show="data.createOpen"
                @close="data.createOpen = false"
                :title="props.title"
                :permissions="props.permissions"
            />
            <Edit
                :show="data.editOpen"
                @close="data.editOpen = false"
                :rank="data.rank"
                :title="props.title"
            /> -->

            <!-- <Dialog v-model:visible="permissionDialog" modal :header="'Permission ' + data.ranks?.name" :style="{ width: '50rem' }">
                <div class="grid grid-cols-2 sm:grid-cols-3">
                    <div
                        v-for="(permission, index) in data.rank?.permissions"
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
