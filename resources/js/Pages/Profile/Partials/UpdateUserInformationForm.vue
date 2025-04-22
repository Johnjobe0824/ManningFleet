<script setup>
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import AddUserInformationForm from './AddUserInformationForm.vue';
import EditUserInformationForm from './EditUserInformationForm.vue';
import PersonalInfo from '@/Pages/Personnel/Profile/PersonalInfo.vue';
import UpdateProfileInformationForm from './UpdateProfileInformationForm.vue';
import UpdatePasswordForm from './UpdatePasswordForm.vue';
import TextInput from '@/Components/TextInput.vue';
import { Link, useForm, usePage} from '@inertiajs/vue3';
import DatePicker from 'primevue/datepicker';
import Divider from 'primevue/divider';


import { onMounted, reactive, ref, watch, computed} from "vue";

const props = defineProps({
    mustVerifyEmail: Boolean,
    status: String,
    ranks: Object
});


const user = usePage().props.auth.user;

const form = useForm({
    first_name : user.personal_info?.first_name,
    last_name : user.personal_info?.last_name,
    middle_name: user.personal_info?.middle_name,
    suffix : user.personal_info?.suffix,
    civil_status : user.personal_info?.civil_status,
    gender : user.personal_info?.gender,
    address : user.personal_info?.address,
    serial_no: user.personal_info?.serial_no,
    rank_id : user.personal_info?.rank_id,
    user_id : user.id,
    age: user.personal_info?.age,
    birth_date: user.personal_info?.birth_date
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



const data = reactive({
    params: {
        createOpen: false,
        editOpen: false,

    },
    permission: null,
});

// const updateUserInfo = () => {

//     form.birth_date = new Date(form.birth_date).toISOString().split('T')[0];
//     form.patch(route('personal_info.update', user.personal_info?.id), {
//         preserveScroll: true,
//         onSuccess: (response) => {
//             // console.log(response.props.user.personal_info);
//             // emit("close");
//             user.personal_info = response.props.user.personal_info
//             data.editOpen = false
//             form.reset();
//         },
//         onError: () => null,
//         onFinish: () => null,
//     });
// };

// const updateAge = computed(() => {
//   if (!form.birth_date) return 'N/A'; // No birth date provided

//   const birthDate = new Date(form.birth_date);
//   const today = new Date();

//   let years = today.getFullYear() - birthDate.getFullYear();

//   // Check if birthday hasn't occurred yet this year
//   const hasBirthdayPassedThisYear =
//     today.getMonth() > birthDate.getMonth() ||
//     (today.getMonth() === birthDate.getMonth() && today.getDate() >= birthDate.getDate());

//   if (!hasBirthdayPassedThisYear) {
//     years--;
//   }

//   form.age = `${years}`;

//   return form.age;
// });

</script>

<template>
    <section>
        <header>
            <h2 class="text-lg font-medium text-gray-900">Personal Information</h2>

            <p v-if="user.personal_info && Object.keys(user.personal_info).length > 0" class="mt-1 text-sm text-gray-600">

                Update your personal informations.
            </p>
        </header>

        <div class="mt-2 space-y-2">
            <PrimaryButton 
                v-if="!user.personal_info || user.personal_info.length === 0" 
                @click="data.createOpen = true"
            >
                Add Personal Info
            </PrimaryButton>

            <PrimaryButton 
                v-else 
                @click="data.editOpen = true"
            >
                Edit Personal Info
            </PrimaryButton>                                                                                

            <AddUserInformationForm
                :show="data.createOpen"
                :ranks="ranks"
                @close="data.createOpen = false"
            />
            
            <EditUserInformationForm
            :show="data.editOpen"
            :ranks="ranks"
            @close="data.editOpen = false"
            />

            <PersonalInfo
            v-if="user.personal_info && Object.keys(user.personal_info).length > 0"
            :personal_info="user.personal_info"
            />
            <!-- <div class="mb-2 p-4 sm:p-8" v-if="user.personal_info && Object.keys(user.personal_info).length > 0">
                <div class="mt-1 space-y-1">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="flex flex-col gap-2">
                            <label for="first_name">First Name</label>
                            <p class="flex-auto">{{ user.personal_info.first_name }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="middle_name">Middle Name</label>
                            <p class="flex-auto">{{ user.personal_info.middle_name }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="last_name">Last Name</label>
                            <p class="flex-auto">{{ user.personal_info.last_name }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="suffix">Suffix</label>
                            <p class="flex-auto">{{ user.personal_info.suffix }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="rank_id">Rank</label>
                            <p class="flex-auto">{{ ranks.find(rank => rank.id === user.personal_info.rank_id)?.name || 'N/A' }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="serial_no">Serial Number</label>
                            <p class="flex-auto">{{ user.personal_info.serial_no }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="address">Address</label>
                            <p class="flex-auto">{{ user.personal_info.address }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="gender">Gender</label>
                            <p class="flex-auto">{{ user.personal_info.gender }}</p>
                        </div>
                        <div class="flex flex-col gap-2">
                            <label for="civil_status">Civil Status</label>
                            <p class="flex-auto">{{ user.personal_info.civil_status }}</p>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- <div v-if="data.editOpen">
                <div class="mb-2 p-4 sm:p-8"  v-if="user.personal_info && Object.keys(user.personal_info).length > 0">
                        <form @submit.prevent="updateUserInfo" class="mt-1 space-y-1">
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
                    
                    </div>

                            <div class="flex mt-2 items-center gap-4">
                                <PrimaryButton :disabled="form.processing" class="mt-2">Save</PrimaryButton>

                                <Transition
                                    enter-active-class="transition ease-in-out"
                                    enter-from-class="opacity-0"
                                    leave-active-class="transition ease-in-out"
                                    leave-to-class="opacity-0"
                                >
                                        <p v-if="form.recentlySuccessful" class="text-sm text-gray-600">Saved.</p>
                                </Transition>
                            </div>
                        </form>
                    </div>      

            </div> -->

            

            <Divider />
            <div class="p-4 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <Divider />
                <div class="p-4 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div>

        </div>

    </section>
</template>
