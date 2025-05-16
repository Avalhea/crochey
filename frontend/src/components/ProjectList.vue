<template>
  <div class="page-container">
    <div class="header">
      <h2>Quests</h2>
      <button class="btn-primary" @click="showAddForm = true" v-if="!showAddForm">Add New Project</button>
    </div>

    <div class="filters" v-if="!showAddForm && !editingProject">
      <div class="search">
        <input 
          type="text" 
          v-model="filters.search" 
          placeholder="Search projects..."
        >
      </div>
      <div class="filter-group">
        <select v-model="filters.status">
          <option value="">All Statuses</option>
          <option value="Not started">Not Started</option>
          <option value="WIP">WIP</option>
          <option value="Finished">Finished</option>
        </select>
        <select v-model="filters.difficulty">
          <option value="">All Difficulties</option>
          <option value="Beginner">Beginner</option>
          <option value="Easy">Easy</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Advanced">Advanced</option>
        </select>
        <select v-model="sortBy">
          <option value="name">Sort by Name</option>
          <option value="startDate">Sort by Start Date</option>
          <option value="status">Sort by Status</option>
          <option value="difficulty">Sort by Difficulty</option>
        </select>
      </div>
    </div>

    <ProjectForm 
      v-if="showAddForm"
      @submit="handleAdd"
      @cancel="showAddForm = false"
    />

    <ProjectForm 
      v-else-if="editingProject"
      :project="editingProject"
      :edit-mode="true"
      @submit="handleUpdate"
      @cancel="editingProject = null"
    />

    <div v-else>
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">{{ error }}</div>
      <div v-else>
        <div>Number of projects: {{ filteredAndSortedProjects.length }} 
          <span v-if="filteredAndSortedProjects.length !== projects.length">
            ({{ projects.length }} total)
          </span>
        </div>
        <div class="item-grid">
          <div 
            v-for="project in filteredAndSortedProjects" 
            :key="project.id" 
            class="card"
            @click="$router.push(`/projects/${project.id}`)"
            style="cursor: pointer;"
          >
            <div class="card-actions" @click.stop>
              <button class="btn-icon" @click="editingProject = project" title="Edit">✏️</button>
              <button class="btn-icon" @click="handleDelete(project)" title="Delete">🗑️</button>
            </div>
            <div class="card-image">
              <img 
                :src="getImageUrl(project)" 
                :alt="project.name"
                @error="$event.target.src = DEFAULT_PROJECT_IMAGE"
              >
            </div>
            <div class="card-content">
              <h3>{{ project.name }}</h3>
              <div class="card-details">
                <p><strong>Status:</strong> {{ project.Status }}</p>
                <p><strong>Difficulty:</strong> {{ project.Difficulty }}</p>
                <p v-if="project.Status === 'WIP' || project.Status === 'Finished'">
                  <strong>Start Date:</strong> {{ formatDate(project.started_at) }}
                </p>
                <p v-if="project.Status === 'Finished' && project.finished_at">
                  <strong>Completion Date:</strong> {{ formatDate(project.finished_at) }}
                </p>
              </div>
              <p class="item-notes">{{ project.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import ProjectForm from './ProjectForm.vue'

const DEFAULT_PROJECT_IMAGE = 'https://placehold.co/400x300/e9ecef/495057?text=Project+Image'

const projects = ref([])
const loading = ref(true)
const error = ref(null)
const showAddForm = ref(false)
const editingProject = ref(null)
const filters = ref({
  search: '',
  status: '',
  difficulty: ''
})
const sortBy = ref('name')

const isValidImageUrl = (url) => {
  if (!url) return false
  return !url.includes('example.com')
}

const getImageUrl = (project) => {
  return isValidImageUrl(project.imageUrl) ? project.imageUrl : DEFAULT_PROJECT_IMAGE
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  return new Date(dateString).toLocaleDateString()
}

const filteredAndSortedProjects = computed(() => {
  let filtered = projects.value.filter(project => {
    const matchesSearch = !filters.value.search || 
      (project.name?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      project.description?.toLowerCase().includes(filters.value.search.toLowerCase()))
    
    const matchesStatus = !filters.value.status || project.Status === filters.value.status
    const matchesDifficulty = !filters.value.difficulty || project.Difficulty === filters.value.difficulty
    
    return matchesSearch && matchesStatus && matchesDifficulty
  })

  return filtered.sort((a, b) => {
    if (sortBy.value === 'startDate') {
      return new Date(b.started_at || 0) - new Date(a.started_at || 0)
    }
    return (a[sortBy.value] || '').localeCompare(b[sortBy.value] || '')
  })
})

const fetchProjects = async () => {
  try {
    console.log('Fetching projects...')
    const response = await fetch('/api/projects')
    console.log('Response status:', response.status)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    const data = await response.json()
    console.log('Raw data:', data)
    
    // Extract the projects from the 'member' property
    let projectArray = data.member || []
    console.log('Project array before transform:', projectArray)

    // Transform the data to ensure consistent property names
    const transformedProjects = projectArray.map(project => ({
      id: project.id,
      name: project.name || '',
      description: project.description || '',
      Status: project.Status || '',
      Difficulty: project.Difficulty || '',
      started_at: project.started_at || null,
      finished_at: project.finished_at || null,
      imageUrl: project.imageUrl || ''
    }))
    
    console.log('Transformed projects:', transformedProjects)
    projects.value = transformedProjects
    console.log('Final projects.value:', projects.value)
  } catch (e) {
    error.value = 'Error loading projects: ' + e.message
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

const handleAdd = async (formData) => {
  try {
    const response = await fetch('/api/projects', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to add project')
    
    await fetchProjects()
    showAddForm.value = false
  } catch (e) {
    error.value = 'Error adding project: ' + e.message
  }
}

const handleUpdate = async (formData) => {
  try {
    const response = await fetch(`/api/projects/${editingProject.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to update project')
    
    await fetchProjects()
    editingProject.value = null
  } catch (e) {
    error.value = 'Error updating project: ' + e.message
  }
}

const handleDelete = async (project) => {
  if (!confirm('Are you sure you want to delete this project?')) return
  
  try {
    const response = await fetch(`/api/projects/${project.id}`, {
      method: 'DELETE',
    })
    
    if (!response.ok) throw new Error('Failed to delete project')
    
    await fetchProjects()
  } catch (e) {
    error.value = 'Error deleting project: ' + e.message
  }
}

onMounted(() => {
  console.log('Component mounted, fetching projects...')
  fetchProjects()
})
</script>

