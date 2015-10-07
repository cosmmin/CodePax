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
 * Called via AJAX to run the DB scripts
 *
 * @category CodePax
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 */
require '../application/bootstrap.php';

// initialize view object
$view = new CodePax_View();
$view->setViewsPath(VIEWS_PATH);
$view->setCurrentView('run_db_scripts');

try {
    $configuration = CodePax_Config::getInstance(CONFIG_PATH . 'config.ini');
    $dbVersioningService = new CodePax_DbVersioning_Service($configuration);

    $preserveTestData = false;
    if (isset($_POST['preserve_test_data'])) {
        $preserveTestData = $_POST['preserve_test_data'];
    }
    $result = $dbVersioningService->run($preserveTestData);
    $view->db_scripts = $result;

} catch (CodePax_DbVersioning_Exception $dbv_e) {
    $view->error_message = 'DB versioning error: ' . $dbv_e->getMessage();
} catch (Exception $e) {
    $view->error_message = 'Generic error: ' . $e->getMessage();
}

//render the view
try {
    $view->render();
} catch (CodePax_View_Exception $e) {
    echo $e->getMessage();
    exit();
}