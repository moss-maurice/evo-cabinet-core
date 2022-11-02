<?php use mmaurice\cabinet\core\App; ?>

<!-- Auth block [ -->
<p>Вы запросили восстановление пароля. Если это действительно были вы, тогда для продолжения Вам необходимо перейти по <a href="<?= $_SERVER['REQUEST_SCHEME']; ?>://<?= $_SERVER['SERVER_NAME']; ?><?= App::init()->makeUrl('/{lk}/login/check-key/', ['key' => $key]); ?>">ссылке</a>.</p>
<!-- ] Auth block -->
