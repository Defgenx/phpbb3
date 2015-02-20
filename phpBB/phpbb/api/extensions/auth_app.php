<?php
/**
 *
 * This file is part of the phpBB Forum Software package.
 *
 * @copyright (c) phpBB Limited <https://www.phpbb.com>
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 * For full copyright and license information, please see
 * the docs/CREDITS.txt file.
 *
 */

namespace phpbb\api\extensions;

use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\api\exception\api_exception;
use phpbb\api\exception\invalid_key_exception;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for authentication
 */
abstract class auth_app
{
    const AUTH_API_KEY = '';

    /**
     * Constructor
     *
     * @param request 					 $request
     */
    function __construct(request $request)
    {
        // This check prevents access to debug front controllers that are deployed by accident to production servers.
        // Feel free to remove this, extend it, or make something more sophisticated.
        $request->enable_super_globals();
        if (isset($_SERVER['HTTP_CLIENT_IP'])
            || isset($_SERVER['HTTP_X_FORWARDED_FOR'])
            || !in_array($request->variable('server_true_ip', ''), array(
                '37.59.39.109',
            ))
            || !isset($_SERVER['USER'])
            || !in_array(@$_SERVER['USER'], array(
                'www-data',
            ))
            || !isset($_SERVER['SERVER_NAME'])
            || !in_array(@$_SERVER['SERVER_NAME'], array(
                'worldxbox.fr',
            ))
            || !isset($_SERVER['SERVER_SOFTWARE'])
            || !in_array(@$_SERVER['SERVER_SOFTWARE'], array(
                'nginx/1.7.10',
            ))
        ) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Check '.basename(__FILE__).' for more information.');
        }

        $request->disable_super_globals();
        if ($request->variable('auth_token', '') !== self::AUTH_API_KEY) {
            header('HTTP/1.0 403 Forbidden');
            exit('You are not allowed to access this file. Contact the admin for more information.');
        }
    }

    protected function sendResponse (array $dataReponse) {
        return new JsonResponse($dataReponse, $dataReponse['status']);
    }
}
