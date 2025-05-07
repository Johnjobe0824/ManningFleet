<script setup>
import { Link, useForm, usePage  } from "@inertiajs/vue3";
import { watchEffect, ref, watch, computed } from "vue";
import Select from 'primevue/select';
import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';

import Certificates from "../Certificates/Certificates.vue";


const props = defineProps({
    show: Boolean,
    user: Object
});

const user = usePage().props.auth.user;

const emit = defineEmits(['close']);

const tabs = [
  {
    label: 'Certificates',
    component: Certificates
  },
  {
    label: 'Training/Schooling',
    component: Certificates
  },
  {
    label: 'Assessment',
    component: Certificates
  },
  {
    label: 'Chips Report',
    component: Certificates
  }
]

// const activeTab = ref(0)
const activeTab = ref(window.defaultTab || 0) // Set default tab based on session

watchEffect(() => {
    if (props.show) {
    }
});

</script>

<template>
    <Dialog v-model:visible="props.show" position="top" modal :header="'Select Transaction'" :style="{ width: '60em' }" :closable="true">
        <div class="w-full h-full flex flex-col">
            <Tabs v-model="activeTab" scrollable class="flex-grow">
                <TabList  class="flex justify-between w-full">
                    <Tab
                        v-for="(tab, index) in tabs"
                        :key="index"
                        :value="index"
                        v-slot="slotProps"
                        asChild
                    >
                    <div
                    :class="['flex flex-col items-center gap-2', slotProps.class]"
                    @click="slotProps.onClick"
                    v-bind="slotProps.a11yAttrs"
                    >
                        <!-- Large Icon -->
                        <Avatar image="https://www.gravatar.com/avatar/05dfd4b41340d09cae045235eb0893c3?d=mp" class="flex items-center justify-center mr-2" size="xlarge" />
                        <!-- Text Below Icon -->
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
        <div class="flex justify-end gap-2 col-span-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <!-- <Button type="submit" label="Save"></Button> -->
                </div>
    </Dialog>
</template>
