<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { useTemplateStore } from '@/stores/template'
import { usePostStore } from '@/stores/post'
import { decode } from '@msgpack/msgpack'
import { PostForInput } from '@/types/types'

const templateStore = useTemplateStore()
const postStore = usePostStore()

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

onMounted(() => {
    // トークンは store 側で自動取得 or localStorage から取得済み前提
    templateStore.fetchTemplateList()
})

const isPosting = ref(false) //投稿処理中
const isPostComplete = ref(false)
const isPostFailed = ref(false)
const postErrorMessage = ref('')

const create = async (inputData: PostForInput) => {
    console.log('inputData', inputData)
    if (isPosting.value) {
        return false
    }

    isPosting.value = true
    isPostFailed.value = false
    postErrorMessage.value = ''

    try {
        const result = await postStore.createPost(inputData)
        if (result) {
            isPostComplete.value = true
            console.log('新規投稿成功！')
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
                postErrorMessage.value = String((decoded as any).message)
            } else {
                postErrorMessage.value =
                    '新規投稿に失敗しました。時間をおいて再度お試しください。'
            }
        } else {
            postErrorMessage.value = err
        }

        isPostFailed.value = true
    } finally {
        isPosting.value = false
    }
}

const closeRegisterFailed = () => {
    isPostFailed.value = false
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
        <h1>新規投稿</h1>

        <ul>
            <li>
                <RouterLink to="/post/">戻る</RouterLink>
            </li>
        </ul>

        <PostForm
            v-if="!templateStore.isLoading && templateStore.templateList.length"
            :templateList="templateStore.templateList"
            isCretae
            @submit="create"
        />

        <p
            v-if="
                !templateStore.isLoading && !templateStore.templateList.length
            "
        >
            ジャンルが登録されていません。投稿するにはジャンルを1つ以上登録してください。<br /><RouterLink
                to="/template/create"
                >ジャンル新規作成</RouterLink
            >
        </p>

        <Loading v-if="isPosting" text="新規作成中" />

        <Modal
            :isShow="isPostComplete"
            title="成功"
            size="s"
            @close="closeRegisterComplete()"
        >
            <template #body>
                <p>新規投稿しました。</p>
            </template>
        </Modal>

        <Modal
            :isShow="isPostFailed"
            title="新規投稿失敗"
            @close="closeRegisterFailed()"
        >
            <template #body>
                <p>{{ postErrorMessage }}</p>
            </template>
        </Modal>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    display: flex;
    flex-direction: column;
}
</style>
