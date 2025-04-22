<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

import DeleteUserForm from '../../Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '../../Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '../../Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateUserInformationForm from '../../Profile/Partials/UpdateUserInformationForm.vue';

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';


const props = defineProps({
    show: Boolean,
    title: String,
    permissions:Object,
    status: String,
    mustVerifyEmail: Boolean
});


const emit = defineEmits(["close"]);

</script>

<template>

    <div class="card">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                    
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdateProfileInformationForm
                            :must-verify-email="mustVerifyEmail"
                            :status="status"
                            class="max-w-xl"
                        />
                </div>
                                                
                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                        <UpdatePasswordForm class="max-w-xl" />
                
                    <!-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                                        <DeleteUserForm class="max-w-xl" />
                                                    </div> -->
                </div>
            </div>
        </div>
    
</template>
