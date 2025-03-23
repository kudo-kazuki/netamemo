import { defineStore } from 'pinia'
import axios, { AxiosError } from 'axios'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'

interface JWTPayload {
    exp: number
    name: string
    level: number | null
    sub: number
}

interface Admin {
    id: number
    name: string
    level: number
    remarks?: string
    created_at: string
    updated_at: string
}

export interface AuthState {
    token: string | null
    isAuthenticated: boolean
    name: string | null
    level: number | null
    adminList: Admin[]
}

export const useAuthStore = defineStore('auth', {
    state: (): AuthState => ({
        token: null,
        isAuthenticated: false,
        name: null,
        level: null,
        adminList: [],
    }),

    actions: {
        async login(name: string, password: string) {
            try {
                const response = await axios.post(
                    `/api/admin/login.php`,
                    { name, password },
                    {
                        headers: {
                            'Content-Type': 'application/json',
                        },
                    },
                )
                this.token = response.data.token

                // JWTをlocalStorageに保存
                if (this.token) {
                    // JWTをデコード
                    const decoded = jwtDecode<JWTPayload>(this.token)

                    this.isAuthenticated = true
                    this.name = decoded.name // ユーザー名を保存
                    this.level = decoded.level
                    localStorage.setItem('jwt_token', this.token)
                    localStorage.setItem('name', this.name)
                    localStorage.setItem('level', String(this.level))

                    // 認証成功後、/admin/indexにリダイレクト
                    router.push('/admin/')
                }
            } catch (error: any) {
                console.log(error)

                // サーバーからのエラーメッセージをそのまま投げる
                if (
                    error.response &&
                    error.response.data &&
                    error.response.data.message
                ) {
                    throw new Error(error.response.data.message)
                } else {
                    // エラーメッセージが無い場合のデフォルトエラーメッセージ
                    throw new Error('ログインに失敗しました')
                }
            }
        },

        logout() {
            this.token = null
            this.isAuthenticated = false
            this.name = null // ユーザー名をリセット
            localStorage.removeItem('jwt_token')
            localStorage.removeItem('name') // ユーザー名も削除
            router.push('/admin/login')
        },

        checkAuth() {
            const token = localStorage.getItem('jwt_token')
            const storedname = localStorage.getItem('name')
            const storedLevel = localStorage.getItem('level')

            if (token) {
                try {
                    const decoded = jwtDecode<JWTPayload>(token)
                    const currentTime = Date.now() / 1000
                    if (decoded.exp > currentTime) {
                        this.token = token
                        this.isAuthenticated = true
                        this.name = storedname
                        this.level = storedLevel ? parseInt(storedLevel) : null
                    } else {
                        this.logout() // トークン期限切れ
                    }
                } catch (e) {
                    this.logout() // 解析失敗もログアウト扱い
                }
            }
        },

        async fetchAdminList() {
            try {
                const token = this.token || localStorage.getItem('jwt_token')
                const response = await axios.post(
                    '/api/admin/index.php',
                    { action: 'list' },
                    {
                        headers: {
                            Authorization: `Bearer ${token}`,
                        },
                    },
                )
                this.adminList = response.data
            } catch (err) {
                const error = err as AxiosError
                console.error('管理者一覧の取得に失敗しました', error)
                console.log(error?.response?.data)
                this.adminList = []
            }
        },
    },
})
