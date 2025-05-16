<script setup>
import { useRoute } from 'vue-router'
import { computed } from 'vue'
import ThemeToggle from './components/ThemeToggle.vue'

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
        <ThemeToggle class="theme-toggle" />
      </nav>
    </header>
    <main>
      <router-view></router-view>
    </main>
  </div>
</template>

<style>
:root {
  /* Light theme variables */
  --background-color: #f5f5f5;
  --card-background: #ffffff;
  --text-color: #2c3e50;
  --text-secondary: #666666;
  --border-color: #dddddd;
  --hover-color: rgba(0, 0, 0, 0.05);
  --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
  --primary-color: #42b883;
  --primary-hover: #3aa876;
}

/* Dark theme */
.dark-theme {
  --background-color: #1a1a1a;
  --card-background: #2d2d2d;
  --text-color: #ffffff;
  --text-secondary: #b0b0b0;
  --border-color: #404040;
  --hover-color: rgba(255, 255, 255, 0.05);
  --card-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

body {
  margin: 0;
  background-color: var(--background-color);
  color: var(--text-color);
  transition: background-color 0.3s, color 0.3s;
}

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
  color: var(--primary-color);
  margin-bottom: 20px;
}

nav {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
}

.nav-link {
  padding: 8px 16px;
  border: 2px solid var(--primary-color);
  background: var(--card-background);
  color: var(--primary-color);
  border-radius: 4px;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s ease;
  text-decoration: none;
}

.nav-link:hover {
  background: var(--primary-color);
  color: white;
}

.nav-link.active {
  background: var(--primary-color);
  color: white;
}

.theme-toggle {
  margin-left: 16px;
}
</style>
