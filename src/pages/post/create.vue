<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { useTemplateStore } from '@/stores/template'

const templateStore = useTemplateStore()

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

onMounted(() => {
    // トークンは store 側で自動取得 or localStorage から取得済み前提
    templateStore.fetchTemplateList()
})
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
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    display: flex;
    flex-direction: column;
}
</style>
