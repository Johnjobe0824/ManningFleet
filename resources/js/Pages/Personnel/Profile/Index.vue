<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

import DeleteUserForm from '../../Profile/Partials/DeleteUserForm.vue';
import UpdatePasswordForm from '../../Profile/Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from '../../Profile/Partials/UpdateProfileInformationForm.vue';

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
                            <Tabs value="0">
                                <TabList>
                                    <Tab v-slot="slotProps"  value="0" asChild>
                                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                                            <!-- <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" shape="circle" /> -->
                                            <span class="font-bold whitespace-nowrap">Profile</span>
                                        </div>
                                    
                                    </Tab>
                                    <Tab v-slot="slotProps"  value="1" asChild>
                                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                                            <!-- <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/onyamalimba.png" shape="circle" /> -->
                                            <span class="font-bold whitespace-nowrap">Medical Record</span>
                                        </div>
                                    </Tab>
                                    <Tab v-slot="slotProps" value="2" asChild>
                                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                                            <!-- <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/ionibowcher.png" shape="circle" /> -->
                                            <span class="font-bold whitespace-nowrap">Certification</span>
                                            <!-- <Badge value="2" /> -->
                                        </div>
                                    </Tab>
                                </TabList>
                                <TabPanels>
                                    <TabPanel value="0" v-slot="slotProps" asChild>
                                        <div v-show="slotProps.active" :class="slotProps.class" v-bind="slotProps.a11yAttrs">
                                            <div class="py-12">
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
                                                    </div>

                                                    <!-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                                                        <DeleteUserForm class="max-w-xl" />
                                                    </div> -->
                                                </div>
                                            </div>
                                        </div>
                                    </TabPanel>
                                    <TabPanel value="1" v-slot="slotProps" asChild>
                                        <div v-show="slotProps.active" :class="slotProps.class" v-bind="slotProps.a11yAttrs">
                                            <p class="m-0">
                                        Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim
                                        ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt. Consectetur, adipisci velit, sed quia non numquam eius modi.
                                            </p>
                                        </div>
                                    </TabPanel>
                                    <TabPanel v-slot="slotProps" value="2" asChild>
                                        <div v-show="slotProps.active" :class="slotProps.class" v-bind="slotProps.a11yAttrs">
                                            <p class="m-0">
                                                At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident, similique sunt in
                                                culpa qui officia deserunt mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque nihil impedit quo minus.
                                            </p>
                                        </div>
                                    </TabPanel>
                                </TabPanels>
                            </Tabs>      
                    
        </div>
    
</template>
