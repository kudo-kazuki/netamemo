<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { User, UserFilterForm } from '@/types/types'
import dayjs from 'dayjs'

const authStore = useAuthStore()
const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

const formatDate = (value: string | number) => {
    return dayjs(value).isValid()
        ? dayjs(value).format('YYYY-MM-DD HH:mm:ss')
        : ''
}

const createDateFormatter = (field: keyof User) => {
    return (row: User) => {
        const value = row?.[field]
        return value ? formatDate(value) : ''
    }
}

const statusLabel = (status: number): string => {
    switch (status) {
        case 0:
            return '仮登録'
        case 1:
            return '本登録済'
        case 2:
            return 'アクセス禁止'
        case 3:
            return '退会済み'
        default:
            return '不明'
    }
}
const statusFormatter = (row: User) => {
    return statusLabel(row.status)
}

const tableRowClassName = (row: { row: User }) => {
    if (row.row.status === 0) {
        return 'rowStatusPending' // 仮登録の場合
    }
    if (row.row.status === 2) {
        return 'rowStatusBanned' // アクセス禁止中
    }
    if (row.row.status === 3) {
        return 'rowStatusDeleted' // 退会済み
    }
    return ''
}

const genderLabel = (gender: number): string => {
    switch (gender) {
        case 1:
            return '男'
        case 2:
            return '女'
        default:
            return '不明'
    }
}
const genderFormatter = (row: User) => {
    return genderLabel(row.gender ?? 0)
}

const userList = ref<User[]>([])

onMounted(async () => {
    const list = await authStore.fetchUserList()
    if (list) {
        userList.value = list
    }
})

const filterForm = ref<UserFilterForm>({
    sort: 'id_desc',
    name: '',
    email: '',
    message: '',
    profile: '',
    notes: '',
    status: '',
    gender: '',
    birthday_start: '',
    birthday_end: '',
    last_login_start: '',
    last_login_end: '',
})

const birthdayRange = ref<[string, string] | undefined>()
const lastLoginRange = ref<[string, string] | undefined>()

const onSearch = async () => {
    // 日付範囲を filterForm に反映
    if (birthdayRange.value) {
        filterForm.value.birthday_start = birthdayRange.value[0]
        filterForm.value.birthday_end = birthdayRange.value[1]
    } else {
        filterForm.value.birthday_start = ''
        filterForm.value.birthday_end = ''
    }

    if (lastLoginRange.value) {
        filterForm.value.last_login_start = lastLoginRange.value[0]
        filterForm.value.last_login_end = lastLoginRange.value[1]
    } else {
        filterForm.value.last_login_start = ''
        filterForm.value.last_login_end = ''
    }

    const list = await authStore.fetchUserList(filterForm.value)
    if (list) {
        userList.value = list
    }
}

const onReset = () => {
    filterForm.value = {
        sort: 'id_desc',
        name: '',
        email: '',
        message: '',
        profile: '',
        notes: '',
        status: '',
        gender: '',
        birthday_start: '',
        birthday_end: '',
        last_login_start: '',
        last_login_end: '',
    }
    birthdayRange.value = undefined
    lastLoginRange.value = undefined
}

const statusOptions: Record<number, string> = {
    0: '仮登録',
    1: '本登録済',
    2: 'アクセス禁止',
    3: '退会済み',
}
const previousStatusMap = ref<Record<number, number>>({})
const onChangeStatus = async (id: number, newStatus: number) => {
    const previousStatus = previousStatusMap.value[id] // 変更前のステータスを一旦保存

    try {
        await authStore.changeUserStatus(id, newStatus)
        ElMessage.success('ステータスを更新しました')
    } catch (err) {
        console.error(err)
        ElMessage.error('ステータス更新に失敗しました')

        // 元データの userList を修正（idで該当ユーザーを探して巻き戻し）
        const target = userList.value.find((u) => u.id === id)
        console.log(target, previousStatus)
        if (target) {
            target.status = previousStatus
        }
    }
}
</script>

<template>
    <section
        class="Page"
        :style="{ height: `${windowHeight}px` }"
        :data-device="deviceType"
        :data-windowWidth="windowWidth"
    >
        <h1>ユーザー管理ページ <router-link to="/admin">TOP</router-link></h1>

        <hr />

        <!-- 
        <div
            style="
                position: fixed;
                top: 0;
                right: 0;
                background-color: #fff;
                padding: 12px;
                z-index: 1;
                font-size: 12px;
                box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.4);
            "
        >
            <pre>{{ birthdayRange }}、{{ lastLoginRange }}</pre>
            <pre>{{ filterForm }}</pre>
        </div>
         -->

        <el-form :inline="true" class="mb-4">
            <el-form-item label="名前">
                <el-input
                    v-model="filterForm.name"
                    placeholder="名前で検索"
                    clearable
                />
            </el-form-item>
            <el-form-item label="メール">
                <el-input
                    v-model="filterForm.email"
                    placeholder="メールで検索"
                    clearable
                />
            </el-form-item>
            <el-form-item label="メッセージ">
                <el-input
                    v-model="filterForm.message"
                    placeholder="一言"
                    clearable
                />
            </el-form-item>
            <el-form-item label="プロフィール">
                <el-input
                    v-model="filterForm.profile"
                    placeholder="自己紹介"
                    clearable
                />
            </el-form-item>
            <el-form-item label="備考">
                <el-input
                    v-model="filterForm.notes"
                    placeholder="備考で検索"
                    clearable
                />
            </el-form-item>

            <el-form-item label="ステータス">
                <el-select
                    v-model="filterForm.status"
                    placeholder="選択"
                    clearable
                >
                    <el-option label="全て" value="" />
                    <el-option label="仮登録" value="0" />
                    <el-option label="本登録済" value="1" />
                    <el-option label="アクセス禁止" value="2" />
                    <el-option label="退会済み" value="3" />
                </el-select>
            </el-form-item>

            <el-form-item label="性別">
                <el-select
                    v-model="filterForm.gender"
                    placeholder="選択"
                    clearable
                >
                    <el-option label="全て" value="" />
                    <el-option label="男性" value="1" />
                    <el-option label="女性" value="2" />
                </el-select>
            </el-form-item>

            <el-form-item label="誕生日範囲">
                <el-date-picker
                    v-model="birthdayRange"
                    type="daterange"
                    start-placeholder="開始日"
                    end-placeholder="終了日"
                    unlink-panels
                    format="YYYY-MM-DD"
                    value-format="YYYY-MM-DD"
                    clearable
                />
            </el-form-item>

            <el-form-item label="最終ログイン範囲">
                <el-date-picker
                    v-model="lastLoginRange"
                    type="datetimerange"
                    start-placeholder="開始日時"
                    end-placeholder="終了日時"
                    unlink-panels
                    format="YYYY-MM-DD HH:mm"
                    value-format="YYYY-MM-DD HH:mm:ss"
                    clearable
                />
            </el-form-item>

            <el-form-item label="並び順">
                <el-select v-model="filterForm.sort" placeholder="ソート">
                    <el-option label="ID降順" value="id_desc" />
                    <el-option label="ID昇順" value="id_asc" />
                    <el-option label="作成日降順" value="created_at_desc" />
                    <el-option label="作成日昇順" value="created_at_asc" />
                    <el-option label="更新日降順" value="updated_at_desc" />
                    <el-option label="更新日昇順" value="updated_at_asc" />
                </el-select>
            </el-form-item>

            <el-form-item>
                <el-button type="primary" @click="onSearch">検索</el-button>
                <el-button @click="onReset">リセット</el-button>
            </el-form-item>
        </el-form>

        <el-table
            v-loading="authStore.userListLoading ?? false"
            :data="userList"
            style="width: 100%"
            table-layout="fixed"
            stripe
            border
            :row-class-name="tableRowClassName"
        >
            <el-table-column fixed prop="id" label="ID" width="60" />
            <el-table-column fixed prop="name" label="名前" width="130" />
            <el-table-column prop="email" label="メールアドレス" width="260" />
            <el-table-column
                prop="status"
                label="ステータス"
                :formatter="statusFormatter"
                width="156"
            >
                <template #default="{ row }">
                    <el-select
                        v-model="row.status"
                        size="default"
                        @change="(val) => onChangeStatus(row.id, val)"
                        style="width: 130px"
                        @visible-change="
                            (visible) => {
                                if (visible) {
                                    previousStatusMap[row.id] = row.status
                                }
                            }
                        "
                    >
                        <el-option
                            v-for="(label, key) in statusOptions"
                            :key="key"
                            :label="label"
                            :value="Number(key)"
                        />
                    </el-select>
                </template>
            </el-table-column>
            <el-table-column
                prop="last_login_at"
                label="最終ログイン"
                width="170"
            />
            <el-table-column prop="birthday" label="誕生日" width="110" />
            <el-table-column
                prop="gender"
                label="性別"
                width="60"
                :formatter="genderFormatter"
            />
            <el-table-column prop="message" label="一言" width="200" />
            <el-table-column
                prop="created_at"
                label="作成日"
                :formatter="createDateFormatter('created_at')"
                width="170"
            />
            <el-table-column
                prop="updated_at"
                label="更新日"
                :formatter="createDateFormatter('updated_at')"
                width="170"
            />
            <el-table-column prop="notes" label="備考" width="200" />
            <el-table-column prop="profile" label="自己紹介" width="600" />
            <el-table-column prop="provider" label="プロバイダ" width="100" />
        </el-table>

        <p v-if="!authStore.userListLoading && !userList.length" class="mt-4">
            ユーザーが見つかりませんでした。
        </p>
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    overflow: hidden;

    :deep(.rowStatusPending) {
        td {
            background-color: darkorange !important;
            color: #fff !important;
            text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
        }
    }

    :deep(.rowStatusBanned) {
        td {
            background-color: crimson !important;
            color: #fff !important;
            text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
        }
    }

    :deep(.rowStatusDeleted) {
        td {
            background-color: #222 !important;
            color: #fff !important;
            text-shadow: 1px 1px 0 rgba(0, 0, 0, 0.3);
        }
    }

    :deep(.rowStatusPending),
    :deep(.rowStatusBanned),
    :deep(.rowStatusDeleted) {
        td {
            .el-select {
                text-shadow: none;
            }
        }
    }
}
</style>
