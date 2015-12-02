<?php

/**
 * CodePax
 *
 * LICENSE
 *
 * This source file is subject to the New BSD license that is bundled
 * with this package in the file LICENSE
 * It is also available through the world-wide-web at this URL:
 * http://www.codepax.com/license.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@codepax.com so we can send you a copy immediately.
 * */
/**
 * The main page of the app
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

$configuration = CodePax_Config::getInstance(CONFIG_PATH . 'config.ini');
$filesManager = new CodePax_DbVersioning_Files_Manager($configuration);

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('index');

//--- show Db versioning section
if (isset($configuration->use_db_versioning) && $configuration->use_db_versioning) {

    try {
        // get database versioning status from the service
        $databaseVersioningService = new CodePax_DbVersioning_Service($configuration);
        $databaseVersioningInfo = $databaseVersioningService->info();

        // forward the information to the view
        $view->use_db_versioning = true;
        $view->database_name = $databaseVersioningInfo->database_name;
        $view->database_structure_version = $databaseVersioningInfo->database_structure_version;
        $view->database_structure_last_update = $databaseVersioningInfo->database_structure_last_update;
        $view->database_data_version = $databaseVersioningInfo->database_data_version;
        $view->database_data_last_update = $databaseVersioningInfo->database_data_last_update;
        $view->db_is_updated = $databaseVersioningInfo->db_is_updated;
        $view->db_scripts = $databaseVersioningInfo->db_scripts;
        $view->data_db_scripts = $databaseVersioningInfo->data_db_scripts;
        $view->baseline_script = $databaseVersioningInfo->baseline_script;
        $view->db_versioning_dev_note = $databaseVersioningInfo->db_versioning_dev_note;
    } catch
    (PDOException $pdo_e) {
        $view->error_message = $pdo_e->getMessage();
        $view->render();
        exit();
    }
}

try {
    $view->render();
} catch (CodepPax_View_Exception $e) {
    echo $e->getMessage();
    exit();
}
