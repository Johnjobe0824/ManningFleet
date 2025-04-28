<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive } from "vue";

const props = defineProps({
    show: Boolean,
    title: String,
    permissions:Object,
});

const emit = defineEmits(["close"]);

const data = reactive({
    multipleSelect: false,
});

const form = useForm({
    name: "",
    code: ""
});


const create = () => {
    form.post(route("position.store"), {
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


</script>

<template>
    <Dialog v-model:visible="props.show" position="center" modal :header="'Add ' + props.title" :style="{ width: '50rem' }" :closable="false">
        <form @submit.prevent="create">
            <div class="flex flex-col gap-4">
                <div class="flex flex-col gap-2">
                    <label for="name">Name</label>
                    <InputText id="name" v-model="form.name" class="flex-auto" autocomplete="off" placeholder="Name" />    
                    <small v-if="form.errors.name" class="text-red-500">{{ form.errors.name }}</small>  
                </div>
                <div class="flex flex-col gap-2">
                    <label for="code">Code</label>
                    <InputText id="code" v-model="form.code" class="flex-auto" autocomplete="off" placeholder="Enter Code" />  
                    
                    <small v-if="form.errors.code" class="text-red-500">{{ form.errors.code }}</small>  
                </div>
                
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Save"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
