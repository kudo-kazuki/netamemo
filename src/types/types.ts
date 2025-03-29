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
}
