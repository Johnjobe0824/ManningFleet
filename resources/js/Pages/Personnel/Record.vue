<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';


const props = defineProps({
    show: Boolean,
    title: String,
    permissions:Object,
});

const emit = defineEmits(["close"]);

// const data = reactive({
//     multipleSelect: false,
// });

// const form = useForm({
//     name: "",
//     type: ""
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
// ];

// const create = () => {
//     form.post(route("rank.store"), {
//         preserveScroll: true,
//         onSuccess: () => {
//             emit("close");
//             form.reset();
//         },
//         onError: () => null,
//         onFinish: () => null,
//     });
// };

// watchEffect(() => {
//     if (props.show) {
//         form.errors = {};
//     }
// });

// const selectAll = (event) => {
//     if (event.target.checked === false) {
//         form.permissions = [];
//     } else {
//         form.permissions = [];
//         props.permissions.forEach((permission) => {
//             form.permissions.push(permission.id);
//         });
//     }
// };
// const select = () => {
//     if (props.permissions.length == form.permissions.length) {
//         data.multipleSelect = true;
//     } else {
//         data.multipleSelect = false;
//     }
// };

const items = ref([
    { route: '/dashboard', label: 'Dashboard', icon: 'pi pi-home' },
    { route: '/transactions', label: 'Transactions', icon: 'pi pi-chart-line' },
    { route: '/products', label: 'Products', icon: 'pi pi-list' },
    { route: '/messages', label: 'Messages', icon: 'pi pi-inbox' }
]);

</script>

<template>

    <div class="card">
            <Tabs value="0">
                <TabList>
                    <Tab v-slot="slotProps"  value="0" asChild>
                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                             <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/amyelsner.png" shape="circle" />
                            <span class="font-bold whitespace-nowrap">Profile</span>
                        </div>
                       
                    </Tab>
                    <Tab v-slot="slotProps"  value="1" asChild>
                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                              <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/onyamalimba.png" shape="circle" />
                            <span class="font-bold whitespace-nowrap">Slot Locator</span>
                        </div>
                    </Tab>
                    <Tab v-slot="slotProps" value="2" asChild>
                        <div :class="['flex items-center gap-2', slotProps.class]" @click="slotProps.onClick" v-bind="slotProps.a11yAttrs">
                            <Avatar image="https://primefaces.org/cdn/primevue/images/avatar/ionibowcher.png" shape="circle" />
                            <span class="font-bold whitespace-nowrap">Exam Schedule</span>
                            <!-- <Badge value="2" /> -->
                        </div>
                    </Tab>
                </TabList>
                <TabPanels>
                    <TabPanel value="0" v-slot="slotProps" asChild>
                        <div v-show="slotProps.active" :class="slotProps.class" v-bind="slotProps.a11yAttrs">
                            <p class="m-2 text-3xl text-surface-900">
                                Profile Section
                            </p>
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
                                            <p class="m-0">
                                                Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo
                                                consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.
                                            </p>
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
