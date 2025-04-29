<script setup lang="ts">
import { onMounted, computed } from 'vue'
import { useUserStore } from '@/stores/user'
import { UserForInput } from '@/types/types'
import { decode } from '@msgpack/msgpack'

const userStore = useUserStore()
const userInfo = computed(() => userStore.userInfo)

onMounted(() => {
    if (!userStore.userInfo) {
        userStore.getUserInfo()
    }
})

const isProfileSaving = ref(false) //プロフィール上書き保存中
const isProfileSaveComplete = ref(false)
const isProfileSaveFailed = ref(false)
const saveProfileErrorMessage = ref('')

const saveEditedProfile = async (inputData: UserForInput) => {
    console.log('inputData', inputData)
    if (isProfileSaving.value) {
        return false
    }

    isProfileSaving.value = true
    isProfileSaveFailed.value = false
    saveProfileErrorMessage.value = ''

    // 必要なプロパティだけ抽出
    const { name, email, birthday, gender, message, profile } = inputData
    const sanitizedInput = { name, email, birthday, gender, message, profile }
    console.log('sanitizedInput', sanitizedInput)

    try {
        const result = await userStore.updateUserProfile(sanitizedInput)
        if (result) {
            isProfileSaveComplete.value = true
            console.log('編集成功！')
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
                saveProfileErrorMessage.value = String((decoded as any).message)
            } else {
                saveProfileErrorMessage.value =
                    '編集に失敗しました。時間をおいて再度お試しください。'
            }
        } else {
            saveProfileErrorMessage.value = err
        }

        isProfileSaveFailed.value = true
    } finally {
        isProfileSaving.value = false
    }
}

const closeProfileSaveFailed = () => {
    isProfileSaveFailed.value = false
}
const closeProfileComplete = () => {
    location.reload()
}
</script>

<template>
    <section class="Page">
        <h1>profile</h1>
        <p><RouterLink to="/mypage">戻る</RouterLink></p>
        <CreateForm
            :isEditMode="false"
            :input="userInfo"
            @submit="saveEditedProfile"
        />

        <Loading v-if="isProfileSaving" text="上書き処理中" />

        <Modal
            :isShow="isProfileSaveComplete"
            title="編集成功"
            size="s"
            @close="closeProfileComplete()"
        >
            <template #body>
                <p>編集しました。</p>
            </template>
        </Modal>

        <Modal
            :isShow="isProfileSaveFailed"
            title="編集失敗"
            @close="closeProfileSaveFailed()"
        >
            <template #body>
                <p>{{ saveProfileErrorMessage }}</p>
            </template>
        </Modal>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;
}
</style>
