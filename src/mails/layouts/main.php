
<?php use mmaurice\cabinet\core\providers\ModxProvider; ?>

<?php ModxProvider::modxInit(); ?>
<?php $modx = ModxProvider::getModx(); ?>
<?php $logo = $modx->getConfig('client_logo'); ?>
<?php $siteName = $modx->getConfig('client_siteName'); ?>
<?php $sitePhone = $modx->getConfig('client_sitePhone'); ?>
<?php $siteEmail = $modx->getConfig('client_siteEmail'); ?>
<?php $siteCompanyName = $modx->getConfig('client_siteCompanyName'); ?>

<div style="max-width: 645px; min-width: 320px; margin: 0px auto; padding: 10px; background-color: #ffffff; box-shadow: 0 0 0 1px #dcdcdc; font-family: Arial, Helvetica, sans-serif; font-size: 16px; line-height: 1.5;">
    <div style="display:flex; flex-wrap:wrap; align-items: center; border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 30px;">
<?php if (!empty($logo)) : ?>
        <img style="max-width:220px;" alt="logo" src="<?= $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/' . $logo; ?>">
<?php endif; ?>
<?php /*
        <div style="display:flex; flex-wrap:wrap; align-items: center; flex-grow:1;">
            <a style="color: #4681f1; margin: 5px 5px 5px auto;" href="<?= $siteName; ?>"><?= $siteName; ?></a>
        </div>
*/ ?>
    </div>

    <?= $content ? $content : ''; ?>

    <div style="font-size: 14px; border-top: 1px solid #ccc; padding-top: 10px; margin-top: 30px;">
        <div style="text-align: center; margin: 10px 0;">Если возникли вопросы, обращайтесь к нам, мы всегда рады помочь!</div>
<?php if (!empty($sitePhone)) : ?>
        <div style="text-align: center; margin: 10px 0;">
            Телефон: <a style="color: #4681f1" href="tel:<?= $sitePhone; ?>"><?= $sitePhone; ?></a>
        </div>
<?php endif; ?>
<?php if (!empty($siteEmail)) : ?>
        <div style="text-align: center; margin: 10px 0;">
            E-mail: <a style="color: #4681f1" href="mailto:<?= $siteEmail; ?>"><?= $siteEmail; ?></a>
        </div>
<?php endif; ?>
<?php if (!empty($siteCompanyName)) : ?>
        <div style="text-align: right;"><?= $siteCompanyName; ?></div>
<?php endif; ?>
    </div>
</div>
