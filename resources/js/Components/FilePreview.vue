<!-- components/FilePreview.vue -->
<template>
  <Dialog
    v-model:visible="visible"
    header="File Preview"
    :modal="true"
    :style="{ width: '80vw', height: '80vh' }"
  >
    <div class="h-full w-full flex flex-col items-center">
      
      <!-- File Name Display -->
      <div class="text-lg font-semibold mb-4 text-center text-gray-800">
        {{ fileName }}
      </div>

      <div class="flex-1 w-full flex justify-center items-center">
        <template v-if="isImage">
          <img :src="filePath" alt="Preview" class="max-h-full max-w-full object-contain" />
        </template>

        <template v-else-if="isPDF">
          <iframe :src="filePath" class="w-full h-[75vh] border" frameborder="0"></iframe>
        </template>

        <template v-else-if="isOffice">
          <iframe :src="`https://docs.google.com/gview?url=${fullUrl}&embedded=true`" class="w-full h-[75vh] border" frameborder="0"></iframe>
        </template>

        <template v-else>
          <div class="text-center">
            <p class="mb-4 text-gray-600">Unsupported file type.</p>
            <a :href="filePath" :download="fileName" class="text-blue-600 underline">Download {{ fileName }}</a>
          </div>
        </template>
      </div>
    </div>
  </Dialog>
</template>


<script setup>
import { ref, computed } from 'vue'
import Dialog from 'primevue/dialog'

const visible = ref(false)
const filePath = ref('')
const fileName = ref('')

const show = (path, name = '') => {
  filePath.value = path
  fileName.value = name || path.split('/').pop()
  visible.value = true
}

defineExpose({ show })

const isImage = computed(() => /\.(jpe?g|png|gif|bmp|webp)$/i.test(filePath.value))
const isPDF = computed(() => /\.pdf$/i.test(filePath.value))
const isOffice = computed(() => /\.(docx?|xlsx?|pptx?)$/i.test(filePath.value))

const fullUrl = computed(() => {
  const baseUrl = window.location.origin
  let path = filePath.value

  if (!/^https?:\/\//.test(path)) {
    if (!path.startsWith('/')) path = '/' + path
    if (!path.startsWith('/storage/')) path = `/storage${path}`
    return `${baseUrl}${path}`
  }

  return path
})
</script>
