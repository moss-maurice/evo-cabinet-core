<?php use mmaurice\cabinet\core\providers\ModxProvider; ?>
<?php use mmaurice\cabinet\core\helpers\FormatHelper; ?>

<?php ModxProvider::modxInit(); ?>

<?php $modx = ModxProvider::getModx(); ?>
<?php $link = (!isset($_SERVER['HTTP_ORIGIN']) ? (!isset($_SERVER['HTTP_X_FORWARDED_PROTO']) ? (!isset($_SERVER['REQUEST_SCHEME']) ? 'http' : $_SERVER['REQUEST_SCHEME']) : $_SERVER['HTTP_X_FORWARDED_PROTO']) . '://' . (!isset($_SERVER['HTTP_HOST']) ? $_SERVER['SERVER_NAME'] : $_SERVER['HTTP_HOST']) : $_SERVER['HTTP_ORIGIN']); ?>

<div style="margin-bottom: 15px;">
    Здравствуйте<?= $user['attributes']['fullname'] ? ', ' . $user['attributes']['fullname'] : ''; ?>!
</div>

<div style="font-size: 22px; margin: 15px 0;">
    Ваш заказ №<?= $orderNumber ? $orderNumber : ''; ?> <span style="color: #37bb50">оформлен от <?= $currentDate ? $currentDate : ''; ?></span>
    <br />
</div>

<ul>
    <li>Тур: <a href="<?= $link; ?><?= $modx->makeUrl($tour['id']); ?>"><?= $tour['pagetitle']; ?></a> (<?= $tour['id']; ?>)</li>
    <li>ФИО: <?= $user['attributes']['fullname']; ?></li>
    <li>Телефон: <?= $user['attributes']['phone']; ?></li>
    <li>Email: <?= $user['attributes']['email']; ?></li>
    <li>Дата рейса: <?= FormatHelper::dateConvert($order['voyage']['voyage_out']['date'], 'Y-m-d H:i:s', 'Y-m-d'); ?></li>
    <li>Номеров: <?= !is_null($order['room']) ? $order['room']['pagetitle'] : '&mdash;'; ?></li>
    <li>Примечание: <?= $order['comment']; ?></li>
    <li>Цена: <?= $order['price']; ?></li>
</ul>

<p><a href="<?= $link; ?>/lk/orders?orderId=<?= $order['id']; ?>">Подробнее</a></p>
