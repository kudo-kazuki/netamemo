import { createRouter, createWebHistory } from 'vue-router'
import routes from 'virtual:generated-pages'
import { useAuthStore } from '@/stores/auth'
import { useUserAuthStore } from '@/stores/user_auth'

console.log('routes', routes)

const router = createRouter({
    history: createWebHistory(),
    routes: [...routes],
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()
    authStore.checkAuth()

    const userAuthStore = useUserAuthStore()
    userAuthStore.checkAuth()

    const isAdminPage = to.path.startsWith('/admin')
    const isAdminLogin = to.path === '/admin/login'

    const isUserPage = to.path.startsWith('/mypage')
    const isUserLogin = to.path === '/user/login'

    // 管理者ページにアクセス → ログインしてなければ /admin/login へ
    if (isAdminPage && !isAdminLogin && !authStore.isAuthenticated) {
        return next('/admin/login')
    }

    // ユーザーページにアクセス → ログインしてなければ /user/login へ
    if (isUserPage && !isUserLogin && !userAuthStore.isAuthenticated) {
        return next('/user/login')
    }

    next()
})

export default router
