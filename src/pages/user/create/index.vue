<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { User, UserFilterForm } from '@/types/types'
import dayjs from 'dayjs'

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
        <h1>新規登録</h1>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    overflow: hidden;
}
</style>
