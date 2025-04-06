import { defineStore } from 'pinia'
import router from '@/router'
import { jwtDecode } from 'jwt-decode'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { UserJWTPayload, UserAuthState } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

export const useUserAuthStore = defineStore('userAuth', {
    state: (): UserAuthState => ({
        token: null,
        isAuthenticated: false,
        name: null,
        email: null,
    }),

    actions: {
        async login(email: string, password: string) {
            try {
                const response = await msgpackRequest<{ token: string }>(
                    '/api/user/login.php',
                    { email, password },
                )

                this.token = response.token

                // JWTをlocalStorageに保存
                if (this.token) {
                    // JWTをデコード
                    const decoded = jwtDecode<UserJWTPayload>(this.token)

                    this.isAuthenticated = true
                    this.name = decoded.name // ユーザー名を保存
                    this.email = decoded.email
                    localStorage.setItem('user_jwt_token', this.token)
                    localStorage.setItem('user_name', this.name)
                    localStorage.setItem('user_email', String(this.email))

                    // 認証成功後、/mypage/indexにリダイレクト
                    router.push('/mypage')
                }
            } catch (error: any) {
                console.log(error)

                let errorMessage = 'ログインに失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as {
                        message: string
                    }
                    console.error(decoded.message)
                    errorMessage = decoded.message
                }

                throw new Error(errorMessage)
            }
        },

        logout() {
            this.token = null
            this.isAuthenticated = false
            this.name = null // ユーザー名をリセット
            localStorage.removeItem('user_jwt_token')
            localStorage.removeItem('user_name') // ユーザー名も削除
            router.push('/user/login')
        },

        checkAuth() {
            const token = localStorage.getItem('user_jwt_token')
            const storedName = localStorage.getItem('user_name')
            const storedEmail = localStorage.getItem('user_email')

            if (token) {
                try {
                    const decoded = jwtDecode<UserJWTPayload>(token)
                    const currentTime = Date.now() / 1000
                    if (decoded.exp > currentTime) {
                        this.token = token
                        this.isAuthenticated = true
                        this.name = storedName
                        this.email = storedEmail
                    } else {
                        this.logout() // トークン期限切れ
                    }
                } catch (e) {
                    this.logout() // 解析失敗もログアウト扱い
                }
            }
        },
    },
})
