<script setup>
import { useForm } from "@inertiajs/vue3";
import { watchEffect, reactive, ref, computed } from "vue";
import InputText from "primevue/inputtext";
import Textarea from "primevue/textarea";
import Button from "primevue/button";
import Dialog from "primevue/dialog";
import Dropdown from "primevue/dropdown";
import DatePicker from "primevue/datepicker";
import InputSwitch from 'primevue/inputswitch';


const props = defineProps({
    show: Boolean,
    title: String,
    user: Object,
    vessels: Array,
});

const user_id = props.user?.id;

const selectedVesselId = ref(null);
const selectedPositionId = ref(null);

const filteredPositions = computed(() => {
  const vessel = props.vessels.find(v => v.id === selectedVesselId.value);
  return vessel?.assigned_positions ?? [];
});


const emit = defineEmits(["close"]);

const form = useForm({
    user_id: user_id,
    vessel_id: null,
    position_id: null,
    date_from: "",
    date_to: "",
    authority: "",
    tst: "",
    tmc: "",
    status: "Pending",
    is_active: false,
});

const create = () => {
    form.post(route("seaDutyRecords.store"), {
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
    <Dialog
        v-model:visible="props.show"
        position="center"
        modal
        :header="'Add ' + props.title"
        :style="{ width: '50rem' }"
        :closable="false"
    >
        <form @submit.prevent="create">
            <div class="flex flex-col gap-4">
                
                <div class="mb-4">
                    <label for="vessel" class="block text-sm font-medium text-gray-700">Vessel</label>
                    <Dropdown
                    id="vessel"
                    v-model="form.vessel_id"
                    :options="props.vessels"
                    optionLabel="vessel_name"
                    optionValue="id"
                    placeholder="Select Vessel"
                    class="w-full"
                    />
                    <small v-if="form.errors.vessel_id" class="text-red-500">{{ form.errors.vessel_id }}</small>

                    </div>

                    <div class="mb-4">
                    <label for="position" class="block text-sm font-medium text-gray-700">Position</label>
                    <Dropdown
                    id="position"
                    v-model="form.position_id"
                    :options="filteredPositions"
                    :optionLabel="(pos) => pos.position.name"
                    optionValue="position_id"
                    placeholder="Select Position"
                    class="w-full"
                    />
                    <small v-if="form.errors.position_id" class="text-red-500">{{ form.errors.position_id }}</small>

                    </div>


                <!-- Date From -->
                <div class="flex flex-col gap-2">
                    <label for="date_from">Date From</label>
                    <DatePicker
                        v-model="form.date_from"
                        placeholder="Start Date"
                        dateFormat="yy-mm-dd"
                        class="w-full"
                    />
                    <small v-if="form.errors.date_from" class="text-red-500">{{ form.errors.date_from }}</small>
                </div>

                <!-- Date To -->
                <div class="flex flex-col gap-2">
                    <label for="date_to">Date To</label>
                    <DatePicker
                        v-model="form.date_to"
                        placeholder="End Date"
                        dateFormat="yy-mm-dd"
                        class="w-full"
                    />
                    <small v-if="form.errors.date_to" class="text-red-500">{{ form.errors.date_to }}</small>
                </div>

                <!-- Authority -->
                <div class="flex flex-col gap-2">
                    <label for="authority">Authority</label>
                    <InputText
                        id="authority"
                        v-model="form.authority"
                        class="w-full"
                        placeholder="Enter Authority"
                    />
                    <small v-if="form.errors.authority" class="text-red-500">{{ form.errors.authority }}</small>
                </div>

                <!-- TST -->
                <div class="flex flex-col gap-2">
                    <label for="tst">Total Steaming Time</label>
                    <InputText
                        id="tst"
                        v-model="form.tst"
                        class="w-full"
                        placeholder="e.g. 120 days"
                    />
                    <small v-if="form.errors.tst" class="text-red-500">{{ form.errors.tst }}</small>
                </div>

                <!-- TMC -->
                <div class="flex flex-col gap-2">
                    <label for="tmc">Total Miles Covered</label>
                    <InputText
                        id="tmc"
                        v-model="form.tmc"
                        class="w-full"
                        placeholder="e.g. 3500"
                    />
                    <small v-if="form.errors.tmc" class="text-red-500">{{ form.errors.tmc }}</small>
                </div>
                <!-- Is Active Switch -->
                <div class="flex flex-col gap-2">
                    <label for="is_active">Is Active?</label>
                    <div class="flex items-center gap-3">
                        <InputSwitch id="is_active" v-model="form.is_active" />
                        <span>{{ form.is_active ? 'Active' : 'Inactive' }}</span>
                    </div>
                    <small v-if="form.errors.is_active" class="text-red-500">{{ form.errors.is_active }}</small>
                </div>


                <!-- Footer Buttons -->
                <div class="flex justify-end gap-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')" />
                    <Button type="submit" label="Save" />
                </div>
            </div>
        </form>
    </Dialog>
</template>

