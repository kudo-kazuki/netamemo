<script setup lang="ts">
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${windowHeight}px` }"
        :data-device="deviceType"
        :data-windowWidth="windowWidth"
    >
        <h1>post</h1>

        <ul>
            <li>
                <RouterLink to="/post/create">新規投稿</RouterLink>
            </li>
        </ul>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;
}
</style>
