<script setup>
import { useForm, usePage } from "@inertiajs/vue3";
import { watchEffect, reactive, ref,computed } from "vue";

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
    ranks: Object,
    documents: Object,
    certificates: Object,
    seaDutyRecords: Object,
    user: Object,
    vessels: Array 
});


const emit = defineEmits(["close"]);


const certificates = computed(() => usePage().props.certificates);
const documents = computed(() => usePage().props.documents);
const seaDutyRecords = computed(() => usePage().props.seaDutyRecords);
const vessels = computed(() => usePage().props.vessels);
const ranks = computed(() => usePage().props.ranks);
const user = computed(() => usePage().props.user);


const tabs = computed(() => [
  {
    label: 'Profile',
    component: UpdateUserInformationForm,
    props: { ranks: ranks.value }
  },
  {
    label: 'Sea-Duty Record',
    component: ServiceRecord,
     props: {
      seaDutyRecords: seaDutyRecords.value,
      user: user.value,
      vessels: vessels.value, 
    } 
  },
  {
    label: 'Documents',
    component: Documents,
    props: { documents: documents.value, user: user.value }
  },
  {
    label: 'Training & Certification',
    component: Certificates,
  },
  {
    label: 'Assessment',
    component: Certificates
  },
  {
    label: 'Chips Report',
    component: Certificates
  }
]);


// const activeTab = ref(0)
const activeTab = ref(window.defaultTab || 0) // Set default tab based on session


</script>

<template>

    <div class="card">
        <Tabs v-model="activeTab" scrollable>
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
