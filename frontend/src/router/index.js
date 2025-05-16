// src/router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import YarnList from '../components/YarnList.vue'
import ProjectList from '../components/ProjectList.vue'
import ProjectDetail from '../components/ProjectDetail.vue'
import YarnDetail from '../components/YarnDetail.vue'

const routes = [
    { 
        path: '/', 
        redirect: '/yarns'
    },
    {
        path: '/yarns',
        name: 'Yarns',
        component: YarnList
    },
    {
        path: '/yarns/:id',
        name: 'YarnDetail',
        component: YarnDetail,
        props: true
    },
    {
        path: '/projects',
        name: 'Projects',
        component: ProjectList
    },
    {
        path: '/projects/:id',
        name: 'ProjectDetail',
        component: ProjectDetail,
        props: true
    }
]

const router = createRouter({
    history: createWebHistory(),
    routes
})

export default router
