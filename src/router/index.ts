import { createRouter, createWebHistory } from 'vue-router'
import routes from 'virtual:generated-pages'
import { useAuthStore } from '@/stores/auth'

console.log('routes', routes)

const router = createRouter({
    history: createWebHistory(),
    routes: [...routes],
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()
    authStore.checkAuth()

    const requiresAuth =
        to.path.startsWith('/admin') && to.path !== '/admin/login'

    if (requiresAuth && !authStore.isAuthenticated) {
        next('/admin/login')
    } else {
        next()
    }
})

export default router
