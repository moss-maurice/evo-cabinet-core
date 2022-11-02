<?php use mmaurice\cabinet\core\App; ?>

<div style="margin-bottom: 15px;">
    Здравствуйте, <?= $order['user']['settings']['first_name']; ?>!
</div>

<div style="font-size: 22px; margin: 15px 0;">
    В рамках заказа №<?= $order['id']; ?> была произведена оплата на сумму <strong><?= (intval($response->Amount) / 100); ?> <?= $order['tour']['tv']['priceLabel']; ?></strong>
</div>
