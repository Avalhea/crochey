<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'

const route = useRoute()
const currentRoute = computed(() => route.path)
</script>

<template>
  <div class="app">
    <header>
      <h1>Crochey</h1>
      <nav>
        <router-link 
          to="/projects"
          class="nav-link"
          :class="{ active: currentRoute === '/projects' }"
        >
          Projects
        </router-link>
        <router-link 
          to="/yarns"
          class="nav-link"
          :class="{ active: currentRoute === '/yarns' }"
        >
          Yarns
        </router-link>
      </nav>
    </header>
    <main>
      <router-view></router-view>
    </main>
  </div>
</template>

<style>
.app {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
}

header {
  margin-bottom: 40px;
  text-align: center;
}

h1 {
  color: #42b883;
  margin-bottom: 20px;
}

nav {
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-top: 20px;
}

.nav-link {
  padding: 8px 16px;
  border: 2px solid #42b883;
  background: white;
  color: #42b883;
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s ease;
  text-decoration: none;
}

.nav-link:hover {
  background: #42b883;
  color: white;
}

.nav-link.active {
  background: #42b883;
  color: white;
}
</style>
