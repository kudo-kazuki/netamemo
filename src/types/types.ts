export interface JWTPayload {
    exp: number
    name: string
    level: number | null
    sub: number
}

export interface Admin {
    id: number
    name: string
    level: number
    remarks?: string
    created_at: string
    updated_at: string
}

export interface AuthState {
    token: string | null
    isAuthenticated: boolean
    name: string | null
    level: number | null
    adminList: Admin[]
    userListLoading?: boolean
    userStatusChangeLoading?: boolean
}

/**ユーザーが登録時に入力するとき */
export interface UserForInput {
    name?: string
    password?: string
    email?: string
    birthday?: string | null
    gender?: number | null
    message?: string
    profile?: string
}

export interface User extends UserForInput {
    id: number
    name: string
    password: string
    email: string
    status: number
    last_login_at: string | null
    notes?: string
    created_at: string
    updated_at: string
}

export interface UserFilterForm {
    sort: string
    name: string
    email: string
    message: string
    profile: string
    notes: string
    status: string
    gender: string
    birthday_start: string
    birthday_end: string
    last_login_start: string
    last_login_end: string
}
