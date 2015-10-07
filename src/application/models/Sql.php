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
 * To be used by all driver/engine specific
 * concrete classes
 *
 * @category CodePax
 * @subpackage Models
 * @copyright Copyright (c) 2012 Zitec COM srl, Romania
 * @license New BSD http://www.codepax.com/license.html
 * */
class CodePax_Sql
{
    /**
     * @var PDO
     * */
    protected $db;

    /**
     * @var CodePax_Config
     * */
    protected $configuration;

    /**
     * Sets up a connection to the database based on the configured engine
     *
     * @param CodePax_Config $configuration
     */
    public function __construct(CodePax_Config $configuration)
    {
        // set the database configuration details
        $this->configuration = $configuration;

        switch (DB_ENGINE) {
            case 'mysql':
                $this->db = new PDO(
                        sprintf('mysql:host=%s;dbname=%s', $this->configuration->db_host, $this->configuration->db_name), $this->configuration->db_user, $this->configuration->db_pass);
                break;
            case 'pgsql':
                $this->db = new PDO(
                        sprintf('pgsql:host=%s;port=5432;dbname=%s', $this->configuration->db_host, $this->configuration->db_name), $this->configuration->db_user, $this->configuration->db_pass);
                break;
            case 'sqlsrv':
                $this->db = sqlsrv_connect($this->configuration->db_host, array("Database" => $this->configuration->db_name, 'UID' => $this->configuration->db_user, 'PWD' => $this->configuration->db_pass, 'ConnectionPooling' => 0));
                break;
        }
    }

}
