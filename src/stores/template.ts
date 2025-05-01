import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'
import {
    TemplateForInput,
    TemplateWithHeadings,
    TemplateForInputWithId,
} from '@/types/types'

interface TemplateState {
    templateList: TemplateWithHeadings[]
    isLoading?: boolean
    currentTemplate: TemplateWithHeadings | null
}

export const useTemplateStore = defineStore('template', {
    state: (): TemplateState => ({
        templateList: [],
        isLoading: false,
        currentTemplate: null,
    }),
    actions: {
        async createTemplate(inputData: TemplateForInput) {
            if (this.isLoading) {
                return false
            }

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            this.isLoading = true

            try {
                const response = await msgpackRequest<{ success: boolean }>(
                    '/api/template/index.php',
                    {
                        action: 'create',
                        ...inputData,
                    },
                    { token },
                )
                this.isLoading = false
                return response.success === true
            } catch (error: unknown) {
                console.error('テンプレート作成失敗', error)
                let errorMessage = 'テンプレート作成に失敗しました'

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

        async fetchTemplateList() {
            if (this.isLoading) {
                return false
            }

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            this.isLoading = true

            try {
                const response = await msgpackRequest<TemplateWithHeadings[]>(
                    '/api/template/index.php',
                    {
                        action: 'list',
                    },
                    { token },
                )

                this.templateList = response
                this.isLoading = false
                return true
            } catch (error: unknown) {
                console.error('テンプレート一覧取得失敗', error)
                let errorMessage = 'テンプレート一覧取得に失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as {
                        message: string
                    }
                    errorMessage = decoded.message
                }

                this.isLoading = false

                throw new Error(errorMessage)
            }
        },

        async deleteTemplate(templateId: number) {
            if (this.isLoading) {
                return false
            }

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            this.isLoading = true

            try {
                await msgpackRequest(
                    '/api/template/index.php',
                    {
                        action: 'delete',
                        id: templateId,
                    },
                    { token },
                )

                // 削除成功 → state から除外
                this.templateList = this.templateList.filter(
                    (t) => t.id !== templateId,
                )
                this.isLoading = false
            } catch (error) {
                let errorMessage = 'テンプレート削除に失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as { message: string }
                    errorMessage = decoded.message
                }

                console.error(errorMessage)
                this.isLoading = false

                throw new Error(errorMessage)
            }
        },

        async fetchTemplateById(templateId: number) {
            if (this.isLoading || !templateId || isNaN(templateId)) {
                return false
            }

            this.isLoading = true

            const token = localStorage.getItem('user_jwt_token')
            if (!token) {
                throw new Error('認証情報がありません')
            }

            try {
                const response = await msgpackRequest<TemplateWithHeadings>(
                    '/api/template/index.php',
                    { action: 'find', id: templateId },
                    { token },
                )
                this.currentTemplate = response
                this.isLoading = false
            } catch (error) {
                let errorMessage = 'テンプレートの取得に失敗しました'

                if (error instanceof AxiosError && error.response?.data) {
                    const decoded = decode(
                        new Uint8Array(error.response.data),
                    ) as { message: string }
                    errorMessage = decoded.message
                }

                this.currentTemplate = null
                console.error(errorMessage)
                this.isLoading = false

                throw new Error(errorMessage)
            }
        },

        async updateTemplate(inputData: TemplateForInputWithId) {
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
                    '/api/template/index.php',
                    {
                        action: 'update',
                        ...inputData,
                    },
                    { token },
                )

                this.isLoading = false

                return response.success === true
            } catch (error: unknown) {
                console.error('テンプレート編集失敗', error)
                let errorMessage = 'テンプレート編集に失敗しました'

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
