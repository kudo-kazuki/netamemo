<script setup lang="ts">
import { ref, watch, computed } from 'vue'
import { PostForInput, TemplateWithHeadings } from '@/types/types'
import cloneDeep from 'lodash/cloneDeep'

interface Props {
    input?: PostForInput | null
    templateList?: TemplateWithHeadings[]
    isFirstCretae?: boolean
    isEditMode?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isEditMode: true,
})

const emit = defineEmits(['update:input', 'submit'])

const MAX_TITLE_LENGTH = 100
const MAX_HEADINGS_LENGTH = 10
const MAX_CONTENTS_LENGTH = 10000

const templateSelectItems = computed(() => {
    return (props.templateList ?? []).map((item) => ({
        value: item.id,
        label: item.title,
    }))
})

const isEditMode = ref(props.isEditMode ?? true)
watch(
    () => props.isEditMode,
    (newVal) => {
        isEditMode.value = newVal
    },
)
const setEditMode = (flg: boolean) => {
    isEditMode.value = flg
}

const contentsItemsByTemplateId = ref<Record<
    number,
    PostForInput['contents']
> | null>(null)
const initializeContentsItemsByTemplateId = () => {
    if (!props.templateList || !props.templateList.length) {
        contentsItemsByTemplateId.value = null
        return false
    }

    const result: Record<number, PostForInput['contents']> = {}

    props.templateList.forEach((template) => {
        result[template.id] = template.headings.map((heading) => ({
            heading_id: heading.id,
            content: '',
        }))
    })

    contentsItemsByTemplateId.value = result
}
watch(
    () => props.templateList,
    () => {
        initializeContentsItemsByTemplateId()
    },
    { immediate: true, deep: true },
)

const DEFAULT_INPUT: PostForInput = {
    template_id: undefined,
    title: '',
    contents: [
        {
            heading_id: null,
            content: '',
        },
    ],
}
const input = ref(
    props.input
        ? cloneDeep({ ...props.input })
        : {
              ...cloneDeep(DEFAULT_INPUT),
          },
)
watch(
    () => props.input,
    (newVal) => {
        input.value = cloneDeep(newVal) ?? cloneDeep(DEFAULT_INPUT)
    },
    { deep: true },
)
watch(
    () => input.value,
    (newVal) => {
        emit('update:input', newVal)
    },
    { deep: true },
)

// contentsItemsByTemplateId の選択中テンプレート（template_id）の内容と
// input.value.contents を双方向ではなく「片方向で同期」する watch 処理
let stopSyncWatcher: (() => void) | null = null
watch(
    () => input.value.template_id,
    (newTemplateId) => {
        // 前回の watch を解除（切り替え時に二重監視しないように）
        stopSyncWatcher?.()

        // template_id が未定義 or 該当データなし → 空で初期化して return
        if (
            newTemplateId === undefined ||
            !contentsItemsByTemplateId.value ||
            !contentsItemsByTemplateId.value[newTemplateId]
        ) {
            input.value.contents = []
            stopSyncWatcher = null
            return
        }

        if (
            typeof newTemplateId === 'number' &&
            contentsItemsByTemplateId.value &&
            contentsItemsByTemplateId.value[newTemplateId]
        ) {
            const contents = contentsItemsByTemplateId.value[newTemplateId]

            // 初期表示時に input.value.contents にコピー（shallow copy）
            input.value.contents = contents.map((item) => ({
                heading_id: item.heading_id,
                content: item.content,
            }))

            // contents の中の content の変更を監視して input.contents に同期（shallow copy）
            stopSyncWatcher = watch(
                () => contents.map((item) => item.content),
                (_, __) => {
                    input.value.contents = contents.map((item) => ({
                        heading_id: item.heading_id,
                        content: item.content,
                    }))
                },
                { deep: false },
            )
        }
    },
    { immediate: true },
)

const isOpenConfirm = ref(false)
const openConfirm = () => {
    isOpenConfirm.value = true
}
const closeConfirm = () => {
    isOpenConfirm.value = false
}

const errorMessages = ref<{
    template_id: string
    title: string
    contents: string[]
}>({
    template_id: '',
    title: '',
    contents: [],
})
const onValidate = () => {
    errorMessages.value.template_id = ''
    errorMessages.value.title = ''
    errorMessages.value.contents = []

    if (
        input.value.template_id === null ||
        input.value.template_id === undefined
    ) {
        errorMessages.value.template_id = 'ジャンルが指定されていません'
    }

    if (!input.value.title) {
        errorMessages.value.title = 'タイトルは必須項目です'
    }

    if (input.value.title.length >= MAX_TITLE_LENGTH) {
        errorMessages.value.title = `タイトルが長過ぎます(最大${MAX_TITLE_LENGTH}文字まで)`
    }

    // contents チェック（1項目ずつチェック）
    const contents = input.value.contents
    contents.forEach((content, index) => {
        let message = ''
        if (!content.heading_id) {
            message = '見出しが選択されていません'
        } else if (!content.content) {
            message = 'コンテンツが未入力です'
        } else if (content.content.length >= MAX_CONTENTS_LENGTH) {
            message = `コンテンツが長過ぎます（最大${MAX_CONTENTS_LENGTH}文字まで）`
        }

        errorMessages.value.contents[index] = message
    })

    // 全体のエラーが空なら confirm へ
    const hasContentsError = errorMessages.value.contents.some((msg) => msg)
    if (
        !errorMessages.value.template_id &&
        !errorMessages.value.title &&
        !hasContentsError
    ) {
        openConfirm()
    }

    return false
}

const onSubmit = () => {
    emit('submit', input.value)
    return false
}
</script>

<template>
    <div class="PostForm">
        <el-scrollbar>
            <table class="table">
                <tbody>
                    <tr>
                        <th>
                            <label for="template_id"
                                >ジャンル<RequireLabel v-if="isEditMode"
                            /></label>
                        </th>
                        <td>
                            <template v-if="!isEditMode && !isFirstCretae">{{
                                input.template_id ?? '-'
                            }}</template>
                            <el-select
                                id="template_id"
                                v-model="input.template_id"
                                placeholder=""
                            >
                                <el-option
                                    v-for="item in templateSelectItems"
                                    :key="item.value"
                                    :label="item.label"
                                    :value="item.value"
                                />
                            </el-select>
                            <p
                                v-if="errorMessages.template_id"
                                class="errorMessage"
                            >
                                {{ errorMessages.template_id }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="title"
                                >タイトル<RequireLabel v-if="isEditMode"
                            /></label>
                        </th>
                        <td>
                            <template v-if="!isEditMode && !isFirstCretae">{{
                                input.title ?? '-'
                            }}</template>
                            <input
                                v-if="isEditMode"
                                type="text"
                                v-model="input.title"
                                id="title"
                                :class="{ error: errorMessages.title }"
                            />
                            <p v-if="errorMessages.title" class="errorMessage">
                                {{ errorMessages.title }}
                            </p>
                        </td>
                    </tr>
                    <tr
                        v-if="
                            input.template_id !== undefined &&
                            contentsItemsByTemplateId
                        "
                    >
                        <th>内容</th>
                        <td>
                            <div
                                v-for="(
                                    contentItem, index
                                ) in contentsItemsByTemplateId[
                                    input.template_id!
                                ]"
                                :key="
                                    contentItem.heading_id ?? `heading-${index}`
                                "
                                class="contentBlock"
                            >
                                <h2 class="headingTitle">
                                    {{
                                        props.templateList
                                            ?.find(
                                                (t) =>
                                                    t.id === input.template_id,
                                            )
                                            ?.headings.find(
                                                (h) =>
                                                    h.id ===
                                                    contentItem.heading_id,
                                            )?.heading_title ||
                                        '（不明な見出し）'
                                    }}
                                </h2>
                                <textarea
                                    v-model="contentItem.content"
                                    class="contentTextarea"
                                ></textarea>
                                <p
                                    v-if="errorMessages.contents[index]"
                                    class="errorMessage"
                                >
                                    {{ errorMessages.contents[index] }}
                                </p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <pre>{{ contentsItemsByTemplateId }}</pre>
            ---------
            <pre style="white-space: pre">{{ input }}</pre>
        </el-scrollbar>

        <div class="PostForm__footer">
            <Button
                v-if="isEditMode || isFirstCretae"
                class="PostForm__button"
                color="blue"
                text="確認"
                size="m"
                @click="onValidate()"
            />
        </div>

        <Modal
            :isShow="isOpenConfirm"
            :title="`${isFirstCretae ? '投稿' : '編集'}内容確認`"
            @close="closeConfirm()"
        >
            <template #body
                ><p>
                    以下の内容で{{
                        isFirstCretae ? '投稿' : '上書き'
                    }}します。よろしいですか？
                </p>
                <table class="table">
                    <colgroup>
                        <col width="180" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>ジャンル&nbsp;<RequireLabel /></th>
                            <td>
                                {{ input.template_id }}
                            </td>
                        </tr>
                        <tr>
                            <th>タイトル&nbsp;<RequireLabel /></th>
                            <td>
                                {{ input.title }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
            <template #footer>
                <div class="PostForm__confirmFooter">
                    <Button
                        class="PostForm__button"
                        text="キャンセル"
                        color="gray"
                        @click="closeConfirm()"
                    />
                    <Button
                        class="PostForm__button"
                        :text="isFirstCretae ? '投稿' : '保存'"
                        color="blue"
                        @click="onSubmit()"
                    />
                </div>
            </template>
        </Modal>
    </div>
</template>

<style lang="scss" scoped>
.PostForm {
    display: flex;
    flex-direction: column;
    height: 100%;
    overflow: hidden;

    .el-date-editor.el-input {
        width: 100%;
    }

    &__radioItems {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
    }

    textarea {
        height: 200px;
    }

    &__footer {
        display: flex;
        justify-content: center;
        align-items: center;
        column-gap: 12px;
        flex-shrink: 0;
        padding: 12px 0;
    }

    &__button {
        width: 140px;
    }

    &__confirmFooter {
        display: flex;
        justify-content: center;
        align-items: center;
        column-gap: 16px;
    }

    @media (max-width: 767px) {
        .table {
            tr {
                th {
                    width: 100%;
                }
            }
        }
    }
}

:deep(.table) {
    table-layout: fixed;

    th {
        width: 180px;
        label {
            display: flex;
            column-gap: 4px;
            align-items: center;
        }
    }
}
</style>
