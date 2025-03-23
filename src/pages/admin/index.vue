<script setup lang="ts">
import { ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'

const authStore = useAuthStore()

const errorMessage = ref('')

const logout = async () => {
    try {
        await authStore.logout()
    } catch (error) {
        errorMessage.value = 'ログアウトに失敗しました。'
    }
}

if (authStore.level === 0) {
    authStore.fetchAdminList()
}

const formatDate = (value: string) => {
    return dayjs(value).format('YYYY-MM-DD HH:mm:ss')
}

const editAdmin = (admin: any) => {
    console.log('編集ボタンが押されました', admin)
    // ここに編集処理を書く（後でモーダルなど）
}

const deleteAdmin = (admin: any) => {
    console.log('削除ボタンが押されました', admin)
    // ここに削除API呼び出しを実装予定
}
</script>

<template>
    <section class="Page">
        <h1>管理ページ</h1>
        <p>
            ようこそ、{{ authStore.name }}さん。（レベル：{{
                authStore.level
            }}）
        </p>

        <Button
            @click.prevent="logout()"
            class="Page__logoutButton"
            text="ログアウト"
            size="m"
            color="blue"
        />

        <!-- 管理者だけが見れる一覧 -->
        <div v-if="authStore.level === 0">
            <h2>管理者一覧</h2>

            <el-table
                v-if="authStore.adminList.length > 0"
                :data="authStore.adminList"
                border
                style="width: 100%; margin-top: 1rem"
            >
                <el-table-column prop="id" label="ID" width="80" />
                <el-table-column prop="name" label="名前" />
                <el-table-column prop="level" label="レベル" width="100" />
                <el-table-column prop="remarks" label="備考" />
                <el-table-column label="作成日">
                    <template #default="{ row }">
                        {{ formatDate(row.created_at) }}
                    </template>
                </el-table-column>
                <el-table-column label="更新日">
                    <template #default="{ row }">
                        {{ formatDate(row.updated_at) }}
                    </template>
                </el-table-column>

                <!-- 編集・削除ボタン（まだ処理は無し） -->
                <el-table-column label="操作" width="160">
                    <template #default="scope">
                        <el-button
                            size="small"
                            type="primary"
                            @click="editAdmin(scope.row)"
                            >編集</el-button
                        >
                        <el-button
                            size="small"
                            type="danger"
                            @click="deleteAdmin(scope.row)"
                            >削除</el-button
                        >
                    </template>
                </el-table-column>
            </el-table>

            <div v-else>読み込み中...</div>
        </div>
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
