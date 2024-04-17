<?php
/**
 * WHMCS SDK Sample Addon Module
 *
 * An addon module allows you to add additional functionality to WHMCS. It
 * can provide both client and admin facing user interfaces, as well as
 * utilise hook functionality within WHMCS.
 *
 * This sample file demonstrates how an addon module for WHMCS should be
 * structured and exercises all supported functionality.
 *
 * Addon Modules are stored in the /modules/addons/ directory. The module
 * name you choose must be unique, and should be all lowercase, containing
 * only letters & numbers, always starting with a letter.
 *
 * Within the module itself, all functions must be prefixed with the module
 * filename, followed by an underscore, and then the function name. For this
 * example file, the filename is "irnic" and therefore all functions
 * begin "irnic_".
 *
 * For more information, please refer to the online documentation.
 *
 * @see https://developers.whmcs.com/addon-modules/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */

/**
 * Require any libraries needed for the module to function.
 * require_once __DIR__ . '/path/to/library/loader.php';
 *
 * Also, perform any initialization required by the service's library.
 */

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\irnic\Admin\AdminDispatcher;
use WHMCS\Module\Addon\irnic\Client\ClientDispatcher;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

/**
 * Define addon module configuration parameters.
 *
 * Includes a number of required system fields including name, description,
 * author, language and version.
 *
 * Also allows you to define any configuration parameters that should be
 * presented to the user when activating and configuring the module. These
 * values are then made available in all module function calls.
 *
 * Examples of each and their possible configuration parameters are provided in
 * the fields parameter below.
 *
 * @return array
 */
function irnic_config()
{
    return [
        // Display name for your module
        'name' => 'ماژول ایرنیک ',
        // Description displayed within the admin interface
        'description' => 'یک افزونه کاربردی برای سیستم مدیریت هاستینگ WHMCS می باشد که نمایندگان سامانه irnic را قادر می سازد تا براحتی اقدام به ارتباط با nic.ir کرده و تمامی امکانات و اتوماسیون ها را داشته باشند.',
        // Module author name
        'author' => 'Yazdani',
        // Default language
        'language' => 'farsi',
        // Version number
        'version' => '1.0',
        'fields' => [
            // a text field type allows for single line text input
            'deposit_code' => [
                'FriendlyName' => 'IRNIC Deposit Code',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'deposit Code',
            ],
            'auth_token' => [
                'FriendlyName' => 'IRNIC Authentication Token',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'IRNIC Authentication Token ( length > 50char ) without Bearer',
            ],
            'trid' => [
                'FriendlyName' => 'Request TrId',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'unique TrID for all requests',
            ],
            'admin_contact' => [
                'FriendlyName' => 'Admin Contact ID',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Irnic Admin contact',
            ],
            'technical_contact' => [
                'FriendlyName' => 'technical Contact ID',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Irnic Technical contact',
            ],
            'billing_contact' => [
                'FriendlyName' => 'billing Contact ID',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Irnic Billing contact',
            ],
            'ns_1' => [
                'FriendlyName' => 'name serve 1',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Name Server1',
            ],
            'ns_2' => [
                'FriendlyName' => 'name serve 2',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Name Server2',
            ],
            'ns_3' => [
                'FriendlyName' => 'name serve 3',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Name Server3',
            ],
            'ns_4' => [
                'FriendlyName' => 'name serve 4',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Name Server4',
            ],
            'ns_5' => [
                'FriendlyName' => 'name serve 5',
                'Type' => 'text',
                'Default' => '',
                'Description' => 'Name Server5',
            ],

        ]

    ];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 * Use this function to perform any database and schema modifications
 * required by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function irnic_activate()
{
    // Create custom tables and schema required by your module
    try {
        Capsule::schema()
            ->create(
                'irnic_logs',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->text('log');
                    $table->timestamps();
                }
            );
        Capsule::schema()
            ->create(
                'irnic_poll_logs',
                function ($table) {
                    /** @var \Illuminate\Database\Schema\Blueprint $table */
                    $table->increments('id');
                    $table->string('msg_id', 20);
                    $table->string('res_code', 20);
                    $table->string('qcount', 20);
                    $table->text('msg_index');
                    $table->text('msg_note');
                    $table->text('response_xml');
                    $table->string('res_date',50);
                    $table->timestamps();
                }
            );

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'This is a demo module only. '
                . 'In a real module you might report a success or instruct a '
                . 'user how to get started with it here.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            'status' => "error",
            'description' => 'Unable to create tables : ' . $e->getMessage(),
        ];
    }
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 * Use this function to undo any database and schema modifications
 * performed by your module.
 *
 * This function is optional.
 *
 * @see https://developers.whmcs.com/advanced/db-interaction/
 *
 * @return array Optional success/failure message
 */
function irnic_deactivate()
{
    // Undo any database and schema modifications made by your module here
    try {
        Capsule::schema()
            ->dropIfExists('irnic_logs');
        Capsule::schema()
            ->dropIfExists('irnic_poll_logs');

        return [
            // Supported values here include: success, error or info
            'status' => 'success',
            'description' => 'success deactivate module -- all tables deleted.',
        ];
    } catch (\Exception $e) {
        return [
            // Supported values here include: success, error or info
            "status" => "error",
            "description" => "Unable to drop tables : {$e->getMessage()}",
        ];
    }
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 * Should return HTML output for display to the admin user.
 *
 * This function is optional.
 *
 * @return string
 * @see irnic\Admin\Controller::index()
 *
 */
function irnic_output($vars)
{
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. irnics.php?module=irnic
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['Text Field Name'];
    $configPasswordField = $vars['Password Field Name'];
    $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['Dropdown Field Name'];
    $configRadioField = $vars['Radio Field Name'];
    $configTextareaField = $vars['Textarea Field Name'];

    // Dispatch and handle request here. What follows is a demonstration of one
    // possible way of handling this using a very basic dispatcher implementation.

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new AdminDispatcher();
    $response = $dispatcher->dispatch($action, $vars);
    echo $response;
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 * This function is optional.
 *
 * @param array $vars
 *
 * @return string
 */
function irnic_sidebar($vars)
{
    $sidebar = '<p>Developed By <a href="https://www.linkedin.com/in/ali-yazdanifar-98757721b/" target="_blank">Ali Yazdanifar</a> </p>';
    return $sidebar;
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 * Should return an array of output parameters.
 *
 * This function is optional.
 *
 * @return array
 * @see irnic\Client\Controller::index()
 *
 */
function irnic_clientarea($vars)
{
    // Get common module parameters
    $modulelink = $vars['modulelink']; // eg. index.php?m=irnic
    $version = $vars['version']; // eg. 1.0
    $_lang = $vars['_lang']; // an array of the currently loaded language variables

    // Get module configuration parameters
    $configTextField = $vars['Text Field Name'];
    $configPasswordField = $vars['Password Field Name'];
    $configCheckboxField = $vars['Checkbox Field Name'];
    $configDropdownField = $vars['Dropdown Field Name'];
    $configRadioField = $vars['Radio Field Name'];
    $configTextareaField = $vars['Textarea Field Name'];

    /**
     * Dispatch and handle request here. What follows is a demonstration of one
     * possible way of handling this using a very basic dispatcher implementation.
     */

    $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

    $dispatcher = new ClientDispatcher();
    return $dispatcher->dispatch($action, $vars);
}
