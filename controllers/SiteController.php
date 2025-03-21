<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Laporan;
use app\models\BiroPekerjaan;
use dektrium\user\models\User;
use yii\data\ActiveDataProvider;

class SiteController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['index', 'tambahlaporan'], // Halaman yang ingin dibatasi aksesnya
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'], // Hanya untuk pengguna yang sudah login
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'], // Jika guest, maka redirect ke login
                        'denyCallback' => function ($rule, $action) {
                            return Yii::$app->response->redirect(['site/login']);
                        },
                    ],
                ],
            ],
        ];
    }

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    public function actionIndex()
    {
        $biroList = BiroPekerjaan::find()->select(['id', 'nama'])->asArray()->all();
        
        $query = Laporan::find()->with(['user.biroPekerjaan', 'kategori']);

        // Ambil parameter filter dari request
        $biroId = Yii::$app->request->get('biro_id');

        if (!empty($biroId)) {
            $query->joinWith('user')
                ->andWhere(['user.biro_pekerjaan_id' => $biroId]);
        }

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 10],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'biroList' => $biroList,
            'selectedBiro' => $biroId, // Kirim pilihan ke view
        ]);
    }
    public function actionLogin()
    {
        // $pass1 = Yii::$app->security->generatePasswordHash('admin');
        // $pass2 = Yii::$app->security->generatePasswordHash('aaron');
        // $pass3 = Yii::$app->security->generatePasswordHash('julyan');
        // var_dump($pass1, $pass2, $pass3);die;

        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        date_default_timezone_set('Asia/Jakarta');
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            Yii::$app->db->createCommand()->update('user', [
                'last_login_at' => date('Y-m-d H:i:s')
            ], ['id' => Yii::$app->user->id])->execute();
            return $this->goBack();
        }

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    public function actionDelete($id)
    {
        $model = Laporan::findOne($id);

        if ($model === null) {
            throw new NotFoundHttpException('Data laporan tidak ditemukan.');
        }

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Laporan berhasil dihapus.');
        } else {
            Yii::$app->session->setFlash('error', 'Gagal menghapus laporan.');
        }

        return $this->redirect(['index']);
    }

    public function actionLogout()
    {
        if (!Yii::$app->user->isGuest) {
            Yii::$app->user->logout();
        }
        return $this->redirect(['site/login']);
    }     
    
    public function actionTambahlaporan()
    {
        return $this->render('tambahlaporan');
    }
    public function actionTambahkategori()
    {
        return $this->render('tambahkategori');
    }
    public function actionTambahbiro()
    {
        return $this->render('tambahbiro');
    }
    public function actionDaftarpengguna()
    {
        return $this->render('daftarpengguna');
    }
    public function actionDaftarkategori()
    {
        return $this->render('daftarkategori');
    }
    public function actionDaftarbiro()
    {
        return $this->render('daftarbiro');
    }
}
