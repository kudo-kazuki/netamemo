<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useUserStore } from '@/stores/user'

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)

onMounted(() => {
    if (!userStore.userInfo) {
        userStore.getUserInfo()
    }
})
</script>

<template>
    <section class="Page">
        <h1>profile</h1>
        <pre>{{ userInfo }}</pre>
        <CreateForm />
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;
}
</style>
