<script setup lang="ts">
import { ref, watch } from 'vue'
import { UserForInput } from '@/types/types'
import cloneDeep from 'lodash/cloneDeep'

interface Props {
    input?: UserForInput
    isFirstCretae?: boolean
    isEditMode?: boolean
}

const props = withDefaults(defineProps<Props>(), {
    isEditMode: true,
})

const emit = defineEmits(['update:input', 'submit'])

const isEditMode = ref(props.isEditMode ?? true)
watch(
    () => props.isEditMode,
    (newVal) => {
        isEditMode.value = newVal
    },
)

const DEFAULT_INPUT: UserForInput = {
    name: '',
    email: '',
    birthday: '',
    gender: null,
    message: '',
    profile: '',
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
        input.value = cloneDeep({ ...newVal })
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

const birthdayModel = computed({
    get: () => input.value.birthday ?? '', // fallbackとして空文字
    set: (val) => {
        input.value.birthday = val
    },
})

const isOpenConfirm = ref(false)
const openConfirm = () => {
    isOpenConfirm.value = true
}
const closeConfirm = () => {
    isOpenConfirm.value = false
}

const errorMessages = ref<Record<string, string>>({
    name: '',
    password: '',
    email: '',
})
const onValidate = () => {
    errorMessages.value.name = ''
    errorMessages.value.email = ''

    if (!input.value.name) {
        errorMessages.value.name = '名前は必須項目です'
    }

    if (!input.value.password) {
        errorMessages.value.password = 'パスワードは必須項目です'
    }

    if (!input.value.email) {
        errorMessages.value.email = 'メールアドレスは必須項目です'
    } else {
        // メール形式チェック
        const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        if (!emailPattern.test(input.value.email)) {
            errorMessages.value.email =
                '正しいメールアドレスの形式で入力してください'
        }
    }

    if (
        !errorMessages.value.name &&
        !errorMessages.value.password &&
        !errorMessages.value.email
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
    <div class="UserCreateForm">
        <el-scrollbar>
            <table class="table">
                <tbody>
                    <tr>
                        <th>
                            <label for="name"
                                >名前<RequireLabel v-if="isEditMode"
                            /></label>
                        </th>
                        <td>
                            <input
                                v-if="isEditMode"
                                type="text"
                                v-model="input.name"
                                id="name"
                                :class="{ error: errorMessages.name }"
                            />
                            <p v-if="errorMessages.name" class="errorMessage">
                                {{ errorMessages.name }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="password"
                                >パスワード<RequireLabel v-if="isEditMode"
                            /></label>
                        </th>
                        <td>
                            <input
                                v-if="isEditMode"
                                type="password"
                                v-model="input.password"
                                id="password"
                                :class="{ error: errorMessages.password }"
                            />
                            <p
                                v-if="errorMessages.password"
                                class="errorMessage"
                            >
                                {{ errorMessages.password }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="email"
                                >メールアドレス<RequireLabel v-if="isEditMode"
                            /></label>
                        </th>
                        <td>
                            <input
                                v-if="isEditMode"
                                type="email"
                                v-model="input.email"
                                id="email"
                                :class="{ error: errorMessages.email }"
                            />
                            <p v-if="errorMessages.email" class="errorMessage">
                                {{ errorMessages.email }}
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="birthday">生年月日</label>
                        </th>
                        <td>
                            <el-date-picker
                                v-if="isEditMode"
                                id="birthday"
                                v-model="birthdayModel"
                                type="date"
                                placeholder=""
                                size="large"
                                :default-value="new Date(1990, 1, 1)"
                                value-format="YYYY-MM-DD"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th>性別</th>
                        <td>
                            <ul
                                v-if="isEditMode"
                                class="UserCreateForm__radioItems"
                            >
                                <li>
                                    <Radio
                                        id="men"
                                        text="男性"
                                        :value="1"
                                        v-model="input.gender"
                                    />
                                </li>
                                <li>
                                    <Radio
                                        id="woman"
                                        text="女性"
                                        :value="2"
                                        v-model="input.gender"
                                    />
                                </li>
                            </ul>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="message">一言メッセージ</label>
                        </th>
                        <td>
                            <textarea
                                v-if="isEditMode"
                                v-model="input.message"
                                id="message"
                            ></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="profile">自己紹介</label>
                        </th>
                        <td>
                            <textarea
                                v-if="isEditMode"
                                v-model="input.profile"
                                id="profile"
                            ></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </el-scrollbar>

        <div v-if="isEditMode" class="UserCreateForm__footer">
            <Button
                class="UserCreateForm__button"
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
                ><p>以下の内容で仮登録します。よろしいですか？</p>
                <table class="table">
                    <colgroup>
                        <col width="180" />
                    </colgroup>
                    <tbody>
                        <tr>
                            <th>名前&nbsp;<RequireLabel /></th>
                            <td>
                                {{ input.name }}
                            </td>
                        </tr>
                        <tr>
                            <th>パスワード&nbsp;<RequireLabel /></th>
                            <td>
                                {{ '●'.repeat(input.password?.length || 0) }}
                            </td>
                        </tr>
                        <tr>
                            <th>メールアドレス&nbsp;<RequireLabel /></th>
                            <td>
                                {{ input.email }}
                            </td>
                        </tr>
                        <tr>
                            <th>生年月日</th>
                            <td>
                                {{ birthdayModel }}
                            </td>
                        </tr>
                        <tr>
                            <th>性別</th>
                            <td>
                                {{ input.gender }}
                            </td>
                        </tr>
                        <tr>
                            <th>一言メッセージ</th>
                            <td>
                                <pre>{{ input.message }}</pre>
                            </td>
                        </tr>
                        <tr>
                            <th>自己紹介</th>
                            <td>
                                <pre>{{ input.profile }}</pre>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </template>
            <template #footer>
                <div class="UserCreateForm__confirmFooter">
                    <Button
                        class="UserCreateForm__button"
                        text="キャンセル"
                        color="gray"
                        @click="closeConfirm()"
                    />
                    <Button
                        class="UserCreateForm__button"
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
.UserCreateForm {
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
        width: 120px;
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
