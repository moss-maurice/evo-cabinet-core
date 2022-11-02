<?php use mmaurice\cabinet\core\App; ?>

<!-- Auth block [ -->
<p>Мы придумали вам новый пароль! Ваши учётные данные:</p>
<ul>
    <li>Логин: <strong><?= $login; ?></strong></li>
    <li>Пароль: <strong><?= $password; ?></strong></li>
</ul>
<p>Вы можете сменить пароль в своём профиле: <a href="<?= $_SERVER['REQUEST_SCHEME']; ?>://<?= $_SERVER['SERVER_NAME']; ?><?= App::init()->makeUrl('/{lk}/login/'); ?>">Войти в личный кабинет</a>.</p>
<!-- ] Auth block -->
