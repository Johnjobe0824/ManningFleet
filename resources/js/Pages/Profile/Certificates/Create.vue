<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    permissions:Object,
    user: Object
});

const user_id = props.user?.id
const emit = defineEmits(["close"]);

const data = reactive({
    multipleSelect: false,
});

const form = useForm({
    user_id: user_id,
    name: "",
    training_center: "",
    batch_no: "",
    date:"",
    status:"pending"
});

const statuses = [
  {
    id: 1,
    name: "Pending"
  },
  {
    id: 2,
    name: "Approved"
  },
  {
    id: 3,
    name: "Rejected"
  }
];

const create = () => {
    form.post(route("certificates.store"), {
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
    }
});

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
    <Dialog v-model:visible="props.show" position="center" modal :header="'Add ' + props.title" :style="{ width: '50rem' }" :closable="false">
        <form @submit.prevent="create">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Name</label>
                    <InputText id="name" v-model="form.name" class="flex-auto" autocomplete="off" placeholder="Training Name" />    
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="training_center">Training Center</label>
                    <InputText id="training_center" v-model="form.training_center" class="flex-auto" autocomplete="off" placeholder="Training Center" />    
                    <small v-if="form.errors.training_center" class="text-red-500">{{ form.errors.training_center }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="batch_no">Batch number</label>
                    <InputText id="batch_no" v-model="form.batch_no" class="flex-auto" autocomplete="off" placeholder="Batch number" />    
                    <small v-if="form.errors.batch_no" class="text-red-500">{{ form.errors.batch_no }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="date">Date Completed</label>
                    <DatePicker v-model="form.date" dateFormat="dd/mm/yy" placeholder="Enter Date Completed"  />
                    <small id="dateError" v-if="form.errors.date" class="text-red-500">{{ form.errors.date }}</small>
                </div>
                
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Save"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
