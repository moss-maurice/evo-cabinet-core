<?php

namespace mmaurice\cabinet\core\providers;

use mmaurice\cabinet\core\App;

class ModxProvider
{
    public static function getModx()
    {
        global $modx;

        return $modx;
    }

    public static function getConfig($key, $defaultValue = null)
    {
        $modx = self::getModx();

        if (array_key_exists($key, $modx->config)) {
            return $modx->config[$key];
        }

        return $defaultValue;
    }

    public static function renderChunk($chunkName, $variables = [])
    {
        $modx = self::getModx();

        $variables['webRoot'] = App::getWebRoot();
        $content = $modx->parseChunk($chunkName, $variables, '[+', '+]');

        return $content;
    }

    public static function redirect($url)
    {
        $modx = self::getModx();

        $modx->sendRedirect($url);

        exit();
    }

    public static function isCliMode()
    {
        if (!defined('MODX_CLI')) {
            return false;
        }

        if (MODX_CLI) {
            return true;
        }

        return false;
    }

    public static function modxInit()
    {
        $modx = self::getModx();
        global $database_type;
        global $database_server;
        global $database_user;
        global $database_password;
        global $database_connection_charset;
        global $database_connection_method;
        global $dbase;
        global $table_prefix;
        global $base_url;
        global $base_path;

        if (isset($modx) and !empty($modx)) {
            return $modx;
        }

        if (!defined('MODX_API_MODE')) {
            define('MODX_API_MODE', true);
        }

        if (!defined('MODX_BASE_PATH')) {
            define('MODX_BASE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/');
        }

        if (!defined('MODX_BASE_URL')) {
            define('MODX_BASE_URL', '/');
        }

        if (!defined('MODX_SITE_URL')) {
            define('MODX_SITE_URL', 'http://localhost/');
        }

        $_SESSION['mgrValidated'] = true;

        @include_once(realpath($_SERVER['DOCUMENT_ROOT'] . '/index.php'));

        $modx->db->connect();

        if (empty($modx->config)) {
            $modx->getSettings();

            return true;
        }

        return false;
    }

    public static function sendForward($id, $content = '')
    {
        $modx = self::getModx();

        $modx->forwards = $modx->forwards - 1;
        $modx->documentIdentifier = $id;
        $modx->documentMethod = 'id';
        $modx->documentObject = $modx->getDocumentObject($modx->documentMethod, $modx->documentIdentifier, 'prepareResponse');
        $modx->documentObject['content'] = $content;
        $modx->documentName = &$modx->documentObject['pagetitle'];

        if ($modx->documentObject['donthit'] == 1) {
            $modx->config['track_visitors'] = 0;
        }

        if ($modx->documentObject['deleted'] == 1) {
            $modx->sendErrorPage();
        } else if ($modx->documentObject['published'] == 0) {
            $modx->_sendErrorForUnpubPage();
        } else if ($modx->documentObject['type'] == 'reference') {
            $modx->_sendRedirectForRefPage($modx->documentObject['content']);
        }

        if (!$modx->documentObject['template']) {
            $templateCode = '[*content*]';
        } else {
            $templateCode = $modx->_getTemplateCodeFromDB($modx->documentObject['template']);
        }

        if (substr($templateCode, 0, 8) === '@INCLUDE') {
            $templateCode = $modx->atBindInclude($templateCode);
        }

        $modx->documentContent = &$templateCode;
        //$modx->invokeEvent('OnLoadWebDocument');
        $modx->documentContent = $modx->parseDocumentSource($templateCode);
        $modx->documentGenerated = 1;

        $modx->outputContent();

        App::init()->end();
    }

    public static function getDocument($id)
    {
        $modx = self::getModx();

        return $modx->getDocument($id);
    }
}
