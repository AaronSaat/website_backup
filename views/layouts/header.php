<?php
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

/* @var $this \yii\web\View */
/* @var $content string */
?>

<header class="main-header">

    <?= Html::a(Yii::$app->name, Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">
                <!-- <li class="dropdown messages-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="fa fa-bell-o"></i>
                        <span class="label label-success">1</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="header">You have 4 messages</li>
                        <li>
                            <ul class="menu">
                                <li>
                                    <a href="#">
                                        <div class="pull-left">
                                            <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle"
                                                 alt="User Image"/>
                                        </div>
                                        <h4>
                                            Support Team
                                            <small><i class="fa fa-clock-o"></i> 5 mins</small>
                                        </h4>
                                        <p>Why not buy a new awesome theme?</p>
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-users text-aqua"></i> 5 new members joined today
                                    </a>
                                </li>
                                <li>
                                    <a href="#">
                                        <i class="fa fa-warning text-yellow"></i> Very long description here that may
                                        not fit into the page and may cause design problems
                                    </a>
                                </li>
                            </ul>
                        </li>
                        <li class="footer"><a href="#">See All Messages</a></li>
                    </ul>
                </li> -->

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <i class="glyphicon glyphicon-user"></i>
                        <span>
                            <?= Yii::$app->user->isGuest ? 'Tamu' : Yii::$app->user->identity->nama ?>
                            <i class="caret"></i>
                        </span>
                    </a>
                    <ul class="dropdown-menu">
                        <li class="user-header">
                            <i class="glyphicon glyphicon-user" style="font-size: 50px;"></i>
                            <p>
                                <?= Yii::$app->user->isGuest ? 'Tamu' : Yii::$app->user->identity->nama ?>
                                <small><?= Yii::$app->user->isGuest ? 'Belum Masuk' : 'Terdaftar Sejak ' . Yii::$app->user->identity->created_at ?></small>
                            </p>
                        </li>
                        <li class="user-footer">
                            <?php if (Yii::$app->user->isGuest): ?>
                                <div class="text-center">
                                    <?= Html::a('<span class="glyphicon glyphicon-lock"></span> Masuk', ['/site/login'], ['class' => 'btn btn-success btn-block']) ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center">
                                    <?= Html::a(
                                        '<span class="glyphicon glyphicon-off"></span> Keluar (' . Yii::$app->user->identity->nama . ')',
                                        ['/site/logout'],
                                        ['class' => 'btn btn-danger btn-block', 'data-method' => 'post']
                                    ) ?>
                                </div>
                            <?php endif; ?>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>
</header>
