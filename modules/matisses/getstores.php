<?php
include_once('../../config/config.inc.php');
require_once _PS_MODULE_DIR_.'matisses/matisses.php';

$matisses = new matisses();
$objstores = $matisses->getActiveStores();
$stores = array();

foreach ($objstores as $store) {
    array_push($stores, $store);
}

echo json_encode($stores);

?>