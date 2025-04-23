
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
