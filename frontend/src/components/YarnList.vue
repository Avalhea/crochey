<!-- src/components/YarnList.vue -->
<template>
  <div class="page-container">
    <div class="header">
      <h2>Yarn Inventory</h2>
      <button class="btn-primary" @click="showAddForm = true" v-if="!showAddForm">Add New Yarn</button>
    </div>

    <div class="filters" v-if="!showAddForm && !editingYarn">
      <div class="search">
        <input 
          type="text" 
          v-model="filters.search" 
          placeholder="Search yarns..."
        >
      </div>
      <div class="filter-group">
        <select v-model="filters.weight">
          <option value="">All Weights</option>
          <option value="Super Fine">Super Fine</option>
          <option value="Fine">Fine</option>
          <option value="Light">Light</option>
          <option value="Medium">Medium</option>
          <option value="Bulky">Bulky</option>
          <option value="Super Bulky">Super Bulky</option>
        </select>
        <select v-model="filters.fiber">
          <option value="">All Fibers</option>
          <option value="Cotton">Cotton</option>
          <option value="Wool">Wool</option>
          <option value="Acrylic">Acrylic</option>
          <option value="Bamboo">Bamboo</option>
          <option value="Alpaca">Alpaca</option>
          <option value="Linen">Linen</option>
          <option value="Silk">Silk</option>
          <option value="Mixed">Mixed</option>
        </select>
        <select v-model="sortBy">
          <option value="name">Sort by Name</option>
          <option value="brand">Sort by Brand</option>
          <option value="color">Sort by Color</option>
          <option value="quantity">Sort by Quantity</option>
        </select>
      </div>
    </div>

    <YarnForm 
      v-if="showAddForm"
      @submit="handleAdd"
      @cancel="showAddForm = false"
    />

    <YarnForm 
      v-else-if="editingYarn"
      :yarn="editingYarn"
      :edit-mode="true"
      @submit="handleUpdate"
      @cancel="editingYarn = null"
    />

    <div v-else>
      <div v-if="loading">Loading...</div>
      <div v-else-if="error">{{ error }}</div>
      <div v-else>
        <div>Number of yarns: {{ filteredAndSortedYarns.length }}
          <span v-if="filteredAndSortedYarns.length !== yarns.length">
            ({{ yarns.length }} total)
          </span>
        </div>
        <div class="item-grid">
          <div 
            v-for="yarn in filteredAndSortedYarns" 
            :key="yarn.id" 
            class="card"
            @click="$router.push(`/yarns/${yarn.id}`)"
            style="cursor: pointer;"
          >
            <div class="card-actions" @click.stop>
              <button class="btn-icon" @click="editingYarn = yarn" title="Edit">✏️</button>
              <button class="btn-icon" @click="handleDelete(yarn)" title="Delete">🗑️</button>
            </div>
            <div class="card-image">
              <img 
                :src="getImageUrl(yarn)" 
                :alt="yarn.name"
                @error="$event.target.src = DEFAULT_YARN_IMAGE"
              >
            </div>
            <div class="card-content">
              <h3>{{ yarn.name }}</h3>
              <div class="card-details">
                <p><strong>Brand:</strong> {{ yarn.brand }}</p>
                <p><strong>Color:</strong> {{ yarn.color }}</p>
                <p><strong>Weight:</strong> {{ yarn.Weight || yarn.weight }}</p>
                <p><strong>Fiber:</strong> {{ yarn.FiberContent || yarn.fiberContent }}</p>
                <p><strong>Quantity:</strong> {{ yarn.quantity }}</p>
              </div>
              <p v-if="yarn.notes" class="item-notes">{{ yarn.notes }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import YarnForm from './YarnForm.vue'

const DEFAULT_YARN_IMAGE = 'https://placehold.co/400x300/e9ecef/495057?text=Yarn+Image'

const yarns = ref([])
const loading = ref(true)
const error = ref(null)
const showAddForm = ref(false)
const editingYarn = ref(null)
const filters = ref({
  search: '',
  weight: '',
  fiber: ''
})
const sortBy = ref('name')

onMounted(() => {
  console.log('Component mounted, fetching yarns...')
  fetchYarns()
})

const filteredAndSortedYarns = computed(() => {
  let filtered = yarns.value.filter(yarn => {
    const matchesSearch = !filters.value.search || 
      (yarn.name?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      yarn.brand?.toLowerCase().includes(filters.value.search.toLowerCase()) ||
      yarn.color?.toLowerCase().includes(filters.value.search.toLowerCase()))
    
    const matchesWeight = !filters.value.weight || yarn.Weight === filters.value.weight
    const matchesFiber = !filters.value.fiber || yarn.FiberContent === filters.value.fiber
    
    return matchesSearch && matchesWeight && matchesFiber
  })

  return filtered.sort((a, b) => {
    if (sortBy.value === 'quantity') {
      return b.quantity - a.quantity
    }
    return (a[sortBy.value] || '').localeCompare(b[sortBy.value] || '')
  })
})

const isValidImageUrl = (url) => {
  if (!url) return false
  return !url.includes('example.com')
}

const getImageUrl = (yarn) => {
  return isValidImageUrl(yarn.imageUrl) ? yarn.imageUrl : DEFAULT_YARN_IMAGE
}

const fetchYarns = async () => {
  try {
    console.log('Fetching yarns...')
    const response = await fetch('/api/yarns')
    console.log('Response status:', response.status)
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    const data = await response.json()
    console.log('Raw data:', data)
    
    // Extract the yarns from the 'member' property
    let yarnArray = data.member || []
    console.log('Yarn array before transform:', yarnArray)

    // Transform the data to ensure consistent property names
    const transformedYarns = yarnArray.map(yarn => ({
      id: yarn.id,
      name: yarn.name || '',
      brand: yarn.brand || '',
      color: yarn.color || '',
      quantity: yarn.quantity || 0,
      imageUrl: yarn.imageUrl || '',
      notes: yarn.notes || '',
      Weight: yarn.Weight || '',  // Only use Weight, not weight
      FiberContent: yarn.FiberContent || ''  // Only use FiberContent, not fiberContent
    }))
    
    console.log('Transformed yarns:', transformedYarns)
    yarns.value = transformedYarns
    console.log('Final yarns.value:', yarns.value)
  } catch (e) {
    error.value = 'Error loading yarns: ' + e.message
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

const handleAdd = async (formData) => {
  try {
    const response = await fetch('/api/yarns', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to add yarn')
    
    await fetchYarns()
    showAddForm.value = false
  } catch (e) {
    error.value = 'Error adding yarn: ' + e.message
  }
}

const handleUpdate = async (formData) => {
  try {
    const response = await fetch(`/api/yarns/${editingYarn.value.id}`, {
      method: 'PUT',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(formData),
    })
    
    if (!response.ok) throw new Error('Failed to update yarn')
    
    await fetchYarns()
    editingYarn.value = null
  } catch (e) {
    error.value = 'Error updating yarn: ' + e.message
  }
}

const handleDelete = async (yarn) => {
  if (!confirm('Are you sure you want to delete this yarn?')) return
  
  try {
    const response = await fetch(`/api/yarns/${yarn.id}`, {
      method: 'DELETE',
    })
    
    if (!response.ok) throw new Error('Failed to delete yarn')
    
    await fetchYarns()
  } catch (e) {
    error.value = 'Error deleting yarn: ' + e.message
  }
}
</script>

<style scoped>
.yarns {
  padding: 20px;
  min-height: 100vh;
  background: #f5f5f5;
}

.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
}

.filters {
  margin-bottom: 24px;
}

.search {
  margin-bottom: 12px;
  width: 100%;
}

.search input {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  box-sizing: border-box;
}

.filter-group {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
  width: 100%;
}

.filter-group select {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  width: 100%;
  box-sizing: border-box;
}

.yarn-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
  margin-top: 20px;
}

.yarn-card {
  position: relative;
  border: 1px solid #ddd;
  border-radius: 8px;
  padding: 16px;
  background: white;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
  min-height: 200px;
  z-index: 1;
}

.yarn-actions {
  position: absolute;
  top: 12px;
  right: 12px;
  display: flex;
  gap: 8px;
}

.btn-icon {
  background: none;
  border: none;
  font-size: 20px;
  cursor: pointer;
  padding: 4px;
  border-radius: 4px;
}

.btn-icon:hover {
  background: rgba(0,0,0,0.05);
}

.yarn-image {
  width: 100%;
  height: 200px;
  overflow: hidden;
  border-radius: 4px;
  margin-bottom: 16px;
  background-color: #e9ecef;
}

.yarn-image img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: opacity 0.2s ease-in-out;
}

.yarn-image img:not([src]), 
.yarn-image img[src=""] {
  opacity: 0;
}

.yarn-content h3 {
  color: #42b883;
  margin: 0 0 12px 0;
  font-size: 1.25rem;
}

.yarn-details {
  margin-bottom: 12px;
}

.yarn-details p {
  margin: 4px 0;
  color: #666;
}

.yarn-details strong {
  color: #2c3e50;
}

.yarn-notes {
  font-style: italic;
  color: #666;
  font-size: 0.9rem;
  margin-top: 12px;
  padding-top: 12px;
  border-top: 1px solid #eee;
}

h2 {
  color: #2c3e50;
  margin: 0;
}

.btn-primary {
  padding: 8px 16px;
  background: #42b883;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

.btn-primary:hover {
  background: #3aa876;
}
</style>
