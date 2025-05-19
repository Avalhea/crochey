<script setup>
import { useRoute } from 'vue-router'
import { computed, watchEffect, ref } from 'vue'
import ThemeToggle from './components/ThemeToggle.vue'

const route = useRoute()
const currentRoute = computed(() => route.path)
const currentItemName = ref('')

// Function to fetch item name for detail pages
const fetchItemName = async () => {
  if (!route.params.id) return

  try {
    const type = route.path.startsWith('/yarns/') ? 'yarns' : 'projects'
    const response = await fetch(`/api/${type}/${route.params.id}`)
    if (response.ok) {
      const data = await response.json()
      currentItemName.value = data.name || ''
    }
  } catch (error) {
    console.error('Error fetching item name:', error)
  }
}

// Watch for route changes and update the document title
watchEffect(async () => {
  let title = 'Crochey'
  
  // Add specific page titles based on the route
  if (route.path === '/yarns') {
    title += ' - Yarn Inventory'
  } else if (route.path === '/projects') {
    title += ' - Crochet Projects'
  } else if (route.path.startsWith('/yarns/') || route.path.startsWith('/projects/')) {
    if (route.params.id) {
      await fetchItemName()
      const type = route.path.startsWith('/yarns/') ? 'Yarn' : 'Project'
      title += currentItemName.value 
        ? ` - ${currentItemName.value}`
        : ` - ${type} Details`
    }
  }
  
  document.title = title
})
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
  --header-height: 60px;
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
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
}

.app {
  max-width: 1200px;
  margin: 0 auto;
  padding: 20px;
  box-sizing: border-box;
}

header {
  margin-bottom: 40px;
  text-align: center;
  padding: 0 16px;
}

h1 {
  color: var(--primary-color);
  margin-bottom: 20px;
  font-size: 2.5rem;
}

nav {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 16px;
  margin-top: 20px;
  flex-wrap: wrap;
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
  min-width: 100px;
  text-align: center;
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

/* Mobile Responsive Styles */
@media (max-width: 768px) {
  .app {
    padding: 10px;
  }

  header {
    margin-bottom: 20px;
  }

  h1 {
    font-size: 2rem;
    margin-bottom: 16px;
  }

  nav {
    flex-direction: column;
    gap: 12px;
    width: 100%;
  }

  .nav-link {
    width: 100%;
    max-width: 300px;
  }

  .theme-toggle {
    margin-left: 0;
    margin-top: 12px;
  }
}

/* Tablet Responsive Styles */
@media (min-width: 769px) and (max-width: 1024px) {
  .app {
    padding: 15px;
  }

  h1 {
    font-size: 2.25rem;
  }
}

/* Add smooth scrolling and better touch handling */
@media (hover: none) {
  .nav-link:hover {
    background: var(--card-background);
    color: var(--primary-color);
  }

  .nav-link:active {
    background: var(--primary-color);
    color: white;
  }
}
</style>
