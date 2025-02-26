<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    vessel: Object,
    // permissions: Object,
});

const data = reactive({
    multipleSelect: false,
});

const emit = defineEmits(["close"]);

const form = useForm({
    vessel_name: "",
    vessel_bow_no:"",
    category: "",
    size:"",
    status:""
});

// const update = () => {
//     form.put(route("vessel.update", props.vessel?.id), {
//         preserveScroll: true,
//         onSuccess: () => {
//             emit("close");
//             form.reset();
//         },
//         onError: () => null,
//         onFinish: () => null,
//     });
// };

const update = () => {
    form.put(route("vessel.update", props.vessel?.id), {
        preserveScroll: true,
        onSuccess: () => {
            emit("close");
            form.reset();
        },
        onError: (errors) => {
            console.error("Update failed:", errors);
        },
        onFinish: () => {
            console.log("Request completed.");
        },
    });
};


watchEffect(() => {
    if (props.show) {
        form.errors = {};
        form.vessel_name = props.vessel?.vessel_name;
        form.vessel_bow_no = props.vessel?.vessel_bow_no;
        form.category = props.vessel?.category;
        form.size = props.vessel?.size;
        form.status = props.vessel?.status;
        // form.permissions = props.vessel.permissions?.map((d) => d.id);
    }
});


const categories = ref([
  {
    id: 1,
    name: "Category I"
  },
  {
    id: 2,
    name: "Category II"
  },
  {
    id: 3,
    name: "Category III"
  }
]);


const statuses = [
  {
    id: 1,
    name: "Ready For Sea"
  },
  {
    id: 2,
    name: "Not Ready For Sea"
  }
];


const sizes = [
  {
    id: 1,
    name: "24-meter"
  },
  {
    id: 2,
    name: "35-meter"
  },
  {
    id: 3,
    name: "44-meter"
  },
  {
    id: 4,
    name: "56-meter"
  },
  {
    id: 5,
    name: "83-meter"
  },
  {
    id: 6,
    name: "97-meter"
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
                    <label for="vessel_bow_no">Vessel Bow No</label>
                    <InputText id="vessel_bow_no" v-model="form.vessel_bow_no" class="flex-auto" autocomplete="off" placeholder="Vessel Bow No" />    
                    <small v-if="form.errors.vessel_bow_no" class="text-red-500">{{ form.errors.vessel_bow_no }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <!-- <div class="font-semibold text-xl">Select</div> -->
                     <label for="category">Category</label>
                    <Select v-model="form.category" :options="categories" optionLabel="name" optionValue="name" placeholder="Select category" />

                    <small v-if="form.errors.category" class="text-red-500">{{ form.errors.category }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="size">Size</label>
                    <!-- <InputText id="type" v-model="form.type" class="flex-auto" autocomplete="off" placeholder="Type" />   -->
                    <Select v-model="form.size" :options="sizes" optionLabel="name" optionValue="name" placeholder="Select size" />

                    <small v-if="form.errors.size" class="text-red-500">{{ form.errors.size }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="status">Status</label>
                    <!-- <InputText id="type" v-model="form.type" class="flex-auto" autocomplete="off" placeholder="Type" />   -->
                    <Select v-model="form.status" :options="statuses" optionLabel="name" optionValue="name" placeholder="Select status" />
                    <small v-if="form.errors.status" class="text-red-500">{{ form.errors.status }}</small>  
                </div>
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Update"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
