<template>
  <div class="page-container">
    <Notification
      :show="notification.show"
      :message="notification.message"
      :type="notification.type"
      @close="notification.show = false"
    />
    
    <div class="header">
      <h2>Quests</h2>
      <button class="btn-primary" @click="showAddForm = true" v-if="!showAddForm">Add New Project</button>
    </div>

    <div class="filters" v-if="!showAddForm && !editingProject">
      <div class="search">
        <input 
          type="text" 
          v-model="filters.search" 
          placeholder="Search by name, description or tag..."
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
      <!-- Display currently active search tags -->
      <div class="active-filters" v-if="filters.search">
        <div class="search-tags">
          <span class="search-tag">
            Search: "{{ filters.search }}"
            <button type="button" class="tag-remove" @click="clearSearch">×</button>
          </span>
        </div>
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
              <div v-if="project.tags && project.tags.length" class="project-tags">
                <span 
                  v-for="(tag, index) in project.tags" 
                  :key="index" 
                  class="tag"
                  @click.stop="searchByTag(tag)"
                >
                  {{ typeof tag === 'string' ? tag : (tag.label || 'Unnamed tag') }}
                </span>
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
import Notification from './Notification.vue'

const DEFAULT_PROJECT_IMAGE = 'https://placehold.co/400x300/e9ecef/495057?text=Project+Image'

const projects = ref([])
const loading = ref(true)
const error = ref(null)
const showAddForm = ref(false)
const editingProject = ref(null)
const filters = ref({
  search: '',
  status: '',
  difficulty: '',
  tags: []
})
const sortBy = ref('name')

const notification = ref({
  show: false,
  message: '',
  type: 'success'
})

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

const availableTags = computed(() => {
  const tagSet = new Set()
  projects.value.forEach(project => {
    if (project.tags) {
      project.tags.forEach(tag => tagSet.add(tag))
    }
  })
  return Array.from(tagSet).sort()
})

const toggleTagFilter = (tag) => {
  const index = filters.value.tags.indexOf(tag)
  if (index === -1) {
    filters.value.tags.push(tag)
  } else {
    filters.value.tags.splice(index, 1)
  }
}

const filteredAndSortedProjects = computed(() => {
  let filtered = projects.value.filter(project => {
    // Search in name, description, and tags
    const matchesSearch = !filters.value.search || 
      project.name?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      project.description?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      (project.tags && project.tags.some(tag => {
        const tagLabel = typeof tag === 'string' ? tag : (tag.label || '');
        return tagLabel.toLowerCase().includes(filters.value.search.toLowerCase());
      }));
    
    const matchesStatus = !filters.value.status || project.Status === filters.value.status;
    const matchesDifficulty = !filters.value.difficulty || project.Difficulty === filters.value.difficulty;
    
    return matchesSearch && matchesStatus && matchesDifficulty;
  });

  return filtered.sort((a, b) => {
    if (sortBy.value === 'startDate') {
      return new Date(b.started_at || 0) - new Date(a.started_at || 0);
    }
    return (a[sortBy.value] || '').localeCompare(b[sortBy.value] || '');
  });
})

const clearSearch = () => {
  filters.value.search = '';
  // No need to refetch as we're filtering client-side
}

const fetchProjects = async () => {
  try {
    loading.value = true;
    console.log('Fetching all projects...');
    
    // Always fetch all projects and filter client-side
    const response = await fetch('/api/projects');
    
    console.log('Response status:', response.status);
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    // Get the raw text first to see what we're dealing with
    const rawText = await response.text();
    console.log('Raw response text:', rawText.substring(0, 200) + '...');
    
    // Parse it manually to handle potential JSON errors
    let data;
    try {
      data = JSON.parse(rawText);
      console.log('Parsed data:', data);
    } catch (jsonError) {
      console.error('JSON parse error:', jsonError);
      throw new Error(`Invalid JSON response: ${rawText.substring(0, 100)}...`);
    }
    
    // Handle various response formats
    let projectArray;
    if (Array.isArray(data)) {
      projectArray = data;
    } else if (data && typeof data === 'object') {
      if (Array.isArray(data.member)) {
        projectArray = data.member;
      } else if (Array.isArray(data.items)) {
        projectArray = data.items;
      } else if (data.id) {
        // Single project response
        projectArray = [data];
      } else {
        console.warn('Unexpected data structure:', data);
        projectArray = [];
      }
    } else {
      console.warn('Unable to extract projects from response:', data);
      projectArray = [];
    }
    
    console.log('Project array before transform:', projectArray);

    const transformedProjects = projectArray.map(project => {
      try {
        return {
          id: project.id,
          name: project.name || '',
          description: project.description || '',
          Status: project.Status || '',
          Difficulty: project.Difficulty || '',
          started_at: project.started_at || null,
          finished_at: project.finished_at || null,
          imageUrl: project.imageUrl || '',
          tags: Array.isArray(project.tags) ? project.tags : []
        };
      } catch (err) {
        console.error('Error transforming project:', project, err);
        return null;
      }
    }).filter(p => p !== null); // Remove any failed transformations
    
    console.log('Transformed projects:', transformedProjects);
    projects.value = transformedProjects;
    console.log('Final projects.value:', projects.value);
  } catch (e) {
    error.value = 'Error loading projects: ' + e.message;
    console.error('Error during fetch:', e);
  } finally {
    loading.value = false;
  }
}

const showNotification = (message, type = 'success') => {
  notification.value = {
    show: true,
    message,
    type
  }
}

const handleAdd = async (formData) => {
  try {
    // Store tags temporarily
    const tags = formData.tags
    // Remove tags from the data to be sent
    delete formData.tags

    console.log('Adding new project with data:', formData)
    const response = await fetch('/api/projects', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) {
      console.error('Failed to add project. Status:', response.status)
      const errorData = await response.json()
      console.error('Error details:', errorData)
      showNotification('Failed to add project. Please try again.', 'error')
      throw new Error(`Failed to add project: ${response.status}`)
    }
    
    const project = await response.json()
    console.log('Successfully added project:', project)

    // If we have tags, add them now
    if (tags && tags.length > 0) {
      const updateResponse = await fetch(`/api/projects/${project.id}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({ ...project, tags }),
      })

      if (!updateResponse.ok) {
        console.error('Failed to add tags. Status:', updateResponse.status)
        showNotification('Project created but failed to add tags.', 'warning')
      }
    }
    
    showNotification('Project successfully added!')
    await fetchProjects()
    showAddForm.value = false
  } catch (e) {
    console.error('Error in handleAdd:', e)
    showNotification('Error adding project: ' + e.message, 'error')
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
    
    if (!response.ok) {
      showNotification('Failed to update project. Please try again.', 'error')
      throw new Error('Failed to update project')
    }
    
    showNotification('Project successfully updated!')
    await fetchProjects()
    editingProject.value = null
  } catch (e) {
    showNotification('Error updating project: ' + e.message, 'error')
  }
}

const handleDelete = async (project) => {
  if (!confirm('Are you sure you want to delete this project?')) return
  
  try {
    const response = await fetch(`/api/projects/${project.id}`, {
      method: 'DELETE',
    })
    
    if (!response.ok) {
      showNotification('Failed to delete project. Please try again.', 'error')
      throw new Error('Failed to delete project')
    }
    
    showNotification('Project successfully deleted!')
    await fetchProjects()
  } catch (e) {
    showNotification('Error deleting project: ' + e.message, 'error')
  }
}

const searchByTag = (tag) => {
  // Extract the tag label, handling both string and object formats
  let tagLabel;
  if (typeof tag === 'string') {
    tagLabel = tag;
  } else if (tag && tag.label) {
    tagLabel = tag.label;
  } else {
    console.error('Invalid tag format for search:', tag);
    return; // Don't search if we can't get a tag label
  }
  
  // Set the search query to the tag label
  console.log('Searching by tag:', tagLabel);
  filters.value.search = tagLabel;
  // No need to refetch as we're filtering client-side
}

onMounted(() => {
  console.log('Component mounted, fetching projects...')
  fetchProjects()
})
</script>

<style scoped>
/* Project-specific styles */
.active-filters {
  margin-top: 12px;
}

.search-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.search-tag {
  display: inline-flex;
  align-items: center;
  background-color: #e7f3ff;
  color: #0366d6;
  border-radius: 20px;
  padding: 5px 12px;
  font-size: 14px;
  margin-right: 8px;
}

.tag-remove {
  background: none;
  border: none;
  color: #0366d6;
  margin-left: 5px;
  padding: 0 5px;
  cursor: pointer;
  font-size: 16px;
  font-weight: bold;
}

.tag-remove:hover {
  color: #034289;
}

/* Project tags */
.project-tags {
  display: flex;
  flex-wrap: wrap;
  gap: 6px;
  margin-top: 10px;
}

.tag {
  display: inline-block;
  background-color: #f1f8ff;
  color: #0366d6;
  border-radius: 12px;
  padding: 3px 10px;
  font-size: 12px;
  cursor: pointer;
}

.tag:hover {
  background-color: #e1efff;
}

/* Mobile-specific project styles */
@media (max-width: 768px) {
  .tag {
    padding: 6px 12px;
    font-size: 16px;
  }

  .project-tags .tag {
    font-size: 14px;
    padding: 4px 10px;
  }
}
</style>

