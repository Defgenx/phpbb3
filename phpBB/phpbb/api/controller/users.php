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

namespace phpbb\api\controller;

use phpbb\api\extensions\auth_app;
use phpbb\config\config;
use phpbb\controller\helper;
use phpbb\db\driver\driver;
use phpbb\request\request;
use phpbb\template\template;
use phpbb\user;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Controller for authentication
 */
class users extends auth_app
{

	/**
	 * User object
	 * @var user
	 */
	protected $user;

	/**
	 * Controller helper object
	 * @var helper
	 */
	protected $helper;

	/**
	 * Template object
	 * @var template
	 */
	protected $template;

	/**
	 * Template object
	 * @var request
	 */
	protected $request;

	/**
	 * Config object
	 * @var config
	 */
	protected $config;

    /**
     * Auth object
     * @var \phpbb\auth\auth
     */
    protected $auth;

	/**
	 * Constructor
	 *
	 * @param user 						 $user
	 * @param helper 					 $helper The controller helper object that helps with rendering
	 * @param template 					 $template
	 * @param request 					 $request
	 * @param config 					 $config
     * @param \phpbb\auth\auth 			 $auth The phpBB auth object
	 */
	function __construct(user $user, helper $helper, template $template, request $request, config $config, \phpbb\auth\auth $auth)
	{
        // Call the parent construct to check API authentication
        parent::__construct($request);

		$this->user = $user;
		$this->helper = $helper;
		$this->template = $template;
		$this->request = $request;
		$this->config = $config;
        $this->auth = $auth;

		$this->user->add_lang('api');
	}

    public function login() {
        // Start session management
        $response = $this->auth->login($this->request->variable('username', ''), $this->request->variable('password', ''));
        return $this->sendResponse(['status' => 200, 'data' => $response]);
    }

    public function get_data() {
        global $user;
        return $this->sendResponse(['status' => 200, 'data' => $user]);
    }

    public function logout() {
        global $user;
        if ($user->data['is_registered']) {
            $user->session_kill();
            $user->session_begin();
            $bResponse = true;
        }
        else {
            $bResponse = false;
        }

        return $this->sendResponse(['status' => 200, 'data' => $bResponse]);
    }

    public function get_config() {
        global $config;

        return $this->sendResponse(['status' => 200, 'data' => $config->getIterator()]);
    }

    public function post() {
        $sMode = $this->request->variable('mode', 'post');
        $sSubject = $this->request->variable('subject', '');
        $sUsername = $this->request->variable('username', '');
        $sTopicType = (int)$this->request->variable('topic_type', 0);
        $aPoll = unserialize(base64_decode($this->request->variable('poll', '')));
        $aData = unserialize(base64_decode($this->request->variable('data', '')));
        $bUpdateMessage = (bool)$this->request->variable('updated', '');
        $bUpdateSearchIndex = (bool)$this->request->variable('search_index', '');

        // Allow a CRUD for posting on PhpBB
        submit_post($sMode, $sSubject, $sUsername, $sTopicType, $aPoll, $aData, $bUpdateMessage, $bUpdateSearchIndex);

        // Get some info of the creation of a new topic
        $aResult = [];
        if ($sMode === "post") {
            $sQuery = 'SELECT t.topic_id, t.forum_id FROM phpbb_posts as p INNER JOIN phpbb_topics as t ON p.poster_id = t.topic_poster WHERE t.topic_first_poster_name = \''.$sUsername.'\' AND t.topic_time = '.$aData['post_time'].' GROUP BY t.topic_id';
            $aResult = $this->database->sql_query($sQuery);
            $aResultQuery = $this->database->sql_fetchrowset($aResult);
        }

        return $this->sendResponse(['status' => 200, 'data' => $aResultQuery]);
    }
}