<script setup lang="ts">
import { onMounted } from 'vue'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import axios from 'axios'

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

const testMessage = ref('')
onMounted(() => {
    axios
        .get('/api/test.php')
        .then((response) => {
            testMessage.value = response.data
            console.log('APIレスポンス:', response)
        })
        .catch((error) => {
            console.error('APIエラー:', error)
        })
})
</script>

<template>
    <div
        class="Page"
        :style="{ height: `${windowHeight}px` }"
        :data-device="deviceType"
        :data-windowWidth="windowWidth"
    >
        {{ testMessage }}
        aaaa
    </div>
</template>

<style lang="scss" scoped>
.Page {
    overflow: hidden;
    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    @media screen and (max-width: 740px) {
    }
}
</style>
