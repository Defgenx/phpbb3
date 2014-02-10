<?php
/**
 *
 * @package controller
 * @copyright (c) 2013 phpBB Group
 * @license http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 */

namespace phpbb\controller\api;

/**
 * @ignore
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

use phpbb\model\exception\api_exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;

/**
 * Controller for the api of a phpBB forum
 * @package phpBB3
 */
class forum
{
	/**
	 * API Model
	 * @var \phpbb\model\repository\forum
	 */
	protected $forum_repository;

	/**
	 * Config object
	 * @var \phpbb\config\config
	 */
	protected $config;

	/**
	 * Auth repository object
	 * @var \phpbb\model\repository\auth
	 */
	protected $auth_repository;

	/**
	 * Constructor
	 *
	 * @param \phpbb\model\repository\forum $forum_repository
	 * @param \phpbb\config\config $config
	 * @param \phpbb\model\repository\auth $auth_repository
	 */
	function __construct(\phpbb\model\repository\forum $forum_repository, \phpbb\config\config $config,
						 \phpbb\model\repository\auth $auth_repository)
	{
		$this->forum_repository = $forum_repository;
		$this->config = $config;
		$this->auth_repository = $auth_repository;
	}

	/**
	 * Controller method to return a list of forums
	 *
	 * Accessible trough /api/forums/{forum_id} (no {forum_id} defaults to 0)
	 * Method: GET
	 *
	 * @param int $forum_id The forum to fetch, 0 fetches everything
	 * @return Response an array of forums, serialized to json
	 */
	public function forums($forum_id)
	{
		$serializer = new Serializer(array(
			new \phpbb\model\normalizer\forum(),
		), array(new JsonEncoder()));

		try
		{
			$user_id = $this->auth_repository->auth($forum_id);

			$forums = $this->forum_repository->get($forum_id, $user_id);

			$response = array(
				'status' => 200,
				'data' => $serializer->normalize($forums),
			);
		}
		catch (api_exception $e)
		{
			$response = array(
				'status' => $e->getCode(),
				'data' => array(
					'error' => $e->getMessage(),
					'valid' => false,
				),
			);
		}


		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response['status']);
	}

	/**
	 * Controller method to return a list of topics in a given forum
	 *
	 * Accesible trough /api/forums/{forum_id}/topics/{page} (no {page} defaults to 1)
	 * Method: GET
	 *
	 * @param int $forum_id the forum to retrieve topics from
	 * @param int $page the page to get
	 * @return Response an array of topics, serialized to json
	 */
	public function topics($forum_id, $page)
	{


		$serializer = new Serializer(array(
			new \phpbb\model\normalizer\topic(),
		), array(new JsonEncoder()));

		try {
			$user_id = $this->auth_repository->auth($forum_id);

			$topics = $this->forum_repository->get_topics($forum_id, $page);

			$response = array(
				'status' => 200,
				'total' => $topics['total'],
				'per_page' => $topics['per_page'],
				'page' => $topics['page'],
				'last_page' => $topics['last_page'],
				'data' => $serializer->normalize($topics['topics']),
			);
		}
		catch (api_exception $e)
		{
			$response = array(
				'status' => $e->getCode(),
				'data' => array(
					'error' => $e->getMessage(),
					'valid' => false,
				),
			);
		}

		$json = $serializer->serialize($response, 'json');

		return new Response($json, $response['status']);
	}

}
