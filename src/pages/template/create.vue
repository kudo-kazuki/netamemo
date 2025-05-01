<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useTemplateStore } from '@/stores/template'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { TemplateForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

const templateStore = useTemplateStore()

const isRegistering = ref(false) //登録処理中
const isRegisterComplete = ref(false)
const isRegisterFailed = ref(false)
const registerErrorMessage = ref('')

const create = async (inputData: TemplateForInput) => {
    console.log('inputData', inputData)
    if (isRegistering.value) {
        return false
    }

    isRegistering.value = true
    isRegisterFailed.value = false
    registerErrorMessage.value = ''

    try {
        const result = await templateStore.createTemplate(inputData)
        if (result) {
            isRegisterComplete.value = true
            console.log('新規作成成功！')
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
                    '新規作成に失敗しました。時間をおいて再度お試しください。'
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

const closeRegisterComplete = () => {
    location.reload()
}
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${windowHeight}px` }"
        :data-device="deviceType"
        :data-windowWidth="windowWidth"
    >
        <h1>ジャンル新規作成</h1>
        <p><RouterLink to="/template/">戻る</RouterLink></p>

        <TemplateForm isFirstCretae @submit="create" />

        <Loading v-if="isRegistering" text="新規作成中" />

        <Modal
            :isShow="isRegisterComplete"
            title="成功"
            size="s"
            @close="closeRegisterComplete()"
        >
            <template #body>
                <p>新規作成しました。</p>
            </template>
        </Modal>

        <Modal
            :isShow="isRegisterFailed"
            title="新規作成失敗"
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
}
</style>
