import axios, { AxiosRequestHeaders } from 'axios'
import { encode, decode } from '@msgpack/msgpack'

type MsgPackRequestOptions = {
    headers?: AxiosRequestHeaders
    token?: string
}

/**
 * MessagePack形式でPOSTリクエストを送信し、MessagePackでレスポンスを受け取る
 */
export async function msgpackRequest<T = any>(
    url: string,
    payload: Record<string, any>,
    options: MsgPackRequestOptions = {},
): Promise<T> {
    const headers = {
        'Content-Type': 'application/x-msgpack',
        Accept: 'application/x-msgpack',
        ...options.headers,
    }

    if (options.token) {
        headers['Authorization'] = `Bearer ${options.token}`
    }

    const response = await axios.post(url, encode(payload), {
        headers,
        responseType: 'arraybuffer',
    })

    return decode(new Uint8Array(response.data)) as T
}
