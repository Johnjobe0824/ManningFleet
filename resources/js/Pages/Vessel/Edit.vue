<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    vessel: Object,
    permissions: Object,
});

const data = reactive({
    multipleSelect: false,
});

const emit = defineEmits(["close"]);

const form = useForm({
    vessel_name: "",
    type: ""
});

const update = () => {
    form.put(route("vessel.update", props.vessel?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
            form.reset();
        },
        onError: () => null,
        onFinish: () => null,
    });
};

watchEffect(() => {
    if (props.show) {
        form.errors = {};
        form.vessel_name = props.vessel?.vessel_name;
        form.type = props.vessel?.type;
        // form.permissions = props.vessel.permissions?.map((d) => d.id);
    }
});



const types = [
  {
    id: 1,
    name: "Patrol ships"
  },
  {
    id: 2,
    name: "Service vessel"
  },
  {
    id: 3,
    name: "Small streamer"
  },
  {
    id: 4,
    name: "PCG vessel operation"
  }
];

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


</script>

<template>
     <Dialog v-model:visible="props.show" position="center" modal :header="'Update ' + props.title" :style="{ width: '50rem' }" :closable="false">
        <form @submit.prevent="update">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="vessel_name">Vessel Name</label>
                    <InputText id="vessel_name" v-model="form.vessel_name" class="flex-auto" autocomplete="off" placeholder="Name" />    
                    <small v-if="form.errors.vessel_name" class="text-red-500">{{ form.errors.vessel_name }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="type">Type</label>
                    <!-- <InputText id="type" v-model="form.type" class="flex-auto" autocomplete="off" placeholder="Type" />   -->
                    
                        <select
                        v-model="form.type"
                        id="id"
                        class="relative z-20 w-full appearance-none rounded border border-stroke bg-transparent py-3 px-5 outline-none transition focus:border-primary active:border-primary dark:border-form-strokedark dark:bg-form-input dark:focus:border-primary"
                        :class="{
                            ' text-red-900 focus:ring-red-500 focus:border-red-500 border-red-300'
                            :form.errors.type
                        }"
                    >
                        <option value="" disabled selected>Select Type</option>
                        <option 
                            v-for="item in types" :key="item.id"
                            :value="item.name">
                            {{ item.name }}
                        </option>
                    </select>
                    <small v-if="form.errors.type" class="text-red-500">{{ form.errors.type }}</small>  
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Update"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
