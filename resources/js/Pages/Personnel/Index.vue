
<script setup>
import AppLayout from "@/sakai/layout/AppLayout.vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
// import Create from "@/Pages/Rank/Create.vue";
// import Edit from "@/Pages/Rank/Edit.vue";
import { usePage, useForm } from '@inertiajs/vue3';
import Profile from './Profile/Index.vue'



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
            <Profile />
        </div>
    </app-layout>
</template>

<style scoped lang="scss">

</style>
