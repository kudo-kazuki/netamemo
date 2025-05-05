import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { PostForInput, PostItem, PostListResponse } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

interface PostState {
    isLoading?: boolean
    postList: PostItem[]
    totalPosts: number
    currentPage: number
    perPage: number
    errorMessage: string
}

export const usePostStore = defineStore('post', {
    state: (): PostState => ({
        isLoading: false,
        postList: [],
        totalPosts: 0,
        currentPage: 1,
        perPage: 50,
        errorMessage: '',
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

        async fetchPostsByTemplateId(
            templateId: number,
            page = 1,
            sort: 'asc' | 'desc' = 'desc',
        ) {
            if (this.isLoading) {
                return false
            }

            this.isLoading = true
            this.errorMessage = ''

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            try {
                const response = await msgpackRequest<PostListResponse>(
                    '/api/post/index.php',
                    {
                        action: 'listByTemplate',
                        template_id: templateId,
                        page,
                        per_page: this.perPage,
                        sort,
                    },
                    { token },
                )

                this.postList = response.posts
                this.totalPosts = response.total
                this.currentPage = response.page
            } catch (error: unknown) {
                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as { message: string }
                    this.errorMessage =
                        decoded.message || '投稿一覧の取得に失敗しました'
                } else {
                    this.errorMessage = '投稿一覧の取得に失敗しました'
                }
                console.error(this.errorMessage)
            } finally {
                this.isLoading = false
            }
        },
    },
})
