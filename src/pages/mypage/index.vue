<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useUserAuthStore } from '@/stores/user_auth'
import { useUserStore } from '@/stores/user'

const userAuthStore = useUserAuthStore()
const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)

onMounted(() => {
    if (!userStore.userInfo) {
        userStore.getUserInfo()
    }
})

const errorMessage = ref('')

const logout = async () => {
    try {
        await userAuthStore.logout()
    } catch (error) {
        errorMessage.value = 'ログアウトに失敗しました。'
    }
}

console.log('userAuthStore', userAuthStore)
</script>

<template>
    <section class="Page">
        <h1>mypage</h1>
        <p>
            ようこそ、{{ userAuthStore.name }}さん。（{{
                userAuthStore.email
            }}）
        </p>

        <ul>
            <li>
                <RouterLink to="/mypage/profile">プロフィール確認</RouterLink>
            </li>
            <li>
                <RouterLink to="/template/">ジャンル確認</RouterLink>
            </li>
            <li>
                <RouterLink to="/post/">投稿確認</RouterLink>
            </li>
        </ul>

        <Button
            @click.prevent="logout()"
            class="Page__logoutButton"
            text="ログアウト"
            size="m"
            color="blue"
        />
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    & &__logoutButton {
        display: block;
        margin: 12px auto;
    }
}
</style>
