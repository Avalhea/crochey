<template>
  <div class="page-container">
    <div class="header">
      <div class="header-left">
        <button class="btn-icon" @click="$router.back()" title="Back">←</button>
        <h2>{{ project?.name || 'Project Details' }}</h2>
      </div>
      <button class="btn-primary" @click="editingProject = project" v-if="!editingProject">Edit Project</button>
    </div>

    <ProjectForm 
      v-if="editingProject"
      :project="editingProject"
      :edit-mode="true"
      @submit="handleUpdate"
      @cancel="editingProject = null"
    />

    <div v-else>
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">{{ error }}</div>
      <div v-else-if="project" class="detail-view">
        <div class="detail-image">
          <img 
            :src="getImageUrl(project)" 
            :alt="project.name"
            @error="$event.target.src = DEFAULT_PROJECT_IMAGE"
          >
        </div>
        <div class="detail-content">
          <h2>{{ project.name }}</h2>
          <div class="detail-info">
            <p><strong>Status:</strong> {{ project.Status }}</p>
            <p><strong>Difficulty:</strong> {{ project.Difficulty }}</p>
            <p v-if="project.Status === 'WIP' || project.Status === 'Finished'">
              <strong>Start Date:</strong> {{ formatDate(project.started_at) }}
            </p>
            <p v-if="project.Status === 'Finished' && project.finished_at">
              <strong>Completion Date:</strong> {{ formatDate(project.finished_at) }}
            </p>
          </div>
          <p class="detail-description">{{ project.description }}</p>
        </div>
      </div>
      <div v-else>Project not found.</div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import ProjectForm from './ProjectForm.vue'

const DEFAULT_PROJECT_IMAGE = 'https://placehold.co/400x300/e9ecef/495057?text=Project+Image'
const route = useRoute()
const router = useRouter()

const project = ref(null)
const loading = ref(true)
const error = ref(null)
const editingProject = ref(null)

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const getImageUrl = (project) => {
  if (!project?.imageUrl || project.imageUrl.includes('example.com')) {
    return DEFAULT_PROJECT_IMAGE
  }
  return project.imageUrl
}

const fetchProject = async () => {
  try {
    loading.value = true
    const response = await fetch(`/api/projects/${route.params.id}`)
    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`)
    const data = await response.json()
    project.value = data
  } catch (e) {
    error.value = 'Error loading project: ' + e.message
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

const handleUpdate = async (formData) => {
  try {
    const response = await fetch(`/api/projects/${project.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to update project')
    
    await fetchProject()
    editingProject.value = null
  } catch (e) {
    error.value = 'Error updating project: ' + e.message
  }
}

onMounted(fetchProject)
</script>

<style scoped>
.header-left {
  display: flex;
  align-items: center;
  gap: 12px;
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