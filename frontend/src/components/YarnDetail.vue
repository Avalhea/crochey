<template>
  <div class="page-container">
    <div class="header">
      <div class="header-left">
        <button class="btn-icon" @click="$router.back()" title="Back">←</button>
        <h2>{{ yarn?.name || 'Yarn Details' }}</h2>
      </div>
      <div class="header-actions">
        <button class="btn-primary btn-danger" @click="handleDelete" v-if="!editingYarn">Delete Yarn</button>
        <button class="btn-primary" @click="editingYarn = yarn" v-if="!editingYarn">Edit Yarn</button>
      </div>
    </div>

    <YarnForm 
      v-if="editingYarn"
      :yarn="editingYarn"
      :edit-mode="true"
      @submit="handleUpdate"
      @cancel="editingYarn = null"
    />

    <div v-else>
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">{{ error }}</div>
      <div v-else-if="yarn" class="detail-view">
        <div class="detail-image">
          <img 
            :src="getImageUrl(yarn)" 
            :alt="yarn.name"
            @error="$event.target.src = DEFAULT_YARN_IMAGE"
          >
        </div>
        <div class="detail-content">
          <h2>{{ yarn.name }}</h2>
          <div class="detail-info">
            <p><strong>Brand:</strong> {{ yarn.brand }}</p>
            <p><strong>Color:</strong> {{ yarn.color }}</p>
            <p><strong>Weight:</strong> {{ yarn.Weight }}</p>
            <p><strong>Fiber:</strong> {{ yarn.FiberContent }}</p>
            <p><strong>Quantity:</strong> {{ yarn.quantity }}</p>
          </div>
          <p v-if="yarn.notes" class="detail-description">{{ yarn.notes }}</p>
        </div>
      </div>
      <div v-else>Yarn not found.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import YarnForm from './YarnForm.vue'

const DEFAULT_YARN_IMAGE = 'https://placehold.co/400x300/e9ecef/495057?text=Yarn+Image'
const route = useRoute()
const router = useRouter()

const yarn = ref(null)
const loading = ref(true)
const error = ref(null)
const editingYarn = ref(null)

const getImageUrl = (yarn) => {
  if (!yarn?.imageUrl || yarn.imageUrl.includes('example.com')) {
    return DEFAULT_YARN_IMAGE
  }
  return yarn.imageUrl
}

const fetchYarn = async () => {
  try {
    loading.value = true
    const response = await fetch(`/api/yarns/${route.params.id}`)
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
    const data = await response.json()
    yarn.value = data
  } catch (e) {
    error.value = 'Error loading yarn: ' + e.message
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

const handleUpdate = async (formData) => {
  try {
    const response = await fetch(`/api/yarns/${yarn.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to update yarn')
    
    await fetchYarn()
    editingYarn.value = null
  } catch (e) {
    error.value = 'Error updating yarn: ' + e.message
  }
}

const handleDelete = async () => {
  if (!confirm('Are you sure you want to delete this yarn?')) return
  
  try {
    const response = await fetch(`/api/yarns/${yarn.value.id}`, {
      method: 'DELETE',
    })
    
    if (!response.ok) throw new Error('Failed to delete yarn')
    
    router.push('/yarns')
  } catch (e) {
    error.value = 'Error deleting yarn: ' + e.message
  }
}

onMounted(fetchYarn)
</script>

<style scoped>
.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
}

.header-actions {
  display: flex;
  gap: 12px;
}

.btn-primary.btn-danger {
  background: #dc3545;
  color: white;
}

.btn-primary.btn-danger:hover {
  background: #c82333;
}

.detail-view {
  background: white;
  border-radius: 8px;
  box-shadow: var(--card-shadow);
  overflow: hidden;
}

.detail-image {
  width: 100%;
  height: 400px;
  overflow: hidden;
  background-color: #e9ecef;
}

.detail-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.detail-content {
  padding: 24px;
}

.detail-info {
  margin: 24px 0;
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.detail-description {
  font-size: 1.1rem;
  line-height: 1.6;
  color: var(--text-color);
}
</style> 