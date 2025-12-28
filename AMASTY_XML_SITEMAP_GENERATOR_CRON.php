<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Magento\Framework\App\Bootstrap;

require __DIR__ . '/../app/bootstrap.php';

$params = $_SERVER;
$bootstrap = Bootstrap::create(BP, $params);
$obj = $bootstrap->getObjectManager();

$appState = $obj->get(\Magento\Framework\App\State::class);
try {
    $appState->setAreaCode('frontend');
} catch (\Exception $e) {
}

$cron = $obj->get(\Amasty\XmlSitemap\Model\Cron\GenerateSitemap::class);
$cron->execute();
