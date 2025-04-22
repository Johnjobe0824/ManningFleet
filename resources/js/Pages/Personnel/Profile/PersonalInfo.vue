<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage } from '@inertiajs/vue3';

import { onMounted, reactive, ref, watch, computed } from "vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    personal_info: Object
});


const user = usePage().props.auth.user;

const form = useForm({
    name: user.name,
    email: user.email,
});


const data = reactive({
    params: {
        createOpen: false,
        editOpen: false,
    },
    permission: null,
});

</script>

<template>
    <section>
        <div class="mt-2 space-y-2">
            <div class="card">
                <ul class="list-none p-0 m-0">
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Name</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.rank?.name }} {{ personal_info?.first_name }} {{ personal_info?.middle_name }} {{ personal_info?.last_name }} {{ personal_info?.suffix }} PCG</div>
                        </div>
    
                    </li>
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Serial Number</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.serial_no }}</div>
                        </div>
                        
                    </li>
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Address</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.address }}</div>
                        </div>
                        
                    </li>
                    
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Birth Date</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.birth_date}}</div>
                        </div>
                        
                    </li>
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Age</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.age}}</div>
                        </div>
                    </li>
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Gender</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.gender }}</div>
                        </div>
                        
                    </li>
                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                        <div>
                            <span class="font-medium mr-2 mb-1 md:mb-0">Civil Status</span>
                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.civil_status }}</div>
                        </div>
                        
                    </li>
                    
                    
                </ul>
            </div>
            
        </div>

    </section>
</template>
