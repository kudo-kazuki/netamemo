<script setup lang="ts">
import { ref } from 'vue'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { UserForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { useUserStore } from '@/stores/user'

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()
const userStore = useUserStore()

const isRegistering = ref(false) //仮登録処理中
const isRegisterComplete = ref(false)
const isRegisterFailed = ref(false)
const registerErrorMessage = ref('')

const cretate = async (inputData: UserForInput) => {
    console.log('inputData', inputData)
    if (isRegistering.value) {
        return false
    }

    isRegistering.value = true
    isRegisterFailed.value = false
    registerErrorMessage.value = ''

    try {
        const result = await userStore.registerUser(inputData)
        if (result) {
            isRegisterComplete.value = true
            console.log('仮登録成功！確認メール送信済み')
            // 必要なら遷移やモーダル表示など
        }
    } catch (err: any) {
        console.error(err)

        if (err?.response?.data) {
            const decoded = decode(new Uint8Array(err.response.data))
            console.log('decoded', decoded)
            if (
                typeof decoded === 'object' &&
                decoded &&
                'message' in decoded
            ) {
                registerErrorMessage.value = String((decoded as any).message)
            } else {
                registerErrorMessage.value =
                    '登録に失敗しました。時間をおいて再度お試しください。'
            }
        } else {
            registerErrorMessage.value = err
        }

        isRegisterFailed.value = true
    } finally {
        isRegistering.value = false
    }
}

const closeRegisterFailed = () => {
    isRegisterFailed.value = false
}
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${windowHeight}px` }"
        :data-device="deviceType"
        :data-windowWidth="windowWidth"
    >
        <template v-if="!isRegisterComplete">
            <h1>新規登録</h1>
            <CreateForm @submit="cretate" isFirstCretae isEditMode />
        </template>
        <template v-else>
            <h1>仮登録完了</h1>
            <p>
                確認用のメールを送りました。<br />24時間以内にメール内に記載された確認用URLを開いて確認を完了させてください。
            </p>
        </template>

        <Loading v-if="isRegistering" text="仮登録中" />

        <Modal
            :isShow="isRegisterFailed"
            title="登録失敗"
            @close="closeRegisterFailed()"
        >
            <template #body>
                <p>{{ registerErrorMessage }}</p>
            </template>
        </Modal>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    overflow: hidden;
}
</style>
