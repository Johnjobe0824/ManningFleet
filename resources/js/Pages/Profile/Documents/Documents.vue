<script setup>
import { ref } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import DataTable from 'primevue/datatable';
import Column from 'primevue/column';
import Button from 'primevue/button';
import FileUpload from 'primevue/fileupload';
import Tag from 'primevue/tag';
import Dialog from 'primevue/dialog';
import { useToast } from 'primevue/usetoast';
import axios from 'axios';
import FilePreview from '@/Components/FilePreview.vue';

const props = defineProps({
    documents: Object,
    user: Object
});

const toast = useToast();
const previewRef = ref(null);


// ✅ Make documents reactive
const documents = ref([...props.documents]);

const form = useForm({
    file: null
});

const showConfirmDialog = ref(false);
const selectedFile = ref(null);

// Upload logic
const upload = (event) => {
    const file = event.files?.[0];
    if (!file) return;

    selectedFile.value = file;
    showConfirmDialog.value = true; 
};

const confirmUpload = async () => {
    if (!selectedFile.value) return;

    const formData = new FormData();
    formData.append('file', selectedFile.value);
    formData.append('name', selectedFile.value.name);
    formData.append('user_id', props.user.id);
    formData.append('status', 'pending');

    try {
        const response = await axios.post(route('documents.store'), formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
            }
        });

        const newDoc = response.data.document || null;
        if (newDoc) {
            documents.value.unshift(newDoc); // ✅ Add new document
        }

        toast.add({
            severity: 'success',
            summary: 'Success',
            detail: 'File uploaded successfully!',
            life: 3000
        });

        selectedFile.value = null;
        showConfirmDialog.value = false;
    } catch (error) {
        const errorMessage =
            error.response?.data?.message ||
            'Something went wrong. Please try again.';

        toast.add({
            severity: 'error',
            summary: 'Upload Failed',
            detail: errorMessage,
            life: 5000
        });
    }
};

const cancelUpload = () => {
    selectedFile.value = null;
    showConfirmDialog.value = false;
};

// Delete logic
const deleteDocument = async (id) => {
    if (!confirm('Are you sure you want to delete this document?')) return;

    try {
        await axios.delete(route('documents.destroy', id));
        documents.value = documents.value.filter(doc => doc.id !== id);

        toast.add({
            severity: 'info',
            summary: 'Deleted',
            detail: 'Document deleted.',
            life: 3000
        });
    } catch (error) {
        toast.add({
            severity: 'error',
            summary: 'Delete Failed',
            detail: 'Could not delete document.',
            life: 5000
        });
    }
};

const statusSeverity = (status) => {
    switch (status) {
        case 'pending': return 'warn';
        case 'approved': return 'success';
        case 'rejected': return 'danger';
        default: return 'warning';
    }
};

const statusBorderClass = (status) => {
    switch (status) {
        case 'approved': return 'border-green-500';
        case 'rejected': return 'border-red-500';
        case 'pending': return 'border-yellow-500';
        default: return 'border-gray-300';
    }
};

const isImage = (path) => /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(path);

const getFileIcon = (path) => {
    const ext = path.split('.').pop().toLowerCase();
    switch (ext) {
        case 'pdf': return 'pi pi-file-pdf';
        case 'doc':
        case 'docx': return 'pi pi-file-word';
        case 'xls':
        case 'xlsx': return 'pi pi-file-excel';
        case 'ppt':
        case 'pptx': return 'pi pi-file';
        case 'zip':
        case 'rar': return 'pi pi-file-zip';
        case 'txt': return 'pi pi-file';
        default: return 'pi pi-file';
    }
};
</script>

<template>
    
    <div class="space-y-6">
        <DataView :value="documents" layout="grid">
            <template #header>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Documents</h2>
                <div class="flex justify-end">
                    
                    <FileUpload 
                        name="file"
                        mode="basic"
                        chooseLabel="Upload Document"
                        customUpload
                        @uploader="upload"
                        accept=".pdf,.doc,.docx,.jpg,.png"
                        auto
                    />
                </div>
            </template>

            <template #grid="slotProps">
                    <div class="grid grid-cols-12 gap-4">
                <div
                v-for="(item, index) in slotProps.items"
                :key="index"
                class="col-span-12 sm:col-span-6 md:col-span-4 xl:col-span-4 p-2"
                >
                <div :class="['p-6 rounded border flex flex-col justify-between', statusBorderClass(item.status)]"
                    style="height: 100%; min-height: 320px;" >
                    <div class="flex justify-center rounded p-4">
                    <div class="relative mx-auto">
                        <img
                        v-if="isImage(item.path)"
                        class="rounded w-full object-contain"
                        :src="`/storage/documents/${item.path}`"
                        :alt="item.name"
                        style="max-height: 200px;"
                        />
                        <div
                        v-else
                        class="flex items-center justify-center w-32 h-32 bg-gray-100 rounded"
                        >
                        <i :class="getFileIcon(item.path)" class="text-6xl text-gray-700"></i>
                        </div>
                    </div>
                    </div>

                    <div class="pt-4 flex flex-col justify-between flex-grow">
                    <div class="flex flex-row justify-between items-start gap-2">
                        <div class="text-lg font-medium">{{ item.name }}</div>
                        <Tag :severity="statusSeverity(item.status)" :value="item.status" />
                    </div>
                    <div class="flex mt-4 gap-2">
                        <Button
                            label="Preview"
                            icon="pi pi-eye"
                            @click="previewRef?.show(`/storage/${item.path}`, item.name)"
                            severity="info"
                            text
                        />

                        <Button
                            v-show="can(['delete document'])" 
                            label="Delete"
                            icon="pi pi-trash"
                            @click="deleteDocument(item.id)"
                            severity="secondary"
                            text
                        />
                        </div>

                    </div>
                </div>
                </div>
            </div>
            </template>

        </DataView>

        <Dialog header="Confirm Upload" v-model:visible="showConfirmDialog" modal>
            <p>Are you sure you want to upload <strong>{{ selectedFile?.name }}</strong>?</p>
            <div class="flex justify-end gap-2 mt-4">
                <Button label="Cancel" icon="pi pi-times" @click="cancelUpload" severity="secondary" text />
                <Button label="Upload" icon="pi pi-upload" @click="confirmUpload" severity="primary" />
            </div>
        </Dialog>
        <FilePreview ref="previewRef" />

    </div>
</template>
