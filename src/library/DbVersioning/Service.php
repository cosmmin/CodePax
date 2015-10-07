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
 * Service class for the DB versioning module
 *
 * @category CodePax
 * @subpackage DbVersioning
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_DbVersioning_Service
{

    /**
     * Stores the configuration variables for the application
     *
     * @var CodePax_Config
     */
    private $configuration;

    /**
     * Class constructor, sets the configuration and the files manager
     *
     * @param CodePax_Config $configuration
     */
    public function __construct($configuration)
    {
        $this->configuration = $configuration;
        $this->filesManager = new CodePax_DbVersioning_Files_Manager($this->configuration);
    }

    /**
     * This runs the database versioning scripts if any are available
     *
     * @param bool $preserveTestData indicates whether the test data should be preserved
     * @return array
     * @throws CodePax_DbVersioning_Exception
     */
    public function run($preserveTestData = false)
    {
        $db_versioning_handler = CodePax_DbVersioning_Environments_Factory::factory($this->configuration);

        // we can only generate test data on certain environments
        $generate_test_data = false;
        if ($this->configuration->application_environment == 'dev' && $preserveTestData) {
            $generate_test_data = true;
        }

        // run the change scripts
        $db_scripts_result = $db_versioning_handler->runScripts($generate_test_data);

        // unset some keys that are redundant on DEV
        unset($db_scripts_result['run_change_scripts'], $db_scripts_result['run_data_change_scripts']);

        return $db_scripts_result;
    }

    /**
     * Return database versioning information
     *
     * @return stdClass
     * @throws CodePax_DbVersioning_Exception
     */
    public function info()
    {
        $databaseVersioningInfo = new stdClass();

        // get current DB version
        $db_versions_model = CodePax_DbVersions::factory($this->configuration);

        if (in_array($this->configuration->application_environment, array('dev', 'prod'))) {
            $latest_baseline_file = $this->filesManager->getLatestBaselineVersion();
            if (!$db_versions_model->checkIsVersionRegistred($latest_baseline_file, CodePax_DbVersions::TYPE_BASELINE)) {
                $db_versions_model->addVersion($latest_baseline_file, CodePax_DbVersions::TYPE_BASELINE);
            }
        }

        $latest_structure_version = $db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_CHANGE_SCRIPT);
        $latest_data_version = $db_versions_model->getLatestVersion(CodePax_DbVersions::TYPE_DATA_CHANGE_SCRIPT);

        $databaseVersioningInfo->database_name = $this->configuration->db_name;
        $databaseVersioningInfo->database_structure_version = $latest_structure_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
        $databaseVersioningInfo->database_structure_last_update = $latest_structure_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE];
        $databaseVersioningInfo->database_data_version = $latest_data_version[CodePax_DbVersions::VERSION_ATTRIBUTE];
        $databaseVersioningInfo->database_data_last_update = !empty($latest_data_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE]) ? $latest_data_version[CodePax_DbVersions::DATE_ADDED_ATTRIBUTE] : 'n/a';

        // get change scripts to run
        $db_versioning_handler = CodePax_DbVersioning_Environments_Factory::factory($this->configuration);
        $change_scripts = $db_versioning_handler->getChangeScripts();
        $data_change_scripts = $db_versioning_handler->getDataChangeScripts();

        $new_baseline_available = false;
        if (in_array($this->configuration->application_environment, array('dev', 'prod'))) {
            $new_baseline_available = (version_compare($latest_structure_version[CodePax_DbVersions::VERSION_ATTRIBUTE], $db_versioning_handler->getLatestBaselineVersion()) == -1);
        }

        // database is up-to-date
        $databaseVersioningInfo->baseline_script = null;
        $databaseVersioningInfo->db_versioning_dev_note = false;
        $databaseVersioningInfo->db_scripts = array();
        $databaseVersioningInfo->data_db_scripts = array();

        if (empty($change_scripts) && empty($data_change_scripts) && !$new_baseline_available) {
            $databaseVersioningInfo->db_is_updated = true;
        } else {
            //database is not up to date
            $databaseVersioningInfo->db_is_updated = false;

            // data change scripts
            if (!empty($change_scripts)) {
                $databaseVersioningInfo->db_scripts = array_keys($change_scripts);
            }

            // data change scripts
            if (!empty($data_change_scripts)) {
                $databaseVersioningInfo->data_db_scripts = array_keys($data_change_scripts);
            }

            // when on DEV, show extra info
            if ($this->configuration->application_environment == 'dev') {
                $databaseVersioningInfo->baseline_script = $db_versioning_handler->getLatestBaselineVersion();
                $databaseVersioningInfo->db_versioning_dev_note = true;
            }
        }

        return $databaseVersioningInfo;
    }
}

