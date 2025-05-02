import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { PostForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

interface PostState {
    isLoading?: boolean
}

export const usePostStore = defineStore('post', {
    state: (): PostState => ({
        isLoading: false,
    }),
    actions: {
        async createPost(inputData: PostForInput) {
            if (this.isLoading) {
                return false
            }

            this.isLoading = true

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            try {
                const response = await msgpackRequest<{ success: boolean }>(
                    '/api/posts/index.php',
                    {
                        action: 'create',
                        ...inputData,
                    },
                    { token },
                )

                this.isLoading = false

                return response.success === true
            } catch (error: unknown) {
                console.error('投稿失敗', error)

                let errorMessage = '投稿に失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as {
                        message: string
                        errors?: Record<string, string[]>
                    }

                    console.error(decoded.message)
                    if (decoded.errors) console.error(decoded.errors)
                    errorMessage = decoded.message
                }

                this.isLoading = false

                throw new Error(errorMessage)
            }
        },
    },
})
