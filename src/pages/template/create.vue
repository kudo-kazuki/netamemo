<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useTemplateStore } from '@/stores/template'

const templateStore = useTemplateStore()
</script>

<template>
    <section class="Page">
        <h1>ジャンル新規作成</h1>
        <TemplateForm isFirstCretae />
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;
}
</style>
