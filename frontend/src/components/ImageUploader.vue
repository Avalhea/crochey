<template>
  <div class="image-uploader">
    <!-- Drag and Drop Zone -->
    <div 
      class="dropzone"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="handleFileDrop"
      :class="{ 'active': isDragging }"
    >
      <div class="dropzone-content">
        <i class="fas fa-cloud-upload-alt"></i>
        <p>Drag and drop image here</p>
        <p>or</p>
        <label for="file-upload" class="btn-primary">Select File</label>
        <input 
          type="file" 
          id="file-upload" 
          accept="image/*" 
          @change="handleFileSelect" 
          class="file-input"
        >
      </div>
    </div>
    
    <!-- Upload Progress -->
    <div v-if="uploading" class="upload-progress">
      <div class="progress-bar">
        <div class="progress-bar-inner" :style="{ width: uploadProgress + '%' }"></div>
      </div>
      <p>Uploading... {{ uploadProgress }}%</p>
    </div>

    <!-- URL Input -->
    <div class="url-input">
      <input 
        type="text" 
        v-model="imageUrl" 
        placeholder="Or enter image URL"
        class="form-input"
      >
      <button 
        class="btn-primary" 
        @click="handleUrlSubmit"
        :disabled="!imageUrl"
      >
        Use URL
      </button>
    </div>

    <!-- Preview -->
    <div v-if="previewUrl" class="image-preview">
      <img :src="previewUrl" alt="Preview" class="preview-image">
      <button class="btn-delete" @click="clearImage" title="Remove Image">×</button>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: String,
    default: ''
  },
  type: {
    type: String,
    default: 'project',
    validator: (value) => ['project', 'yarn'].includes(value)
  }
})

const emit = defineEmits(['update:modelValue', 'upload-complete'])

const isDragging = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const imageUrl = ref('')
const previewUrl = ref('')

// Watch for changes in modelValue
watch(() => props.modelValue, (newValue) => {
  if (newValue) {
    previewUrl.value = newValue
    imageUrl.value = newValue
  }
}, { immediate: true })

const handleFileDrop = (event) => {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    uploadFile(file)
  }
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    uploadFile(file)
  }
}

const uploadFile = async (file) => {
  if (!file.type.match('image.*')) {
    console.warn('File is not an image:', file.name)
    return
  }

  uploading.value = true
  uploadProgress.value = 0
  
  try {
    const formData = new FormData()
    formData.append('image', file)
    
    const endpoint = props.type === 'yarn' ? '/api/yarn/image' : '/api/image/upload'
    const response = await fetch(endpoint, {
      method: 'POST',
      body: formData
    })
    
    if (!response.ok) {
      throw new Error('Failed to upload image')
    }
    
    const data = await response.json()
    uploadProgress.value = 100
    
    // Update the preview and emit the new URL
    previewUrl.value = data.imageUrl
    emit('update:modelValue', data.imageUrl)
    emit('upload-complete', data.imageUrl)
  } catch (error) {
    console.error('Error uploading file:', error)
  } finally {
    uploading.value = false
  }
}

const handleUrlSubmit = () => {
  if (imageUrl.value) {
    previewUrl.value = imageUrl.value
    emit('update:modelValue', imageUrl.value)
    emit('upload-complete', imageUrl.value)
  }
}

const clearImage = () => {
  previewUrl.value = ''
  imageUrl.value = ''
  emit('update:modelValue', '')
  emit('upload-complete', '')
}
</script>

<style scoped>
.image-uploader {
  width: 100%;
}

.dropzone {
  border: 2px dashed var(--border-color);
  border-radius: 8px;
  padding: 2rem;
  text-align: center;
  margin-bottom: 1rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dropzone.active {
  border-color: var(--primary-color);
  background-color: rgba(var(--primary-rgb), 0.05);
}

.dropzone-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 0.5rem;
}

.file-input {
  display: none;
}

.upload-progress {
  margin-bottom: 1rem;
}

.progress-bar {
  height: 10px;
  background-color: var(--border-color);
  border-radius: 5px;
  overflow: hidden;
  margin-bottom: 0.5rem;
}

.progress-bar-inner {
  height: 100%;
  background-color: var(--primary-color);
  transition: width 0.3s ease;
}

.url-input {
  display: flex;
  gap: 0.5rem;
  margin-bottom: 1rem;
}

.form-input {
  flex: 1;
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background: var(--background-color);
  color: var(--text-color);
}

.image-preview {
  position: relative;
  width: 100%;
  max-width: 300px;
  margin: 0 auto;
}

.preview-image {
  width: 100%;
  height: auto;
  border-radius: 8px;
  box-shadow: var(--card-shadow);
}

.btn-delete {
  position: absolute;
  top: 0.5rem;
  right: 0.5rem;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  border-radius: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 18px;
  transition: background-color 0.2s;
}

.btn-delete:hover {
  background: rgba(0, 0, 0, 0.7);
}
</style> 