<?php
require_once __DIR__ . '/../bootstrap.php';

use Models\User;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class UserController
{
    public function create(array $input)
    {
        $rules = User::createRules();

        $errors = validate($input, $rules);

        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }

        // 同じメールアドレスの仮登録をチェックして上書き or エラー
        $existing = User::where('email', $input['email'])->first();
        if ($existing) {
            if ((int)$existing->status == 0) {
                $createdAt = strtotime($existing->email_verify_token_created_at ?? '');
                $expiresAt = $createdAt + (60 * 60 * 24); // 24時間

                if (time() > $expiresAt) {
                    // 有効期限切れの仮登録 → 削除
                    $existing->delete();
                } else {
                    // 有効期限内の仮登録 → 再登録禁止
                    msgpack_response(['message' => 'このメールアドレスは既に仮登録されています。'], 409);
                }
            } else {
                // 本登録済み or アクセス禁止/退会済み → エラー
                msgpack_response(['message' => 'すでに登録されているメールアドレスです'], 409);
            }
        }

        try {
            $token = bin2hex(random_bytes(32)); // トークン生成（64文字）
            $now = date('Y-m-d H:i:s');

            $user = new User();
            $user->name = $input['name'];
            $user->email = $input['email'];
            $user->password = password_hash($input['password'], PASSWORD_DEFAULT);
            $user->status = 0; // 仮登録
            $user->email_verify_token = $token;
            $user->email_verify_token_created_at = $now;
            $user->created_at = $now;
            $user->updated_at = $now;

            // オプション項目（未入力なら null）
            $user->birthday = empty($input['birthday']) ? null : $input['birthday'];
            $user->gender   = $input['gender'] ?? null;
            $user->message  = $input['message'] ?? null;
            $user->profile  = $input['profile'] ?? null;
            $user->notes    = $input['notes'] ?? null;

            $user->save();

            // メール送信処理
            if (isset($_ENV['MAIL_ENABLED']) && $_ENV['MAIL_ENABLED'] === 'true') {
                $this->sendVerificationEmail($input['email'], $input['name'], $token);
            }

            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response(['message' => '登録処理に失敗しました。時間をおいて再度お試しください。', 'error' => $e->getMessage()], 500);
        }
    }

    public function getInfo(): void
    {
        $authUser = requireUser();
        $user = \Models\User::find($authUser->sub);

        if (!$user) {
            msgpack_response(['message' => 'ユーザーが見つかりません'], 404);
        }

        msgpack_response($user->toArray());
    }

    private function sendVerificationEmail(string $toEmail, string $toName, string $token): void
    {
        try {
            $verifyUrl = rtrim($_ENV['APP_URL'], '/') . '/api/user/verify.php?token=' . urlencode($token);

            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';

            // mail() を使う
            $mail->isMail();

            $mail->setFrom('no-reply@example.com', '登録システム');
            $mail->addAddress($toEmail, $toName);

            $mail->Subject = '【仮登録】メールアドレス確認のお願い';
        $mail->Body = <<<EOT
{$toName} 様

ご登録ありがとうございます。
以下のリンクをクリックして本登録を完了してください：

{$verifyUrl}

※このリンクの有効期限は24時間です。
EOT;

            $mail->send();
        } catch (Exception $e) {
            // ここでログ出力
            error_log('[メール送信失敗] ' . $e->getMessage());

            // フロントにエラー通知
            msgpack_response([
                'message' => 'メール送信に失敗しました（ローカルでは正常なメール送信は行えません）',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    public function update(array $input): void
    {
        $authUser = requireUser(); // 認証チェックして、ログインユーザー情報取得
        $user = \Models\User::find($authUser->sub);

        if (!$user) {
            msgpack_response(['message' => 'ユーザーが見つかりません'], 404);
        }

        // バリデーション
        $rules = \Models\User::updateRules(); // ← Userモデルに定義済み
        $errors = validate($input, $rules);

        if (!empty($errors)) {
            msgpack_response(['message' => 'バリデーションエラー', 'errors' => $errors], 422);
        }

        try {
            // 必要なフィールドだけ上書き
            $user->name = $input['name'];
            $user->email = $input['email'];
            $user->birthday = $input['birthday'] ?? null;
            $user->gender = $input['gender'] ?? null;
            $user->message = $input['message'] ?? null;
            $user->profile = $input['profile'] ?? null;
            $user->save();

            msgpack_response(['success' => true]);
        } catch (Exception $e) {
            msgpack_response([
                'message' => '更新処理に失敗しました',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

}
