<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    craft_size: Object,
    // permissions: Object,
});

const data = reactive({
    multipleSelect: false,
});

const emit = defineEmits(["close"]);

const form = useForm({
    code: "",
    description:""
    
});

// const update = () => {
//     form.put(route("craft_size.update", props.craft_size?.id), {
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
    form.put(route("craft_size.update", props.craft_size?.id), {
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
        form.code = props.craft_size?.code;
        form.description = props.craft_size?.description;
    }
});



</script>

<template>
     <Dialog v-model:visible="props.show" position="center" modal :header="'Update ' + props.title" :style="{ width: '50rem' }" :closable="false">
        <form @submit.prevent="update">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="code">Code</label>
                    <InputText id="code" v-model="form.code" class="flex-auto" autocomplete="off" placeholder="Code" />    
                    <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="description">Description</label>
                    <InputText id="description" v-model="form.description" class="flex-auto" autocomplete="off" placeholder="Description" />    
                    <small v-if="form.errors.description" class="text-red-500">{{ form.errors.description }}</small>  
                </div>
               
                
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Update"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
