<script setup lang="ts">
import { ref, watch } from 'vue'
import { UserForInput } from '@/types/types'
import dayjs from 'dayjs'
import cloneDeep from 'lodash/cloneDeep'

interface Props {
    input?: UserForInput
    isFirstCretae?: boolean
}

const props = withDefaults(defineProps<Props>(), {})

const emit = defineEmits(['update:input'])

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
</script>

<template>
    <div class="UserCreateForm">
        {{ input }}
        <el-scrollbar>
            <table>
                <tbody>
                    <tr>
                        <th>
                            <label for="name">名前<RequireLabel /></label>
                        </th>
                        <td>
                            <input type="text" v-model="input.name" id="name" />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="email"
                                >メールアドレス<RequireLabel
                            /></label>
                        </th>
                        <td>
                            <input
                                type="email"
                                v-model="input.email"
                                id="email"
                            />
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="birthday">生年月日</label>
                        </th>
                        <td>
                            <el-date-picker
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
                            <ul>
                                <li>
                                    <Radio
                                        id="men"
                                        text="男"
                                        :value="1"
                                        v-model="input.gender"
                                    />
                                </li>
                                <li>
                                    <Radio
                                        id="woman"
                                        text="女"
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
                                v-model="input.profile"
                                id="profile"
                            ></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </el-scrollbar>
    </div>
</template>

<style lang="scss" scoped>
.UserCreateForm {
    overflow: hidden;
}
</style>
