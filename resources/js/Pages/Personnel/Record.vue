<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

import DeleteUserForm from '../Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '../Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '../Profile/Partials/UpdateProfileInformationForm.vue';
import UpdateUserInformationForm from "../Profile/Partials/UpdateUserInformationForm.vue";

import ServiceRecord from "../Profile/Services/ServiceRecord.vue";
import Certificates from "../Profile/Certificates/Certificates.vue";
import Documents from "../Profile/Documents/Documents.vue";

import Profile from './Profile/Index.vue'

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
    mustVerifyEmail: Boolean,
    ranks: Object
});


const emit = defineEmits(["close"]);

const ranks= usePage().props.ranks;


const tabs = [
  {
    label: 'User Info',
    component: UpdateUserInformationForm,
    props: { ranks: ranks }
  },
  {
    label: 'Service Record',
    component: ServiceRecord
  },
  {
    label: 'Certificates',
    component: Certificates
  },
  {
    label: 'Documents',
    component: Documents
  }
]

const activeTab = ref(0)

</script>

<template>

    <div class="card">
        <Tabs v-model="activeTab">
            <TabList>
                <Tab
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :value="index"
                    v-slot="slotProps"
                    asChild
                >
                    <div
                    :class="['flex items-center gap-2', slotProps.class]"
                    @click="slotProps.onClick"
                    v-bind="slotProps.a11yAttrs"
                    >
                    <span class="font-bold whitespace-nowrap">{{ tab.label }}</span>
                    </div>
                </Tab>
            </TabList>

            <TabPanels>
                <TabPanel
                    v-for="(tab, index) in tabs"
                    :key="index"
                    :value="index"
                    v-slot="slotProps"
                    asChild
                >
                    <div
                    v-show="slotProps.active"
                    :class="slotProps.class"
                    v-bind="slotProps.a11yAttrs"
                    >
                    <component :is="tab.component" v-bind="tab.props || {}" />
                    </div>
                </TabPanel>
            </TabPanels>
        </Tabs>
        </div>
    
</template>
