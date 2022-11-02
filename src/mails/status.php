<?php use mmaurice\cabinet\core\App; ?>

<div style="margin-bottom: 15px;">
    Здравствуйте, <?= $order['user']['settings']['first_name']; ?>!
</div>

<div style="font-size: 22px; margin: 15px 0;">
    У вашего заказа №<?= $order['id']; ?> изменился статус на "<span style="color: #37bb50"><?= $order['status']['name']; ?></span>"
</div>
