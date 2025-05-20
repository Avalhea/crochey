<template>
  <div class="yarn-form">
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
        <label for="brand">Brand*</label>
        <input 
          id="brand" 
          v-model="form.brand" 
          type="text" 
          required
        >
      </div>

      <div class="form-group">
        <label for="color">Color*</label>
        <input 
          id="color" 
          v-model="form.color" 
          type="text" 
          required
        >
      </div>

      <div class="form-group">
        <label for="quantity">Quantity*</label>
        <input 
          id="quantity" 
          v-model.number="form.quantity" 
          type="number" 
          min="0" 
          required
        >
      </div>

      <div class="form-group">
        <label for="weight">Weight*</label>
        <select id="weight" v-model="form.Weight" required>
          <option value="">Select weight</option>
          <option value="Super Fine">Super Fine</option>
          <option value="Fine">Fine</option>
          <option value="Light">Light</option>
          <option value="Medium">Medium</option>
          <option value="Bulky">Bulky</option>
          <option value="Super Bulky">Super Bulky</option>
        </select>
      </div>

      <div class="form-group">
        <label for="fiber">Fiber Content*</label>
        <select id="fiber" v-model="form.FiberContent" required>
          <option value="">Select fiber content</option>
          <option value="Cotton">Cotton</option>
          <option value="Wool">Wool</option>
          <option value="Acrylic">Acrylic</option>
          <option value="Bamboo">Bamboo</option>
          <option value="Alpaca">Alpaca</option>
          <option value="Linen">Linen</option>
          <option value="Silk">Silk</option>
          <option value="Mixed">Mixed</option>
        </select>
      </div>

      <div class="form-group">
        <label for="image">Image</label>
        <ImageUploader
          v-model="form.imageUrl"
          type="yarn"
          @upload-complete="handleImageUpload"
        />
      </div>

      <div class="form-group">
        <label for="notes">Notes</label>
        <textarea 
          id="notes" 
          v-model="form.notes" 
          rows="3"
        ></textarea>
      </div>

      <div class="form-actions">
        <button type="button" class="btn-secondary" @click="$emit('cancel')">Cancel</button>
        <button type="submit" class="btn-primary">{{ editMode ? 'Update' : 'Add' }} Yarn</button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import ImageUploader from './ImageUploader.vue'

const props = defineProps({
  yarn: {
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
  brand: '',
  color: '',
  quantity: 1,
  Weight: '',
  FiberContent: '',
  imageUrl: '',
  notes: ''
})

onMounted(() => {
  if (props.editMode && props.yarn) {
    form.value = { ...props.yarn }
  }
})

const handleSubmit = () => {
  emit('submit', { ...form.value })
}

const handleImageUpload = (url) => {
  // Handle image upload completion
}
</script>

<style scoped>
.yarn-form {
  padding: 20px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.form-group {
  margin-bottom: 16px;
}

label {
  display: block;
  margin-bottom: 8px;
  color: #2c3e50;
  font-weight: 500;
}

input, select, textarea {
  width: 100%;
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 4px;
  font-size: 16px;
}

textarea {
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

button {
  padding: 8px 16px;
  border: none;
  border-radius: 4px;
  font-size: 16px;
  cursor: pointer;
}

.btn-primary {
  background: #42b883;
  color: white;
}

.btn-secondary {
  background: #6c757d;
  color: white;
}

.btn-primary:hover {
  background: #3aa876;
}

.btn-secondary:hover {
  background: #5a6268;
}
</style> 