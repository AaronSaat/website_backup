<?php
use yii\bootstrap\Nav;
use yii\helpers\Html;

?>
<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
        <div class="pull-left image">
            <?php if (!Yii::$app->user->isGuest): ?>
                <?php $user = Yii::$app->user->identity; ?>
                <?php $foto = ($user->username === 'admin') ? 'user1-128x128.jpg' : 'user2-160x160.jpg'; ?>
            <?php else: ?>
                <?php $foto = 'user6-128x128.jpg'; ?>
            <?php endif; ?>
            
            <img src="<?= $directoryAsset ?>/img/<?= $foto ?>" class="img-circle" alt="User Image"/>
        </div>
        <div class="pull-left info">
            <p>
                <?php if (!Yii::$app->user->isGuest): ?>
                    <?= Html::encode($user->nama) ?>
                <?php else: ?>
                    Guest
                <?php endif; ?>
            </p>

            <?php if (!Yii::$app->user->isGuest): ?>
                <?php if ($user->username === 'admin'): ?>
                    <a href="#"><i class="fa fa-user-shield"></i> Admin</a>
                <?php else: ?>
                    <a href="#"><i class="fa fa-briefcase"></i> <?= Html::encode($user->biroPekerjaan->nama) ?></a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

        <!-- search form -->
        <!-- <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form> -->
        <!-- /.search form -->

        <?= Nav::widget([
            'encodeLabels' => false,
            'options' => ['class' => 'sidebar-menu'],
            'items' => array_filter([
                '<li class="header">Menu</li>',
                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                ['label' => '<span class="fa fa-file-code-o"></span> Gii', 'url' => ['/gii']] : null,
                
                Yii::$app->user->identity ?
                ['label' => '<i class="fa fa-th-list"></i> Lihat Laporan Backup', 'url' => ['/site/index']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                ['label' => '<i class="fa fa-th-list"></i> Lihat Daftar Pengguna', 'url' => ['/pengguna/daftarpengguna']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                ['label' => '<i class="fa fa-th-list"></i> Lihat Daftar Biro Pekerjaan', 'url' => ['/biro/daftarbiro']] : null,

                Yii::$app->user->identity && Yii::$app->user->identity->username === 'admin' ? 
                ['label' => '<i class="fa fa-th-list"></i> Lihat Daftar Kategori', 'url' => ['/kategori/daftarkategori']] : null,

                Yii::$app->user->identity ?
                ['label' => '<i class="fa fa-plus-square"></i> Tambah Laporan Baru', 'url' => ['/site/tambahlaporan']] : null,

                Yii::$app->user->identity ?
                '<li class="header"></li>' : null,

                Yii::$app->user->isGuest ? 
                ['label' => '<span class="glyphicon glyphicon-lock"></span> Masuk', 'url' => ['/site/login']] : 
                ['label' => '<span class="glyphicon glyphicon-off"></span> Logout (' . Yii::$app->user->identity->nama . ')',
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post']],
            ]),
        ]); 
        ?>
    </section>

</aside>
