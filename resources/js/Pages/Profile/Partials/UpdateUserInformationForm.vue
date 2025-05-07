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
                <i class="pi pi-plus mr-2"></i> 
                Add Personal Info
            </PrimaryButton>

            <PrimaryButton 
                v-else 
                @click="data.editOpen = true"
            >
                <i class="pi pi-pencil mr-2"></i> 
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
