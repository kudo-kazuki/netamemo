<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'

const name = ref('')
const password = ref('')
const errorMessage = ref('')
const authStore = useAuthStore()

const handleLogin = async () => {
    try {
        await authStore.login(name.value, password.value)
    } catch (error: any) {
        if (error) {
            errorMessage.value = error
        } else {
            errorMessage.value = 'ログインに失敗しました。'
        }
    }
}
</script>

<template>
    <div class="Login">
        <form class="Login__form" @submit.prevent="handleLogin">
            <h1>ログイン</h1>

            <div class="Login__inputWrap">
                <label for="name">ユーザー名:</label>
                <input type="text" v-model="name" id="name" required />
            </div>
            <div class="Login__inputWrap">
                <label for="password">パスワード:</label>
                <input
                    type="password"
                    v-model="password"
                    id="password"
                    required
                />
            </div>

            <Button
                class="Login__button"
                text="ログイン"
                size="m"
                color="blue"
            />
        </form>
        <p v-if="errorMessage" class="Login__errorMessage">
            {{ errorMessage }}
        </p>
    </div>
</template>

<style lang="scss" scoped>
.Login {
    &__form {
        border: 1px solid #ccc;
        max-width: 400px;
        margin: 60px auto;
        padding: 20px;
        border-radius: 6px;
    }

    h1 {
        margin-bottom: 20px;
    }

    &__inputWrap {
        display: flex;
        flex-direction: column;
        row-gap: 4px;
    }

    &__inputWrap + &__inputWrap {
        margin-top: 12px;
    }

    & &__button {
        display: block;
        margin: 20px auto 0;
    }

    &__errorMessage {
        text-align: center;
        margin-top: 12px;
        color: red;
    }
}
</style>
