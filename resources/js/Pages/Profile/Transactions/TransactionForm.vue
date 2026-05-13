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
import Billet from "@/Pages/Billet/Index.vue";
import JuniorHigherBillet from "@/Pages/JuniorHigherBillet/Juniorhigherbillet.vue";
import CompletionBillet from "@/Pages/CompletionBillet/Completionbillet.vue";
import SlmcEndorsement from "@/Pages/SlmcEndorsement/Slmcendorsement.vue";

import Documents from "../Documents/Documents.vue";
import Exodeliberation from "@/Pages/ExoDeliberation/Exodeliberation.vue";
import ExoReassign from "@/Pages/ExoReassign/ExoReassignIndex.vue";
import Category2Exo from "@/Pages/Category2Exo/Cat2exoIndex.vue";
import Category3Exo from "@/Pages/Category3Exo/Cat3exoIndex.vue";
import ExoIcascompletion from "@/Pages/ExoIcascompletion/ExoIcascompletion.vue";
import Category2Co from "@/Pages/Category2Co/Cat2CoIndex.vue";





const props = defineProps({
    show: Boolean,
    user: Object,
    // documents: Array, // ✅ Add documents prop

});

const user = usePage().props.auth.user;

const emit = defineEmits(['close']);

const tabs = computed(() => [
    {
    label: 'Billet Board',
    component: Billet,
    props: {
        user: user
    }
  },

  {
    label: 'Promotion for Higher Billet',
     component: JuniorHigherBillet,
    props: {
        user: user
    }
  },
  {
    label: 'Completion Billet',
    component: CompletionBillet,
    props: {
        user: user
    }
  },
  {
    label: 'SLMC Endorsements',
    component: SlmcEndorsement,
    props: {
        user: user
    }
  },
  {
    label: 'Delibertion for Exo Billet',
    component: Exodeliberation,
    props: {
        user: user
    }
  },
   {
    label: 'Exo Reassignment',
    component:ExoReassign,
    props: {
        user: user
    }
  },
  {
    label: 'CAT 2 EXO',
    component:Category2Exo,
    props: {
        user: user
    }
  },

  {
    label: 'CAT 3 EXO',
    component:Category3Exo,
    props: {
        user: user
    }
  },

  {
    label: 'EXO, ICAS completion',
    component:ExoIcascompletion,
    props: {
        user: user
    }
  },

  {
    label: 'CAT 2 CO',
    component:Category2Co,
    props: {
        user: user
    }
  }


  
 

])

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
