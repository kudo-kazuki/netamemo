<script setup lang="ts">
import { ref, onMounted } from 'vue'
import { useTemplateStore } from '@/stores/template'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { TemplateForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'
import { useRoute } from 'vue-router'

const route = useRoute()
const templateId = Number(route.params.template_id)

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

const templateStore = useTemplateStore()

onMounted(() => {
    if (!isNaN(templateId)) {
        templateStore.fetchTemplateById(templateId)
    }
})

const isSaving = ref(false) //保存処理中
const isSaveComplete = ref(false)
const isSaveFailed = ref(false)
const saveErrorMessage = ref('')

const saveEdit = async (inputData: TemplateForInput) => {
    console.log('inputData', inputData)
    if (isSaving.value || !templateStore.currentTemplate?.id) {
        return false
    }

    isSaving.value = true
    isSaveFailed.value = false
    saveErrorMessage.value = ''

    try {
        const inputWithId = {
            ...inputData,
            id: templateStore.currentTemplate.id, // ← ここで明示的にid追加
        }
        const result = await templateStore.updateTemplate(inputWithId)
        if (result) {
            isSaveComplete.value = true
            console.log('保存成功！')
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
                saveErrorMessage.value = String((decoded as any).message)
            } else {
                saveErrorMessage.value =
                    '編集に失敗しました。時間をおいて再度お試しください。'
            }
        } else {
            saveErrorMessage.value = err
        }

        isSaveFailed.value = true
    } finally {
        isSaving.value = false
    }
}

const closeRegisterFailed = () => {
    isSaveFailed.value = false
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
        <h1>編集</h1>
        <p><RouterLink to="/template/">戻る</RouterLink></p>

        <p v-if="!templateStore.isLoading && !templateStore.currentTemplate">
            データがありません
        </p>

        <TemplateForm
            v-show="!templateStore.isLoading && templateStore.currentTemplate"
            :input="
                templateStore.currentTemplate
                    ? { ...templateStore.currentTemplate }
                    : null
            "
            @submit="saveEdit"
        />

        <Loading v-if="templateStore.isLoading" text="読み込み中" />
        <Loading v-if="isSaving" text="保存中" />

        <Modal
            :isShow="isSaveComplete"
            title="成功"
            size="s"
            @close="closeRegisterComplete()"
        >
            <template #body>
                <p>編集しました。</p>
            </template>
        </Modal>

        <Modal
            :isShow="isSaveFailed"
            title="編集失敗"
            @close="closeRegisterFailed()"
        >
            <template #body>
                <p>{{ saveErrorMessage }}</p>
            </template>
        </Modal>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;
}
</style>
