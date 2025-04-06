<script setup lang="ts">
import { ref } from 'vue'
import { useUserAuthStore } from '@/stores/user_auth'

const userAuthStore = useUserAuthStore()

const errorMessage = ref('')

const logout = async () => {
    try {
        await userAuthStore.logout()
    } catch (error) {
        errorMessage.value = 'ログアウトに失敗しました。'
    }
}
</script>

<template>
    <section class="Page">
        <h1>mypage</h1>
        <p>
            ようこそ、{{ userAuthStore.name }}さん。（{{
                userAuthStore.email
            }}）
        </p>
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
