import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { UserForInput, User } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

export const useUserStore = defineStore('user', {
    state: () => ({
        userInfo: null as User | null,
    }),
    actions: {
        async registerUser(inputData: UserForInput) {
            try {
                const response = await msgpackRequest<{ success: boolean }>(
                    '/api/user/index.php',
                    {
                        action: 'create',
                        ...inputData,
                    },
                )
                return response.success === true
            } catch (error: unknown) {
                console.error('ユーザー登録失敗', error)
                let errorMessage = 'ユーザー登録に失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as {
                        message: string
                        errors: Record<string, string[]>
                    }
                    console.error(decoded.message) // "バリデーションエラー"
                    console.error(decoded.errors) // 各フィールドのエラー
                    errorMessage = decoded.message
                }

                throw new Error(errorMessage)
            }
        },

        async getUserInfo(force = false): Promise<User | null> {
            // キャッシュがあればそれを返す
            if (this.userInfo && !force) {
                return this.userInfo
            }

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                return null
            }

            try {
                const response = await msgpackRequest<User>(
                    '/api/user/index.php',
                    { action: 'getInfo' },
                    { token },
                )
                this.userInfo = response // ← state に保存
                return response
            } catch (error) {
                console.error('ユーザー情報取得失敗', error)
                return null
            }
        },

        // 更新APIを後で追加したとき用に：更新時に上書きもできる
        setUserInfo(updated: User) {
            this.userInfo = updated
        },

        // ユーザープロフィール情報編集（ユーザーが自分で編集するとき）
        updateUserProfile: async function (
            inputData: Partial<UserForInput>,
        ): Promise<boolean> {
            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            try {
                const response = await msgpackRequest<{ success: boolean }>(
                    '/api/user/index.php',
                    {
                        action: 'update',
                        ...inputData,
                    },
                    { token },
                )
                return response.success === true
            } catch (error) {
                console.error('プロフィール更新失敗', error)
                throw error
            }
        },
    },
})
