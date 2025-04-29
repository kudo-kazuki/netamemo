<script setup lang="ts">
import { ref, watch } from 'vue'
import { TemplateForInput } from '@/types/types'
import cloneDeep from 'lodash/cloneDeep'

interface Props {
    input?: TemplateForInput | null
    isFirstCretae?: boolean
    isEditMode?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isEditMode: true,
})

const emit = defineEmits(['update:input', 'submit'])

const MAX_TITLE_LENGTH = 100
const MAX_HEADINGS_LENGTH = 10

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

const DEFAULT_INPUT: TemplateForInput = {
    title: '',
    visibility: 0,
    headings: [
        {
            heading_order: 1,
            heading_title: '',
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

const isOpenConfirm = ref(false)
const openConfirm = () => {
    isOpenConfirm.value = true
}
const closeConfirm = () => {
    isOpenConfirm.value = false
}

const VISIBILITY_TEXT: Record<number, string> = {
    0: '非公開',
    1: 'フレンドのみ',
    2: '全体',
}

const errorMessages = ref<Record<string, string>>({
    title: '',
    visibility: '',
    headings: '',
})
const onValidate = () => {
    errorMessages.value.title = ''
    errorMessages.value.visibility = ''
    errorMessages.value.headings = ''

    if (!input.value.title) {
        errorMessages.value.title = 'タイトルは必須項目です'
    }

    if (input.value.title.length >= MAX_TITLE_LENGTH) {
        errorMessages.value.title = `タイトルが長過ぎます(最大${MAX_TITLE_LENGTH}文字まで)`
    }

    if (
        input.value.visibility === null ||
        input.value.visibility === undefined
    ) {
        errorMessages.value.visibility = '公開範囲は必須項目です'
    }

    if (!input.value.headings.length) {
        errorMessages.value.headings = '見出しは1つ以上必要です'
    } else if (
        input.value.headings.some(
            (heading) => heading.heading_title.trim() === '',
        )
    ) {
        errorMessages.value.headings = '空白の見出しがあります'
    } else if (
        input.value.headings.some(
            (heading) =>
                heading.heading_title.trim().length >= MAX_TITLE_LENGTH,
        )
    ) {
        errorMessages.value.headings = `長過ぎる見出しがあります(最大${MAX_TITLE_LENGTH}文字まで)`
    }

    if (
        !errorMessages.value.title &&
        !errorMessages.value.visibility &&
        !errorMessages.value.headings
    ) {
        openConfirm()
    }

    return false
}

const onSubmit = () => {
    emit('submit', input.value)
    return false
}

const addHeading = () => {
    if (input.value.headings.length >= MAX_HEADINGS_LENGTH) {
        return false
    }

    input.value.headings.push({
        heading_order: input.value.headings.length + 1,
        heading_title: '',
    })
}
</script>

<template>
    <div class="TemplateForm">
        {{ input }}
        <el-scrollbar>
            <table class="table">
                <tbody>
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
                    <tr>
                        <th>公開範囲</th>
                        <td>
                            <template v-if="!isEditMode && !isFirstCretae">{{
                                input.visibility
                                    ? VISIBILITY_TEXT[input.visibility]
                                    : '-'
                            }}</template>
                            <ul
                                v-if="isEditMode"
                                class="TemplateForm__radioItems"
                            >
                                <li>
                                    <Radio
                                        id="visibility0"
                                        :text="VISIBILITY_TEXT[0]"
                                        :value="0"
                                        v-model="input.visibility"
                                    />
                                </li>
                                <li>
                                    <Radio
                                        id="visibility1"
                                        :text="VISIBILITY_TEXT[1]"
                                        :value="1"
                                        v-model="input.visibility"
                                    />
                                </li>
                                <li>
                                    <Radio
                                        id="visibility2"
                                        :text="VISIBILITY_TEXT[2]"
                                        :value="2"
                                        v-model="input.visibility"
                                    />
                                </li>
                            </ul>
                            <p
                                v-if="errorMessages.visibility"
                                class="errorMessage"
                            >
                                {{ errorMessages.visibility }}
                            </p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <h2>見出し</h2>
            <ol>
                <li v-for="(item, index) in input.headings" :key="index">
                    <input type="text" v-model="item.heading_title" />
                </li>
            </ol>
            <p v-if="errorMessages.headings" class="errorMessage">
                {{ errorMessages.headings }}
            </p>
            <Button
                v-if="isEditMode"
                class="TemplateForm__button"
                color="gray"
                text="追加"
                size="s"
                :isDisabled="input.headings.length >= MAX_HEADINGS_LENGTH"
                @click="addHeading()"
            />
        </el-scrollbar>

        <div class="TemplateForm__footer">
            <Button
                v-if="!isEditMode && !isFirstCretae"
                class="TemplateForm__button"
                color="orange"
                text="編集"
                size="m"
                :isDisabled="props.input === null"
                @click="setEditMode(true)"
            />
            <Button
                v-if="isEditMode && !isFirstCretae"
                class="TemplateForm__button"
                text="キャンセル"
                color="gray"
                size="m"
                @click="setEditMode(false)"
            />
            <Button
                v-if="isEditMode || isFirstCretae"
                class="TemplateForm__button"
                color="blue"
                text="確認"
                size="m"
                @click="onValidate()"
            />
        </div>

        <Modal
            :isShow="isOpenConfirm"
            title="登録内容確認"
            @close="closeConfirm()"
        >
            <template #body
                ><p>以下の内容で登録します。よろしいですか？</p>
                <table class="table">
                    <colgroup>
                        <col width="180" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>タイトル&nbsp;<RequireLabel /></th>
                            <td>
                                {{ input.title }}
                            </td>
                        </tr>
                        <tr>
                            <th>公開範囲&nbsp;<RequireLabel /></th>
                            <td>
                                {{ VISIBILITY_TEXT[input.visibility] }}
                            </td>
                        </tr>
                        <tr>
                            <th>見出し</th>
                            <td>
                                <ol>
                                    <li
                                        v-for="(item, index) in input.headings"
                                        :key="index"
                                    >
                                        {{ item.heading_title }}
                                    </li>
                                </ol>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
            <template #footer>
                <div class="TemplateForm__confirmFooter">
                    <Button
                        class="TemplateForm__button"
                        text="キャンセル"
                        color="gray"
                        @click="closeConfirm()"
                    />
                    <Button
                        class="TemplateForm__button"
                        text="登録"
                        color="blue"
                        @click="onSubmit()"
                    />
                </div>
            </template>
        </Modal>
    </div>
</template>

<style lang="scss" scoped>
.TemplateForm {
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
