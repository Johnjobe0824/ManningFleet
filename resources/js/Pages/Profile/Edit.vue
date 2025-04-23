<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Toast from 'primevue/toast';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import axios from 'axios';

import { onMounted, reactive, ref, watch, computed} from "vue";

import { loadToast } from '@/composables/loadToast';

import Record from "@/Pages/Personnel/Record.vue";
import { useToast } from 'primevue/usetoast';


import Tabs from 'primevue/tabs';
import TabList from 'primevue/tablist';
import Tab from 'primevue/tab';
import TabPanels from 'primevue/tabpanels';
import TabPanel from 'primevue/tabpanel';
import FileUpload from "primevue/fileupload";

const props = defineProps({
    user:Object,
    ranks: Object,
    status: String,
    mustVerifyEmail: Boolean
});

const user = usePage().props.auth.user;
const personal_info = user.personal_info;

loadToast();

const toast = useToast();

const data = reactive({
    params: {
        createOpen: false,
        editOpen: false,

    }
});

const src = ref(null);

const form = useForm({
    photo_path: "",
    user_id: user.id
});

function onFileSelect(event) {
  const file = event.files[0];
  if (!file) return;

   // Backup current image in case upload fails
   const previousSrc = src.value;

  // Show image preview
  const reader = new FileReader();
  reader.onload = (e) => {
    src.value = e.target.result;
  };
  reader.readAsDataURL(file);

  // Prepare form data
  const formData = new FormData();
  formData.append('photo_path', file);
  formData.append('user_id', user.id);
  if (user.photo) {
    formData.append('existing_photo', user.photo);
  }

  axios.post(route('user_photo.store'), formData)
    .then(response => {
      const updatedUser = response.data.props?.user;
      if (updatedUser) {
        user.photo = updatedUser.photo;
      }

      // ✅ Show success toast
      toast.add({
        severity: 'success',
        summary: 'Success',
        detail: 'Photo uploaded successfully!',
        life: 3000
      });
    })
    .catch(error => {
        src.value = previousSrc;
        // Try to get the real error message from Laravel
        const errorMessage =
            error.response?.data?.message ||
            error.response?.data?.errors?.photo_path?.[0] ||
            'Something went wrong. Please try again.';

        // 🧾 Show error toast
        toast.add({
            severity: 'error',
            summary: 'Upload Failed',
            detail: errorMessage,
            life: 5000
        });

        console.error('Upload failed:', errorMessage);
    });
}


</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <Toast />
        
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Profile</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <div class="flex flex-col md:flex-row">
                        <div class="w-full md:w-3/12 flex flex-col items-center justify-center py-5 px-2">
                            <div class="flex justify-center w-full">
                                <Image
                                    :src="src ?? (user.photo?.photo_path ? `/storage/${user.photo.photo_path}` : 'https://primefaces.org/cdn/primevue/images/galleria/galleria10.jpg')"
                                    class="w-full sm:w-64"
                                    alt="Profile Image"
                                    width="200"
                                />
                                </div>
                            <div class="card flex flex-col items-center gap-6 mt-4">
                            <FileUpload
                                mode="basic"
                                @select="onFileSelect"
                                customUpload
                                auto
                                chooseLabel="Upload Photo"
                                accept="image/*"
                                class="p-button-outlined"
                            />
                            </div>
                        </div>

                        <div class="w-full md:w-2/12">
                            <Divider layout="vertical" />
                        </div>
                        <div class="w-full md:w-7/12 flex py-5">
                                <ul class="list-none p-0 m-0">
                                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                                        <div>
                                            <span class="font-medium mb-1 md:mb-0">Name</span>
                                            <div class="mt-1 ml-5 text-muted-color">{{ personal_info.rank?.name }} {{ personal_info?.first_name }} {{ personal_info?.middle_name }} {{ personal_info?.last_name }} {{ personal_info?.suffix }} {{ personal_info.serial_no }} PCG</div>
                                        </div>
                    
                                    </li>
                                    <li class="flex flex-col md:flex-row md:items-center md:justify-between mb-2">
                                        <div>
                                            <span class="font-medium mb-1 md:mb-0">Email</span>
                                            <div class="mt-1 ml-5 text-muted-color">{{ user.email }}</div>
                                        </div>
                                        
                                    </li>
                                </ul>
                        </div>
                    </div>
                </div>

                <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <Record
                    :ranks ="ranks"
                    />
                <!-- <div class="p-4 sm:p-8">
                    <UpdateProfileInformationForm
                        :must-verify-email="mustVerifyEmail"
                        :status="status"
                        class="max-w-xl"
                    />
                </div>

                <div class="p-4 sm:p-8">
                    <UpdatePasswordForm class="max-w-xl" />
                </div> -->
                </div>

                

                <!-- <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                    <DeleteUserForm class="max-w-xl" />
                </div> -->
            </div>
        </div>
    </AuthenticatedLayout>
</template>
