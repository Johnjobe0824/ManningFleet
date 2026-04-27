    <script setup>
    import { ref, computed, onMounted } from "vue";
    import { useForm } from "@inertiajs/vue3";
    import DataTable from "primevue/datatable";
    import Column from "primevue/column";
    import Button from "primevue/button";
    import FileUpload from "primevue/fileupload";
    import Tag from "primevue/tag";
    import Dialog from "primevue/dialog";
    import InputText from "primevue/inputtext";
    import IconField from "primevue/iconfield";
    import InputIcon from "primevue/inputicon";
    import { useToast } from "primevue/usetoast";
    import axios from "axios";
    import FilePreview from "@/Components/FilePreview.vue";

    const props = defineProps({
        documents: Object,
        user: Object,
    });

    const toast = useToast();
    const previewRef = ref(null);

    // ── Documents state ──
    const documents = ref([...(props.documents ?? [])]);
    const tableDocuments = ref([]);
    const loadingTable = ref(false);

    // ── Pagination ──
    const totalRecords = ref(0);
    const currentPage = ref(1);
    const rowsPerPage = ref(10);

    // ── Search ──
    const searchQuery = ref("");
    const searchTimeout = ref(null);

    // ── Upload ──
    const form = useForm({ file: null });
    const showConfirmDialog = ref(false);
    const selectedFile = ref(null);

    // ── Grid search (client-side) ──
    const filteredDocuments = computed(() => {
        if (!searchQuery.value.trim()) return documents.value;
        const q = searchQuery.value.toLowerCase();
        return documents.value.filter(
            (doc) =>
                doc.name?.toLowerCase().includes(q) ||
                doc.status?.toLowerCase().includes(q)
        );
    });

    // ── Fetch documents for table (server-side) ──
    const fetchDocuments = async (page = 1, search = "") => {
        loadingTable.value = true;
        try {
            const response = await axios.get(route("documents.fetch"), {
                params: {
                    page,
                    search,
                    per_page: rowsPerPage.value,
                    user_id: props.user.id,
                },
            });

            tableDocuments.value = response.data.data;
            totalRecords.value = response.data.total;
            currentPage.value = response.data.current_page;
        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Fetch Failed",
                detail: "Could not load documents.",
                life: 4000,
            });
        } finally {
            loadingTable.value = false;
        }
    };

    onMounted(() => fetchDocuments());

    // ── Debounced search ──
    const onSearch = () => {
        clearTimeout(searchTimeout.value);
        searchTimeout.value = setTimeout(() => {
            fetchDocuments(1, searchQuery.value);
        }, 300);
    };

    // ── Table page change ──
    const onPageChange = (event) => {
        fetchDocuments(event.page + 1, searchQuery.value);
    };

    // ── Upload logic ──
    const upload = (event) => {
        const file = event.files?.[0];
        if (!file) return;
        selectedFile.value = file;
        showConfirmDialog.value = true;
    };

    const confirmUpload = async () => {
        if (!selectedFile.value) return;

        const formData = new FormData();
        formData.append("file", selectedFile.value);
        formData.append("name", selectedFile.value.name);
        formData.append("user_id", props.user.id);
        formData.append("status", "pending");

        try {
            const response = await axios.post(route("documents.store"), formData, {
                headers: { "Content-Type": "multipart/form-data" },
            });

            const newDoc = response.data.document || null;
            if (newDoc) {
                documents.value.unshift(newDoc); // update grid
            }

            // Refresh table to include new upload
            await fetchDocuments(currentPage.value, searchQuery.value);

            toast.add({
                severity: "success",
                summary: "Success",
                detail: "File uploaded successfully!",
                life: 3000,
            });

            selectedFile.value = null;
            showConfirmDialog.value = false;
        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Upload Failed",
                detail: error.response?.data?.message || "Something went wrong.",
                life: 5000,
            });
        }
    };

    const cancelUpload = () => {
        selectedFile.value = null;
        showConfirmDialog.value = false;
    };

    // ── Delete logic ──
    const deleteDocument = async (id) => {
        if (!confirm("Are you sure you want to delete this document?")) return;

        try {
            await axios.delete(route("documents.destroy", id));

            // Remove from grid
            documents.value = documents.value.filter((doc) => doc.id !== id);

            // Refresh table
            await fetchDocuments(currentPage.value, searchQuery.value);

            toast.add({
                severity: "info",
                summary: "Deleted",
                detail: "Document deleted.",
                life: 3000,
            });
        } catch (error) {
            toast.add({
                severity: "error",
                summary: "Delete Failed",
                detail: "Could not delete document.",
                life: 5000,
            });
        }
    };

    // ── Helpers ──
    const statusSeverity = (status) => {
        switch (status) {
            case "pending":   return "warn";
            case "approved":  return "success";
            case "rejected":  return "danger";
            default:          return "warning";
        }
    };

    const statusBorderClass = (status) => {
        switch (status) {
            case "approved": return "border-green-500";
            case "rejected": return "border-red-500";
            case "pending":  return "border-yellow-500";
            default:         return "border-gray-300";
        }
    };

    const isImage = (path) => /\.(jpg|jpeg|png|gif|bmp|webp)$/i.test(path);

    const getFileIcon = (path) => {
        const ext = path?.split(".").pop().toLowerCase();
        switch (ext) {
            case "pdf":  return "pi pi-file-pdf";
            case "doc":
            case "docx": return "pi pi-file-word";
            case "xls":
            case "xlsx": return "pi pi-file-excel";
            case "zip":
            case "rar":  return "pi pi-file-zip";
            default:     return "pi pi-file";
        }
    };
    </script>

    <template>
        <div class="space-y-6">

            <!-- ── GRID VIEW ── -->
            <DataView :value="filteredDocuments" layout="grid">
                <template #header>
                    <div class="flex items-center justify-between flex-wrap gap-2">
                        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                            Documents
                        </h2>
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
                            <div
                                :class="['p-6 rounded border flex flex-col justify-between', statusBorderClass(item.status)]"
                                style="height: 100%; min-height: 320px"
                            >
                                <div class="flex justify-center rounded p-4">
                                    <div class="relative mx-auto">
                                        <img
                                            v-if="isImage(item.path)"
                                            class="rounded w-full object-contain"
                                            :src="`/storage/${item.path}`"
                                            :alt="item.name"
                                            style="max-height: 200px"
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
                                        <div class="text-lg font-medium break-words line-clamp-2">
                                            {{ item.name }}
                                        </div>
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

            <!-- ── SEARCHABLE TABLE VIEW (server-side) ── -->
            <!-- ── SEARCHABLE TABLE VIEW (server-side) ── -->
        <div v-if="searchQuery.trim()" class="card">
                <DataTable
                    :value="tableDocuments"
                    :loading="loadingTable"
                    lazy
                    paginator
                    :rows="rowsPerPage"
                    :totalRecords="totalRecords"
                    :first="(currentPage - 1) * rowsPerPage"
                    @page="onPageChange"
                    :rowsPerPageOptions="[5, 10, 25]"
                    tableStyle="min-width: 40rem"
                    stripedRows
                >
                    <template #header>
                        <div class="flex items-center justify-between">
                            <span class="font-semibold text-lg">My Uploaded Documents</span>
                            <IconField>
                                <InputIcon>
                                    <i class="pi pi-search" />
                                </InputIcon>
                                <InputText
                                    v-model="searchQuery"
                                    @input="onSearch"
                                    placeholder="Search by name or status…"
                                />
                            </IconField>
                        </div>
                    </template>

                    <template #empty>
                        <div class="text-center py-6 text-gray-400">No documents found.</div>
                    </template>

                    <template #loading>
                        <div class="text-center py-6 text-gray-400">Loading documents…</div>
                    </template>

                    <Column header="#" style="width: 3rem">
                        <template #body="slotProps">
                            {{ (currentPage - 1) * rowsPerPage + slotProps.index + 1 }}
                        </template>
                    </Column>

                    <Column header="File" style="width: 4rem">
                        <template #body="slotProps">
                            <img
                                v-if="isImage(slotProps.data.path)"
                                :src="`/storage/${slotProps.data.path}`"
                                class="w-10 h-10 object-cover rounded"
                            />
                            <i
                                v-else
                                :class="getFileIcon(slotProps.data.path)"
                                class="text-2xl text-gray-600"
                            ></i>
                        </template>
                    </Column>

                    <Column field="name" header="Document Name" sortable />

                    <Column field="status" header="Status" sortable>
                        <template #body="slotProps">
                            <Tag
                                :severity="statusSeverity(slotProps.data.status)"
                                :value="slotProps.data.status"
                            />
                        </template>
                    </Column>

                    <Column header="Actions" style="min-width: 10rem">
                        <template #body="slotProps">
                            <div class="flex gap-2">
                                <Button
                                    icon="pi pi-eye"
                                    label="Preview"
                                    size="small"
                                    severity="info"
                                    text
                                    @click="previewRef?.show(`/storage/${slotProps.data.path}`, slotProps.data.name)"
                                />
                                <Button
                                    v-show="can(['delete document'])"
                                    icon="pi pi-trash"
                                    label="Delete"
                                    size="small"
                                    severity="danger"
                                    text
                                    @click="deleteDocument(slotProps.data.id)"
                                />
                            </div>
                        </template>
                    </Column>
                </DataTable>
            </div>

            <!-- ── CONFIRM UPLOAD DIALOG ── -->
            <Dialog header="Confirm Upload" v-model:visible="showConfirmDialog" modal>
                <p>
                    Are you sure you want to upload
                    <strong>{{ selectedFile?.name }}</strong>?
                </p>
                <div class="flex justify-end gap-2 mt-4">
                    <Button label="Cancel" icon="pi pi-times" @click="cancelUpload" severity="secondary" text />
                    <Button label="Upload" icon="pi pi-upload" @click="confirmUpload" severity="primary" />
                </div>
            </Dialog>

            <FilePreview ref="previewRef" />
        </div>
    </template>