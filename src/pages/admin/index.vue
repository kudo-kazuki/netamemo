<script setup lang="ts">
import { ref, computed } from 'vue'
import { useAuthStore } from '@/stores/auth'
import dayjs from 'dayjs'
import { Admin } from '@/types/types'
import { msgpackRequest } from '@/utils/msgpackRequest'

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
    console.log('adminList', authStore.adminList)
}

const formatDate = (value: string) => {
    return dayjs(value).format('YYYY-MM-DD HH:mm:ss')
}

const isLoading = ref(false)
const isLoadingText = ref('')

const selectedAdminData = ref<Admin | null>(null)
const editedAdminName = ref('')
const editedAdminPassword = ref('')
const editedAdminLevel = ref(0)
const editedAdminRemarks = ref('')

const isOpenEditAdminModal = ref(false)
const openEditAdmin = (admin: Admin) => {
    selectedAdminData.value = admin
    editedAdminName.value = admin.name
    editedAdminPassword.value = ''
    editedAdminLevel.value = admin.level
    editedAdminRemarks.value = admin.remarks ?? ''
    isOpenEditAdminModal.value = true
}
const closeEditAdmin = () => {
    isOpenEditAdminModal.value = false
    selectedAdminData.value = null
}

const saveEditAdmin = async () => {
    if (
        isLoading.value ||
        !editedAdminName.value ||
        typeof editedAdminLevel.value !== 'number' || // 数値以外はNG
        selectedAdminData.value === null
    ) {
        return false
    }

    isLoading.value = true
    isLoadingText.value = '保存中'

    try {
        const token =
            authStore.token ?? localStorage.getItem('jwt_token') ?? undefined

        await msgpackRequest(
            '/api/admin/index.php',
            {
                action: 'edit',
                id: selectedAdminData.value.id,
                name: editedAdminName.value,
                level: editedAdminLevel.value,
                remarks: editedAdminRemarks.value,
                password: editedAdminPassword.value || '',
            },
            { token },
        )

        isLoadingText.value = '成功'

        // 成功時の後処理
        setTimeout(() => {
            isLoading.value = false
            closeEditAdmin()
        }, 1000)

        // 管理者のみリストを再取得
        if (authStore.level === 0) {
            await authStore.fetchAdminList()
        }
    } catch (error: any) {
        console.error('編集に失敗しました', error)
        isLoadingText.value = '失敗'
        isLoading.value = false
    }
}

//====================//
//      削除          //
//====================//
const isOpenDeleteAdminModal = ref(false)
const openDeleteAdmin = (admin: Admin) => {
    selectedAdminData.value = admin
    isOpenDeleteAdminModal.value = true
}
const closeDeleteAdmin = () => {
    isOpenDeleteAdminModal.value = false
    selectedAdminData.value = null
}
const deleteAdmin = async () => {
    if (isLoading.value || selectedAdminData.value === null) {
        return false
    }

    isLoading.value = true
    isLoadingText.value = '削除中'

    try {
        const token =
            authStore.token ?? localStorage.getItem('jwt_token') ?? undefined

        await msgpackRequest(
            '/api/admin/index.php',
            {
                action: 'delete',
                id: selectedAdminData.value.id,
            },
            { token },
        )

        isLoadingText.value = '成功'

        setTimeout(() => {
            isLoading.value = false
            closeDeleteAdmin()
        }, 1000)

        if (authStore.level === 0) {
            await authStore.fetchAdminList()
        }
    } catch (error: any) {
        console.error('削除に失敗しました', error)
        isLoadingText.value = '失敗'
        isLoading.value = false
    }
}

//====================//
//      新規作成       //
//====================//
const createAdminName = ref('')
const createAdminPassword = ref('')
const createAdminLevel = ref(0)
const createAdminRemarks = ref('')
const isOpenCreateAdminModal = ref(false)
const openCreateAdmin = () => {
    isOpenCreateAdminModal.value = true
}
const closeCreateAdmin = () => {
    isOpenCreateAdminModal.value = false
}
const isAdminCreateOk = computed(() => {
    return (
        createAdminName.value &&
        createAdminPassword.value &&
        typeof createAdminLevel.value === 'number'
    )
})
const saveCreateAdmin = async () => {
    if (isLoading.value || !isAdminCreateOk.value) {
        return false
    }

    isLoading.value = true
    isLoadingText.value = '作成中'

    try {
        const token =
            authStore.token ?? localStorage.getItem('jwt_token') ?? undefined

        await msgpackRequest(
            '/api/admin/index.php',
            {
                action: 'create',
                name: createAdminName.value,
                password: createAdminPassword.value,
                level: createAdminLevel.value,
                remarks: createAdminRemarks.value,
            },
            { token },
        )

        isLoadingText.value = '成功'

        // 成功時、1秒後にローディング解除
        setTimeout(() => {
            isLoading.value = false
            closeCreateAdmin()
        }, 1000)

        // 管理者のみリスト更新
        if (authStore.level === 0) {
            await authStore.fetchAdminList()
        }
    } catch (error: any) {
        console.error('管理者作成に失敗しました', error)
        isLoadingText.value = '失敗'
        isLoading.value = false
    }
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

        <hr />

        <div v-if="authStore.level === 0" class="Page__createButtonWrap">
            <Button
                class="Page__createButton"
                text="管理者新規追加"
                color="blue"
                @click="openCreateAdmin()"
            />
        </div>

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
                <el-table-column label="操作" width="160">
                    <template #default="scope">
                        <el-button
                            size="small"
                            type="primary"
                            @click="openEditAdmin(scope.row)"
                            >編集</el-button
                        >
                        <el-button
                            size="small"
                            type="danger"
                            @click="openDeleteAdmin(scope.row)"
                            >削除</el-button
                        >
                    </template>
                </el-table-column>
            </el-table>

            <div v-else>読み込み中...</div>
        </div>

        <Modal
            title="編集"
            :isShow="isOpenEditAdminModal"
            @close="closeEditAdmin()"
        >
            <template #body>
                <ul class="Page__editItems">
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editedAdminName"
                            >名前：<RequireLabel /></label
                        ><input
                            type="text"
                            v-model="editedAdminName"
                            id="editedAdminName"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editedAdminPassword"
                            >パスワード：<small class="Page__LabelSmall"
                                >※空なら変更しない</small
                            ></label
                        ><input
                            type="password"
                            v-model="editedAdminPassword"
                            id="editedAdminPassword"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editedAdminLevel"
                            >レベル：<RequireLabel /></label
                        ><input
                            type="number"
                            v-model="editedAdminLevel"
                            min="0"
                            id="editedAdminLevel"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="editedAdminRemarks"
                            >備考：</label
                        ><textarea
                            v-model="editedAdminRemarks"
                            id="editedAdminRemarks"
                        />
                    </li>
                </ul>
            </template>

            <template #footer>
                <ul>
                    <li>
                        <Button
                            text="キャンセル"
                            color="gray"
                            @click="closeEditAdmin()"
                        />
                    </li>
                    <li>
                        <Button
                            text="保存"
                            color="blue"
                            @click="saveEditAdmin()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <Modal
            title="削除"
            :isShow="isOpenDeleteAdminModal"
            size="m"
            isTextCenter
            @close="closeDeleteAdmin()"
        >
            <template #body>
                <p>
                    {{ selectedAdminData?.id }}：{{ selectedAdminData?.name
                    }}<br />を削除しても本当によろしいですか？
                </p>
                <p>削除すると元には戻せません。</p>
            </template>

            <template #footer>
                <ul>
                    <li>
                        <Button
                            text="キャンセル"
                            color="gray"
                            @click="closeDeleteAdmin()"
                        />
                    </li>
                    <li>
                        <Button
                            text="削除"
                            color="red"
                            @click="deleteAdmin()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <Modal
            title="新規追加"
            :isShow="isOpenCreateAdminModal"
            @close="closeCreateAdmin()"
        >
            <template #body>
                <ul class="Page__editItems">
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createAdminName"
                            >名前：<RequireLabel /></label
                        ><input
                            type="text"
                            v-model="createAdminName"
                            id="createAdminName"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createAdminPassword"
                            >パスワード：<RequireLabel /></label
                        ><input
                            type="password"
                            v-model="createAdminPassword"
                            id="createAdminPassword"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createAdminLevel"
                            >レベル：<RequireLabel /></label
                        ><input
                            type="number"
                            v-model="createAdminLevel"
                            min="0"
                            id="createAdminLevel"
                        />
                    </li>
                    <li class="Page__editItem">
                        <label class="Page__editLabel" for="createAdminRemarks"
                            >備考：</label
                        ><textarea
                            v-model="createAdminRemarks"
                            id="createAdminRemarks"
                        />
                    </li>
                </ul>
            </template>

            <template #footer>
                <ul>
                    <li>
                        <Button
                            text="キャンセル"
                            color="gray"
                            @click="closeCreateAdmin()"
                        />
                    </li>
                    <li>
                        <Button
                            text="作成"
                            color="blue"
                            :isDisabled="!isAdminCreateOk"
                            @click="saveCreateAdmin()"
                        />
                    </li>
                </ul>
            </template>
        </Modal>

        <Loading v-if="isLoading" :text="isLoadingText" />
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    & &__logoutButton {
        display: block;
        margin: 12px auto;
    }

    &__createButtonWrap {
        text-align: center;
    }

    & &__createButton {
        width: 150px;
        display: inline-block;
    }

    &__editItems {
        display: flex;
        flex-direction: column;
        row-gap: 12px;
    }

    &__editItem {
        display: flex;
        align-items: center;
    }

    &__editLabel {
        width: 180px;
    }

    &__LabelSmall {
        display: block;
        font-size: 11px;
    }
}
</style>
