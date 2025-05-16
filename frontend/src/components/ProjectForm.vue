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

const form = ref({
  name: '',
  description: '',
  Status: '',
  Difficulty: '',
  started_at: '',
  finished_at: '',
  imageUrl: ''
})

onMounted(() => {
  if (props.editMode && props.project) {
    form.value = { ...props.project }
  }
})

const handleSubmit = () => {
  emit('submit', { ...form.value })
}
</script>

<style scoped>
.project-form {
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 16px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #2c3e50;
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
</style> 