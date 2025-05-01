<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useTemplateStore } from '@/stores/template'
import { useWindowHeight } from '@/composables/useWindowHeight'
import { useWindowWidthAndDevice } from '@/composables/useWindowWidthAndDevice'
import { TEMPLATE_VISIBILITY_TEXT } from '@/utils/constants'

const { windowHeight } = useWindowHeight()
const { windowWidth, deviceType } = useWindowWidthAndDevice()

const templateStore = useTemplateStore()

onMounted(() => {
    // トークンは store 側で自動取得 or localStorage から取得済み前提
    templateStore.fetchTemplateList()
})

const isOpenDelete = ref(false)
const currentId = ref<number | null>(null)
const isDeleting = ref(false)
const isDeletedComlete = ref(false)
const isDeletedFail = ref(false)

const openDelete = (id: number) => {
    currentId.value = id
    isOpenDelete.value = true
}
const closeDelete = () => {
    currentId.value = null
    isOpenDelete.value = false
    isDeletedComlete.value = false
    isDeletedFail.value = false
}
const currentTemplate = computed(() => {
    if (!currentId.value) {
        return null
    }

    const target = templateStore.templateList.find((item) => {
        return item.id === currentId.value
    })

    return target
})

const onDelete = async () => {
    if (!currentId.value || isDeleting.value) {
        return false
    }

    isDeleting.value = true

    try {
        await templateStore.deleteTemplate(currentId.value)
        isDeleting.value = false
        isDeletedComlete.value = true
    } catch (e) {
        console.error(e)
        isDeleting.value = false
        isDeletedFail.value = true
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
        <h1>ジャンル</h1>
        <p><RouterLink to="/mypage">マイページへ</RouterLink></p>
        <p><RouterLink to="/template/create">新規作成</RouterLink></p>

        <h2>作成済一覧</h2>
        <div class="Page__list">
            <el-scrollbar>
                <p v-if="templateStore.isLoading">読み込み中</p>
                <table
                    v-if="templateStore.templateList.length"
                    class="Page__table table"
                >
                    <colgroup>
                        <col />
                        <col />
                        <col width="140px" />
                        <col width="230px" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th>タイトル</th>
                            <th>見出し</th>
                            <th>公開範囲</th>
                            <th>最終更新日時</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="item in templateStore.templateList"
                            :key="item.id"
                        >
                            <td>{{ item.title }}</td>
                            <td>
                                <ul>
                                    <li
                                        v-for="hdg in item.headings"
                                        :key="hdg.heading_order"
                                    >
                                        {{ hdg.heading_title }}
                                    </li>
                                </ul>
                            </td>
                            <td>
                                {{ TEMPLATE_VISIBILITY_TEXT[item.visibility] }}
                            </td>
                            <td>
                                <div class="Page__actionsWrap">
                                    {{ item.updated_at }}
                                    <ul class="Page__actions">
                                        <li>
                                            <RouterLink
                                                class="Page__actionButton Page__actionButton--edit"
                                                :to="`/template/edit/${item.id}`"
                                            >
                                                編集
                                            </RouterLink>
                                        </li>
                                        <li>
                                            <button
                                                class="Page__actionButton Page__actionButton--delete"
                                                @click="openDelete(item.id)"
                                            >
                                                削除
                                            </button>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <p
                    v-if="
                        !templateStore.isLoading &&
                        !templateStore.templateList.length
                    "
                >
                    データがありません
                </p>
            </el-scrollbar>
        </div>

        <Modal
            :isShow="isOpenDelete"
            title="削除"
            @close="closeDelete()"
            size="m"
            isTextCenter
        >
            <template #body>
                <template v-if="!isDeletedComlete && !isDeletedFail"
                    ><p>{{ currentTemplate?.title }}<br />を削除します。</p>
                    <p>
                        本当によろしいですか？<br />（関連する投稿も全て消えます。）
                    </p></template
                >
                <p v-if="isDeletedComlete">削除しました。</p>
                <p v-if="isDeletedFail">
                    削除に失敗しました。<br />時間を置いて再度お試しください。
                </p>
            </template>
            <template #footer>
                <div class="Page__confirmFooter">
                    <Button
                        v-if="isDeletedComlete || isDeletedFail"
                        class="Page__confirmButton"
                        text="閉じる"
                        color="gray"
                        @click="closeDelete()"
                    />
                    <Button
                        v-if="!isDeletedComlete && !isDeletedFail"
                        class="Page__confirmButton"
                        text="キャンセル"
                        color="gray"
                        @click="closeDelete()"
                    />
                    <Button
                        v-if="!isDeletedComlete && !isDeletedFail"
                        class="Page__confirmButton"
                        text="削除"
                        color="red"
                        @click="onDelete()"
                    />
                </div>
            </template>
        </Modal>

        <Loading v-if="isDeleting" text="削除中" />
    </section>
</template>

<style lang="scss" scoped>
.Page {
    @include mixin.page;

    display: flex;
    flex-direction: column;

    &__list {
        height: 100%;
        overflow: hidden;
    }

    &__table {
        thead {
            tr {
                th {
                    position: sticky;
                    top: 0;
                }
            }
        }
    }

    &__actionsWrap {
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    &__actionButton {
        text-decoration: underline;

        &--edit {
            color: #2472ca;
        }

        &--delete {
            color: #aa180d;
        }

        &:hover {
            text-decoration: none;
        }
    }

    &__confirmFooter {
        display: flex;
        column-gap: 16px;
    }

    &__confirmButton {
        width: 140px;
    }
}
</style>
