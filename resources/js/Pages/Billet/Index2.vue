<script setup>
import { ref, computed, onMounted } from "vue";
import Button from "primevue/button";
import Tag from "primevue/tag";
import Select from "primevue/select";
import InputText from "primevue/inputtext";
import IconField from "primevue/iconfield";
import InputIcon from "primevue/inputicon";
import { useToast } from "primevue/usetoast";
import axios from "axios";

const props = defineProps({
    title:                   { type: String,  default: "Junior Vessel Assignment" },
    user:                    { type: Object,  required: true },
    vessels:                 { type: Array,   required: true },
    positions:               { type: Array,   required: true },
    chipsReports:           { type: Array,   required: true },
    certificates:            { type: Array,   required: true },
});

const toast = useToast();

async function fetchJuniorVesselAssignments() {
    try {
        const response = await axios.get(route("juniorVesselAssignments.index"));
        return response.data;
    } catch (error) {
        toast.add({ severity: "error", summary: "Error", detail: "Failed to fetch junior vessel assignments." });
        return [];
    }
}



</script>

<template>
    <div class="space-y-6">

    </div>
</template>