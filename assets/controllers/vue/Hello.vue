<template>
  <div class="hello-widget">
    <h1>{{ message }}</h1>
    <button @click="toggleMessage">
      {{ message === initial ? 'Say Goodbye' : 'Say Hello' }}
    </button>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// your “initial” message
const initial = 'Hello, world!'

// reactive message state
const message = ref(initial)

// a little interaction
function toggleMessage() {
  message.value = message.value === initial
      ? 'Goodbye, cruel world…'
      : initial
}
</script>

<style scoped>
.hello-widget {
  padding: 1rem;
  background: #fdfdfd;
  border: 1px solid #eee;
  border-radius: 8px;
  text-align: center;
  font-family: sans-serif;
}

.hello-widget h1 {
  margin-bottom: .75rem;
}

.hello-widget button {
  padding: .5rem 1rem;
  border: none;
  background: #42b983;
  color: white;
  border-radius: 4px;
  cursor: pointer;
}

.hello-widget button:hover {
  background: #369f72;
}
</style>
