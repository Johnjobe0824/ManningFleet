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
    code: "",
    description: ""
});


const create = () => {
    form.post(route("craft_size.store"), {
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
                    <Button type="submit" label="Save"></Button>
                </div> 
            </div>
        </form>
    </Dialog>
</template>
