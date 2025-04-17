<?php
namespace app\controllers;

use Yii;
use yii\rest\Controller;
use yii\web\Response;
use app\models\User;
use yii\filters\auth\HttpBearerAuth;
use \Firebase\JWT\JWT;

class ApiController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // JSON response
        $behaviors['contentNegotiator']['formats']['application/json'] = Response::FORMAT_JSON;

        // Hapus authenticator jika ada (biar bebas akses)
        unset($behaviors['authenticator']);

        return $behaviors;
    }

    // GET /api/user?id=1
    public function actionUser($id)
    {
        $user = User::findOne($id);

        if ($user === null) {
            return [
                'success' => false,
                'message' => 'User tidak ditemukan.',
            ];
        }

        $roles = Yii::$app->authManager->getRolesByUser($user->id);
        $roleNames = array_keys($roles);
        $role = isset($roleNames[0]) ? $roleNames[0] : null;

        return [
            'success' => true,
            'data' => [
                'id' => $user->id,
                'username' => $user->username,
                'biro_pekerjaan' => $user->biroPekerjaan->nama,
                'role' => $role,
            ],
        ];
    }

    public $enableCsrfValidation = false;
    public function actionLogin()
    {
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;

        // $data = Yii::$app->request->bodyParams;
        $json = Yii::$app->request->getRawBody();
        $data = json_decode($json, true);

        $username = $data['username'] ?? null;
        $password = $data['password'] ?? null;

        if (!$username || !$password) {
            return [
                'success' => false,
                'message' => 'Username atau password tidak ada.',
            ];
        }

        $user = User::find()->where(['username' => $username])->one();

        if ($user && Yii::$app->security->validatePassword($password, $user->password_hash)) {
            // Membuat JWT Token
            $key = 'a232bf7cea69ea60ef52b1713c49938bdf4b0c0131def42ca2ad1957780ec804'; 
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600;  // token berlaku 1 jam

            $roles = Yii::$app->authManager->getRolesByUser($user->id);
            $roleNames = array_keys($roles);
            $role = isset($roleNames[0]) ? $roleNames[0] : null;

            $payload = [
                'username' => $user->username,
                'role' => $role,
                'iat' => $issuedAt,
                'exp' => $expirationTime
            ];

            // Generate JWT
            $jwt = JWT::encode($payload, $key, 'HS256');

            return [
                'success' => true,
                'message' => 'Login berhasil',
                'username' => $user->username,
                'role' => $role,
                'token' => $jwt,  
            ];
        }

        return [
            'success' => false,
            'message' => 'Username atau password salah',
        ];
    }
}
