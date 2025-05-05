export interface JWTPayload {
    exp: number
    name: string
    level: number | null
    sub: number
    role: string
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

export interface UserJWTPayload {
    exp: number
    name: string
    email: string | null
    sub: number
    role: string
}

export interface UserAuthState {
    token: string | null
    isAuthenticated: boolean
    name: string | null
    email: string | null
    id: number | null
}

/**ユーザーがtemplate登録時に入力するとき
- title: string
- visibility: number // 0 | 1 | 2
- headings: {
        heading_order: number
        heading_title: string
    }[]
*/
export interface TemplateForInput {
    id?: number
    title: string
    visibility: number // 0 | 1 | 2
    headings: {
        heading_order: number
        heading_title: string
    }[]
}

export interface TemplateWithHeadings extends TemplateForInput {
    id: number
    created_at: string
    updated_at: string
    headings: (TemplateForInput['headings'][number] & { id: number })[]
}

export interface TemplateForInputWithId extends TemplateForInput {
    id: number
}

// contents の構造を共通化
export interface PostContentBase {
    content: string
}

// 入力用（post作成用）：heading_id が必要
export interface PostContentForInput extends PostContentBase {
    heading_id: number | null
}

// 表示用（一覧表示用）：heading_order が必要
export interface PostContentForDisplay extends PostContentBase {
    heading_order: number
}

// 投稿作成用
export interface PostForInput {
    template_id: number | undefined
    title: string
    contents: PostContentForInput[]
}

// 投稿一覧取得時の1件分
export interface PostItem {
    id: number
    template_id: number
    title: string
    created_at: string
    updated_at: string
    contents: PostContentForDisplay[]
}

export interface PostListResponse {
    total: number
    page: number
    per_page: number
    posts: PostItem[]
}
