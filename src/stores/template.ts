import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'
import { TemplateForInput } from '@/types/types'

export const useTemplateStore = defineStore('template', {
    actions: {
        async createTemplate(inputData: TemplateForInput) {
            try {
                const response = await msgpackRequest<{ success: boolean }>(
                    '/api/template/index.php',
                    {
                        action: 'create',
                        ...inputData,
                    },
                )
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

                throw new Error(errorMessage)
            }
        },
    },
})
