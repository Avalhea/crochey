<template>
  <div class="project-images">
    <h3>Project Images</h3>
    
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
    <button v-else class="btn-primary" @click="showAddForm = true">Add New Image</button>

    <!-- Images Grid -->
    <div class="images-grid">
      <div v-for="image in projectImages" :key="image.id" class="image-card">
        <img 
          :src="image.imageUrl" 
          :alt="image.caption"
          @error="handleImageError"
        >
        <div class="image-caption">
          <div v-if="editingImage?.id === image.id" class="edit-caption">
            <input 
              type="text" 
              v-model="editingImage.caption" 
              @keyup.enter="handleUpdateCaption"
              @keyup.esc="editingImage = null"
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  projectId: {
    type: [String, Number],
    required: true
  }
})

const projectImages = ref([])
const showAddForm = ref(false)
const editingImage = ref(null)
const newImage = ref({
  imageUrl: '',
  caption: ''
})

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

const handleAddImage = async () => {
  try {
    const response = await fetch(`/api/project/image`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        ...newImage.value,
        project: `/api/projects/${props.projectId}`
      })
    })
    
    if (!response.ok) throw new Error('Failed to add image')
    
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

const startEditing = (image) => {
  editingImage.value = { ...image }
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

onMounted(fetchImages)
</script>

<style scoped>
.project-images {
  margin-top: 2rem;
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

.edit-caption {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.edit-caption input {
  width: 100%;
  padding: 4px 8px;
  border: 1px solid var(--border-color);
  border-radius: 4px;
  background: var(--background-color);
  color: var(--text-color);
}

.edit-actions {
  display: flex;
  gap: 0.5rem;
}
</style> 