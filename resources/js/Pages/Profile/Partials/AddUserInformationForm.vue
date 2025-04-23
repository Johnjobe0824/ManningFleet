<script setup>
import { Link, useForm, usePage } from "@inertiajs/vue3";
import { watchEffect, ref, watch, computed } from "vue";
import Select from 'primevue/select';
import DatePicker from 'primevue/datepicker';


const props = defineProps({
    show: Boolean,
    ranks: Object,
    user: Object
});

const user = usePage().props.auth.user;

const emit = defineEmits(["close"]);

const form = useForm({
    first_name: "",
    last_name: "",
    middle_name:"",
    suffix: "",
    civil_status: "",
    gender: "",
    address: "",
    serial_no:"",
    rank_id:"",
    age:"",
    birth_date:"",
    user_id: user.id
});

const updateAge = computed(() => {
  if (!form.birth_date) return 'N/A'; // No birth date provided

  const birthDate = new Date(form.birth_date);
  const today = new Date();

  let years = today.getFullYear() - birthDate.getFullYear();

  // Check if birthday hasn't occurred yet this year
  const hasBirthdayPassedThisYear =
    today.getMonth() > birthDate.getMonth() ||
    (today.getMonth() === birthDate.getMonth() && today.getDate() >= birthDate.getDate());

  if (!hasBirthdayPassedThisYear) {
    years--;
  }

  form.age = `${years}`;

  return form.age;
});


const civil_statuses = ref([
  {
    id: 1,
    name: "Single"
  },
  {
    id: 2,
    name: "Married"
  },
  {
    id: 3,
    name: "Separated"
  },
  {
    id: 4,
    name: "Widowed"
  }
]);

const genders = ref([
  {
    id: 1,
    name: "Male"
  },
  {
    id: 2,
    name: "Female"
  }
]);

const create = () => {
    form.birth_date = new Date(form.birth_date).toISOString().split('T')[0];
    form.post(route("personal_info.store"), {
        preserveScroll: true,
        onSuccess: (response) => {
            emit("close");
            form.reset();
            user.personal_info = response.props.user.personal_info
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
    <Dialog v-model:visible="props.show" position="top" modal :header="'Add Personal Information'" :style="{ width: '40em' }" :closable="false">
        <form @submit.prevent="create">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="flex flex-col gap-2">
                    <label for="first_name">First Name</label>
                    <InputText id="first_name" v-model="form.first_name" class="flex-auto" autocomplete="off" placeholder="Enter First Name" aria-describedby="firstNameError" />
                    <small id="firstNameError" v-if="form.errors.first_name" class="text-red-500">{{ form.errors.first_name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="middle_name">Middle Name</label>
                    <InputText id="middle_name" v-model="form.middle_name" class="flex-auto" autocomplete="off" placeholder="Enter Middle Name" aria-describedby="middleNameError" />
                    <small id="middleNameError" v-if="form.errors.middle_name" class="text-red-500">{{ form.errors.middle_name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="last_name">Last Name</label>
                    <InputText id="last_name" v-model="form.last_name" class="flex-auto" autocomplete="off" placeholder="Enter Last Name" aria-describedby="lastNameError" />
                    <small id="lastNameError" v-if="form.errors.last_name" class="text-red-500">{{ form.errors.last_name }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="suffix">Suffix</label>
                    <InputText id="suffix" v-model="form.suffix" class="flex-auto" autocomplete="off" placeholder="Enter Suffix" />
                </div>
                <div class="flex flex-col gap-2">
                    <label for="rank_id">Rank</label>
                    <Select v-model="form.rank_id" :options="ranks" optionValue="id" optionLabel="name" placeholder="Select a Rank" class="w-full" />
                    <small v-if="form.errors.rank_id" class="text-red-500">{{ form.errors.rank_id }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="serial_no">Serial Number</label>
                    <InputText id="serial_no" v-model="form.serial_no" class="flex-auto" autocomplete="off" placeholder="Enter Serial #" aria-describedby="serial_noError" />
                    <small id="serial_noError" v-if="form.errors.serial_no" class="text-red-500">{{ form.errors.serial_no }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="address">Address</label>
                    <InputText id="address" v-model="form.address" class="flex-auto" autocomplete="off" placeholder="Enter Address" aria-describedby="addressError" />
                    <small id="addressError" v-if="form.errors.address" class="text-red-500">{{ form.errors.address }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="birth_date">Birth Date</label>
                    <DatePicker v-model="form.birth_date" dateFormat="dd/mm/yy" placeholder="Enter Birth Date"  />
                    <!-- <InputText id="serial_no" v-model="form.serial_no" class="flex-auto" autocomplete="off" placeholder="Enter Serial #" aria-describedby="serial_noError" /> -->
                    <small id="birth_dateError" v-if="form.errors.birth_date" class="text-red-500">{{ form.errors.birth_date }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="age">Age</label>
                    <InputText id="age" v-model="updateAge" class="flex-auto" autocomplete="off" disabled aria-describedby="ageError" />
                    <small id="ageError" v-if="form.errors.age" class="text-red-500">{{ form.errors.age }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="gender">Gender</label>
                    <Select v-model="form.gender" :options="genders" optionLabel="name" optionValue="name" placeholder="Select a Gender" class="w-full" />
                    <small v-if="form.errors.gender" class="text-red-500">{{ form.errors.gender }}</small>
                </div>
                <div class="flex flex-col gap-2">
                    <label for="civil_status">Civil Status</label>
                    <Select v-model="form.civil_status" :options="civil_statuses" optionLabel="name" optionValue="name" placeholder="Select a Civil Status" class="w-full" />
                    <small v-if="form.errors.civil_status" class="text-red-500">{{ form.errors.civil_status }}</small>
                </div>
                <div class="flex justify-end gap-2 col-span-2">
                    <Button type="button" label="Cancel" severity="secondary" @click="emit('close')"></Button>
                    <Button type="submit" label="Save"></Button>
                </div>
            </div>
        </form>
    </Dialog>
</template>
