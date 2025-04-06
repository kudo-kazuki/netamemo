import { defineStore } from 'pinia'
import { msgpackRequest } from '@/utils/msgpackRequest'
import { UserForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { AxiosError } from 'axios'

export const useUserStore = defineStore('user', {
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
    },
})
