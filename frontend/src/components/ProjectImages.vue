<template>
  <div class="project-images">
    <h3>Project Images</h3>
    
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
        <p>Drag and drop images here</p>
        <p>or</p>
        <label for="file-upload" class="btn-primary">Select Files</label>
        <input 
          type="file" 
          id="file-upload" 
          accept="image/*" 
          @change="handleFileSelect" 
          multiple
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
    
    <!-- Add Image Form -->
    <div v-if="showAddForm" class="add-image-form">
      <input 
        type="text" 
        v-model="newImage.imageUrl" 
        placeholder="Image URL"
        class="form-input"
      >
      <input 
        type="text" 
        v-model="newImage.caption" 
        placeholder="Image Caption"
        class="form-input"
      >
      <div class="form-actions">
        <button class="btn-primary" @click="handleAddImage">Add Image</button>
        <button class="btn-secondary" @click="showAddForm = false">Cancel</button>
      </div>
    </div>
    <button v-else class="btn-primary url-button" @click="showAddForm = true">Add Image from URL</button>

    <!-- Images Grid -->
    <div class="images-grid">
      <div v-for="image in projectImages" :key="image.id" class="image-card">
        <img 
          :src="getAssetUrl(image.imageUrl)" 
          :alt="image.caption"
          @error="handleImageError"
          @click="openModal(image)"
        >
        <div class="image-caption">
          <div v-if="editingImage?.id === image.id" class="edit-caption">
            <input 
              type="text" 
              v-model="editingImage.caption" 
              @keyup.enter="handleUpdateCaption"
              @keyup.esc="editingImage = null"
              ref="captionInput"
            >
            <div class="edit-actions">
              <button class="btn-small" @click="handleUpdateCaption">Save</button>
              <button class="btn-small" @click="editingImage = null">Cancel</button>
            </div>
          </div>
          <p v-else @dblclick="startEditing(image)">{{ image.caption }}</p>
        </div>
        <button class="btn-delete" @click="handleDeleteImage(image.id)" title="Delete Image">×</button>
      </div>
    </div>

    <!-- Image Modal -->
    <div v-if="selectedImage" class="modal" @click="closeModal">
      <div class="modal-content" @click.stop>
        <button class="modal-close" @click="closeModal">×</button>
        <img :src="getAssetUrl(selectedImage.imageUrl)" :alt="selectedImage.caption">
        <div class="modal-caption">
          <div v-if="editingImage?.id === selectedImage.id" class="edit-caption">
            <input 
              type="text" 
              v-model="editingImage.caption" 
              @keyup.enter="handleUpdateCaption"
              @keyup.esc="editingImage = null"
              ref="modalCaptionInput"
            >
            <div class="edit-actions">
              <button class="btn-small" @click="handleUpdateCaption">Save</button>
              <button class="btn-small" @click="editingImage = null">Cancel</button>
            </div>
          </div>
          <p v-else @dblclick="startEditing(selectedImage)">{{ selectedImage.caption }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, nextTick } from 'vue'

const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true
  }
})

const projectImages = ref([])
const showAddForm = ref(false)
const editingImage = ref(null)
const isDragging = ref(false)
const uploading = ref(false)
const uploadProgress = ref(0)
const newImage = ref({
  imageUrl: '',
  caption: ''
})
const selectedImage = ref(null)

const fetchImages = async () => {
  try {
    const response = await fetch(`/api/projects/${props.projectId}`)
    if (!response.ok) throw new Error('Failed to fetch project images')
    const data = await response.json()
    projectImages.value = data.ProjectImage || []
  } catch (error) {
    console.error('Error fetching images:', error)
  }
}

const handleFileDrop = (event) => {
  isDragging.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    uploadFiles(files)
  }
}

const handleFileSelect = (event) => {
  const files = event.target.files
  if (files.length > 0) {
    uploadFiles(files)
  }
}

const uploadFiles = async (files) => {
  uploading.value = true
  uploadProgress.value = 0
  
  try {
    for (let i = 0; i < files.length; i++) {
      const file = files[i]
      
      // Only process image files
      if (!file.type.match('image.*')) {
        console.warn('Skipping non-image file:', file.name)
        continue
      }
      
      await uploadFile(file)
      uploadProgress.value = Math.round(((i + 1) / files.length) * 100)
    }
    
    await fetchImages()
  } catch (error) {
    console.error('Error in uploadFiles:', error)
    // You might want to show an error message to the user here
  } finally {
    uploading.value = false
  }
}

const uploadFile = async (file) => {
  try {
    console.log('Starting file upload for:', file.name)
    const formData = new FormData()
    formData.append('image', file)
    formData.append('projectId', props.projectId)
    formData.append('caption', file.name.split('.')[0]) // Use filename as caption
    
    console.log('FormData contents:')
    for (let pair of formData.entries()) {
      console.log(pair[0] + ': ' + (pair[1] instanceof File ? pair[1].name : pair[1]))
    }
    
    const response = await fetch(`/api/project/image`, {
      method: 'POST',
      body: formData
    })
    
    console.log('Response status:', response.status)
    const responseText = await response.text()
    console.log('Response text:', responseText)
    
    if (!response.ok) {
      let errorMessage = 'Failed to upload image'
      try {
        const errorData = JSON.parse(responseText)
        errorMessage = errorData.error || errorMessage
      } catch (e) {
        console.error('Error parsing error response:', e)
      }
      throw new Error(errorMessage)
    }
    
    const result = JSON.parse(responseText)
    console.log('Upload successful:', result)
    return result
  } catch (error) {
    console.error('Error uploading file:', error)
    throw error
  }
}

const handleAddImage = async () => {
  try {
    console.log('Adding image with URL:', newImage.value.imageUrl)
    console.log('Project ID:', props.projectId)
    
    const response = await fetch(`/api/project/image`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      },
      body: JSON.stringify({
        imageUrl: newImage.value.imageUrl,
        caption: newImage.value.caption,
        projectId: props.projectId
      })
    })
    
    console.log('Response status:', response.status)
    const responseText = await response.text()
    console.log('Response text:', responseText)
    
    if (!response.ok) {
      let errorMessage = 'Failed to add image'
      try {
        const errorData = JSON.parse(responseText)
        errorMessage = errorData.error || errorMessage
      } catch (e) {
        console.error('Error parsing error response:', e)
      }
      throw new Error(errorMessage)
    }
    
    await fetchImages()
    showAddForm.value = false
    newImage.value = { imageUrl: '', caption: '' }
  } catch (error) {
    console.error('Error adding image:', error)
  }
}

const handleDeleteImage = async (imageId) => {
  if (!confirm('Are you sure you want to delete this image?')) return
  
  try {
    const response = await fetch(`/api/project/image/${imageId}`, {
      method: 'DELETE'
    })
    
    if (!response.ok) throw new Error('Failed to delete image')
    await fetchImages()
  } catch (error) {
    console.error('Error deleting image:', error)
  }
}

const startEditing = async (image) => {
  editingImage.value = { ...image }
  await nextTick()
  const input = selectedImage.value ? 
    document.querySelector('.modal-caption input') : 
    document.querySelector('.image-caption input')
  if (input) {
    input.focus()
  }
}

const handleUpdateCaption = async () => {
  if (!editingImage.value) return
  
  try {
    const response = await fetch(`/api/project/image/${editingImage.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        imageUrl: editingImage.value.imageUrl,
        caption: editingImage.value.caption,
        project: `/api/projects/${props.projectId}`
      })
    })
    
    if (!response.ok) throw new Error('Failed to update image')
    
    await fetchImages()
    editingImage.value = null
  } catch (error) {
    console.error('Error updating image:', error)
  }
}

const handleImageError = (event) => {
  event.target.src = 'https://placehold.co/400x300/e9ecef/495057?text=Image+Not+Found'
}

// Helper function to ensure absolute URLs
const getAssetUrl = (url) => {
  if (!url) return '';
  // If URL already starts with http:// or https:// or // (protocol-relative),
  // then it's already an absolute URL
  if (url.match(/^(https?:)?\/\//)) {
    return url;
  }
  // Otherwise, ensure it starts with a slash and return
  return url.startsWith('/') ? url : '/' + url;
}

const openModal = (image) => {
  selectedImage.value = image
  document.body.style.overflow = 'hidden'
}

const closeModal = () => {
  selectedImage.value = null
  document.body.style.overflow = ''
}

onMounted(fetchImages)
</script>

<style scoped>
.project-images {
  margin-top: 2rem;
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

.images-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
  gap: 1rem;
  margin-top: 1rem;
}

.image-card {
  position: relative;
  background: var(--card-background);
  border-radius: 8px;
  overflow: hidden;
  box-shadow: var(--card-shadow);
}

.image-card img {
  width: 100%;
  height: 200px;
  object-fit: cover;
}

.image-caption {
  padding: 1rem;
  background: var(--card-background);
}

.image-caption p {
  margin: 0;
  color: var(--text-color);
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

.add-image-form {
  background: var(--card-background);
  padding: 1rem;
  border-radius: 8px;
  margin-bottom: 1rem;
  box-shadow: var(--card-shadow);
}

.form-input {
  width: 100%;
  padding: 0.5rem;
  margin-bottom: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background: var(--background-color);
  color: var(--text-color);
}

.form-actions {
  display: flex;
  gap: 0.5rem;
}

.btn-secondary {
  background: var(--border-color);
  color: var(--text-color);
  border: none;
  padding: 8px 16px;
  border-radius: 4px;
  cursor: pointer;
}

.btn-small {
  padding: 4px 8px;
  font-size: 0.875rem;
}

.url-button {
  margin-bottom: 1rem;
}

.edit-caption {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-actions {
  display: flex;
  gap: 0.5rem;
}

.modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.8);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.modal-content {
  position: relative;
  max-width: 90vw;
  max-height: 90vh;
  background: var(--card-background);
  border-radius: 8px;
  overflow: hidden;
}

.modal-content img {
  max-width: 100%;
  max-height: 80vh;
  object-fit: contain;
}

.modal-close {
  position: absolute;
  top: 1rem;
  right: 1rem;
  background: rgba(0, 0, 0, 0.5);
  color: white;
  border: none;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 24px;
  transition: background-color 0.2s;
}

.modal-close:hover {
  background: rgba(0, 0, 0, 0.7);
}

.modal-caption {
  padding: 1rem;
  background: var(--card-background);
  text-align: center;
}

.image-card img {
  cursor: pointer;
  transition: transform 0.2s;
}

.image-card img:hover {
  transform: scale(1.02);
}

.edit-caption input {
  width: 100%;
  padding: 0.5rem;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background: var(--background-color);
  color: var(--text-color);
  margin-bottom: 0.5rem;
}

.edit-actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.btn-small {
  padding: 4px 8px;
  font-size: 0.875rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  background: var(--primary-color);
  color: white;
}

.btn-small:hover {
  opacity: 0.9;
}
</style> 