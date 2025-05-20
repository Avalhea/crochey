<template>
  <div class="project-form">
    <form @submit.prevent="handleSubmit">
      <div class="form-group">
        <label for="name">Name*</label>
        <input 
          id="name" 
          v-model="form.name" 
          type="text" 
          required
        >
      </div>

      <div class="form-group">
        <label for="description">Description*</label>
        <textarea 
          id="description" 
          v-model="form.description" 
          rows="3"
          required
        ></textarea>
      </div>

      <div class="form-group">
        <label for="status">Status*</label>
        <select id="status" v-model="form.Status" required>
          <option value="">Select status</option>
          <option value="Not started">Not Started</option>
          <option value="WIP">WIP</option>
          <option value="Finished">Finished</option>
        </select>
      </div>

      <div class="form-group">
        <label for="difficulty">Difficulty*</label>
        <select id="difficulty" v-model="form.Difficulty" required>
          <option value="">Select difficulty</option>
          <option value="Beginner">Beginner</option>
          <option value="Easy">Easy</option>
          <option value="Intermediate">Intermediate</option>
          <option value="Advanced">Advanced</option>
        </select>
      </div>

      <div class="form-group" v-if="form.Status === 'WIP' || form.Status === 'Finished'">
        <label for="startDate">Start Date</label>
        <input 
          id="startDate" 
          v-model="form.started_at" 
          type="date"
        >
      </div>

      <div class="form-group" v-if="form.Status === 'Finished'">
        <label for="completionDate">Completion Date</label>
        <input 
          id="completionDate" 
          v-model="form.finished_at" 
          type="date"
        >
      </div>

      <div class="form-group">
        <label for="imageUrl">Image URL</label>
        <input 
          id="imageUrl" 
          v-model="form.imageUrl" 
          type="url"
        >
      </div>

      <div class="form-group">
        <label for="tags">Tags</label>
        <div class="tags-input">
          <div class="tags-list">
            <span 
              v-for="(tag, index) in form.tags" 
              :key="index" 
              class="tag"
            >
              {{ tag.label || tag }}
              <button 
                type="button" 
                class="tag-remove" 
                @click="removeTag(index)"
              >×</button>
            </span>
          </div>
          <input 
            id="tags"
            v-model="newTag"
            @keydown.enter.prevent="addTag"
            @keydown.tab.prevent="addTag"
            @keydown.comma.prevent="addTag"
            type="text"
            placeholder="Add tags (press Enter or Tab)"
          >
        </div>
      </div>

      <div class="form-actions">
        <button type="button" class="btn-secondary" @click="$emit('cancel')">Cancel</button>
        <button type="submit" class="btn-primary">{{ editMode ? 'Update' : 'Add' }} Project</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const props = defineProps({
  project: {
    type: Object,
    default: () => ({})
  },
  editMode: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['submit', 'cancel'])

const newTag = ref('')

const form = ref({
  name: '',
  description: '',
  Status: '',
  Difficulty: '',
  started_at: '',
  finished_at: '',
  imageUrl: '',
  tags: []
})

const addTag = () => {
  const tagLabel = newTag.value.trim()
  if (tagLabel) {
    // Check if tag already exists
    const exists = form.value.tags.some(tag => 
      (typeof tag === 'string' && tag === tagLabel) || 
      (tag.label && tag.label === tagLabel)
    )
    
    if (!exists) {
      // Add as an object with a label property
      form.value.tags.push({ label: tagLabel })
    }
  }
  newTag.value = ''
}

const removeTag = (index) => {
  form.value.tags = form.value.tags.filter((_, i) => i !== index)
}

onMounted(() => {
  if (props.editMode && props.project) {
    // Make a deep copy to avoid modifying the original project
    form.value = {
      ...props.project,
      // Ensure tags are properly formatted as objects with label property
      tags: (props.project.tags || []).map(tag => {
        if (typeof tag === 'string') {
          return { label: tag }
        }
        return tag
      })
    }
  }
})

const handleSubmit = () => {
  const formData = { ...form.value }
  
  // Handle dates based on status
  if (formData.Status === 'Not started') {
    formData.started_at = null
    formData.finished_at = null
  } else if (formData.Status === 'WIP') {
    if (!formData.started_at) {
      formData.started_at = new Date().toISOString().split('T')[0]
    }
    formData.finished_at = null
  } else if (formData.Status === 'Finished') {
    if (!formData.started_at) {
      formData.started_at = new Date().toISOString().split('T')[0]
    }
    if (!formData.finished_at) {
      formData.finished_at = new Date().toISOString().split('T')[0]
    }
  }

  // Format any existing dates
  if (formData.started_at) {
    const date = new Date(formData.started_at)
    formData.started_at = date.toISOString().split('T')[0]
  }
  if (formData.finished_at) {
    const date = new Date(formData.finished_at)
    formData.finished_at = date.toISOString().split('T')[0]
  }
  
  // Ensure tags are in the correct format (objects with label property)
  formData.tags = formData.tags.map(tag => {
    if (typeof tag === 'string') {
      return { label: tag }
    }
    return tag
  })
  
  // Log the data being sent
  console.log('Sending project data:', formData)
  
  emit('submit', formData)
}
</script>

<style scoped>
.project-form {
  padding: 20px;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 500;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
  color: #9e9e9e;
}

.form-group textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-secondary {
  padding: 8px 16px;
  background: #6c757d;
  color: white;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

.btn-secondary:hover {
  background: #5a6268;
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

.tags-input {
  display: flex;
  flex-direction: column;
  gap: 10px;
  padding: 8px;
  border: 1px solid #ddd;
  border-radius: 4px;
}

.tags-list {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
}

.tag {
  display: inline-flex;
  align-items: center;
  gap: 4px;
  padding: 4px 8px;
  background: var(--primary-color);
  color: white;
  border-radius: 16px;
  font-size: 14px;
}

.tag-remove {
  background: none;
  border: none;
  color: white;
  font-size: 16px;
  cursor: pointer;
  padding: 0 2px;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.tag-remove:hover {
  opacity: 0.8;
}

.tags-input input {
  border: none;
  padding: 4px 0;
  font-size: 14px;
  outline: none;
}

.tags-input input:focus {
  outline: none;
}

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  .tags-input {
    padding: 12px;
  }

  .tag {
    padding: 6px 12px;
    font-size: 16px;
  }

  .tag-remove {
    padding: 0 4px;
    font-size: 18px;
  }

  .tags-input input {
    padding: 8px 0;
    font-size: 16px;
  }
}
</style> 