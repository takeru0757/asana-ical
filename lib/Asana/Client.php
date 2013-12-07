<?php
/**
 * Asana client class
 *
 * @link        http://developer.asana.com/documentation/
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace Asana;

use Guzzle\Http\Client as HttpClient;
use Guzzle\Log\ClosureLogAdapter;
use Guzzle\Log\MessageFormatter;
use Guzzle\Plugin\Backoff\BackoffPlugin;
use Guzzle\Plugin\Log\LogPlugin;

/**
 * Asana client class
 *
 * @property-read \Asana\Object\Project[] $projects All projrcts in the workspace
 * @property-read \Asana\Object\User[] $users All users in the workspace
 */
class Client {

    /**
     * API endpoint
     *
     * @var string
     */
    const URL = 'https://app.asana.com/api/1.0';

    /**
     * API Key for Asana
     *
     * @var string
     */
    protected $API_KEY = '';

    /**
     * The Asana workspace ID
     *
     * @var string
     */
    protected $WORKSPACE_ID = '';

    /**
     * Configuration values
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Cached responces
     *
     * @var array
     */
    protected $_caches = array();

    /**
     * All projrcts in the workspace
     *
     * @var \Asana\Object\Project[]
     */
    protected $_projects = array();

    /**
     * All users in the workspace
     *
     * @var \Asana\Object\User[]
     */
    protected $_users = array();

    /**
     * Constructor
     *
     * @param string $API_KEY API Key for Asana.
     * @param string $WORKSPACE_ID The Asana workspace ID.
     * @param array $config
     * @return void
     */
    public function __construct($API_KEY, $WORKSPACE_ID, $config = array()) {
        $this->API_KEY = $API_KEY;
        $this->WORKSPACE_ID = $WORKSPACE_ID;
        $this->_config = $config;

        $projects = $this->request('GET', 'projects');
        foreach ($projects as $project) {
            $this->_projects[] = new Object\Project($this, $project);
        }

        $users = $this->request('GET', 'users', array('opt_fields' => 'name,email'));
        foreach ($users as $user) {
            $this->_users[] = new Object\User($this, $user);
        }
    }

    /**
     * Returns protected properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        if (in_array($name, array('users', 'projects'))) {
            return $this->{"_{$name}"};
        }
    }

    /**
     * Send a request.
     *
     * @param string $method The request HTTP method.
     * @param string $url The request URL following API endpoint.
     * @param array $data Sending data.
     * @param boolean $useCache Whether using cached data.
     * @return array Responced data.
     */
    public function request($method, $url, array $data = array(), $useCache = false) {
        $client = new HttpClient(static::URL);

        // @link http://docs.guzzlephp.org/en/latest/plugins/backoff-plugin.html
        $backoffPlugin = BackoffPlugin::getExponentialBackoff();
        $client->addSubscriber($backoffPlugin);

        // @link http://docs.guzzlephp.org/en/latest/plugins/log-plugin.html
        if (!empty($this->_config['log'])) {
            $log = $this->_config['log'];
            $logPlugin = new LogPlugin(new ClosureLogAdapter(function ($m) use ($log) {
                fwrite(fopen($log, 'a'), $m . PHP_EOL);
            }), MessageFormatter::SHORT_FORMAT);
            $client->addSubscriber($logPlugin);
        }

        // @link http://docs.guzzlephp.org/en/latest/http-client/request.html#http-errors
        $client->getEventDispatcher()->addListener('request.error', function(\Guzzle\Common\Event $event) {
            if ($event['response']->getStatusCode() === 429) {
                $retry = $event['response']->getHeader('Retry-After');
                sleep($retry);
                $newReq = $event['request']->clone();
                $event['response'] = $newReq->send();
                $event->stopPropagation();
            }
        });

        // Set the workspace ID
        $data['workspace'] = $this->WORKSPACE_ID;

        switch ($method) {
            case 'GET':
                $req = $client->get($url, null, array('query' => $data));
                break;
            case 'POST':
                $headers = array('Content-Type' => 'application/json');
                $req = $client->post($url, $headers, json_encode(compact('data')));
                break;
            default:
                throw new Exception('Not allowed method.');
                exit;
        }

        $cacheKey = $url . '?' . $req->getQuery();

        if ($useCache && array_key_exists($cacheKey, $this->_caches)) {
            $json = $this->_caches[$cacheKey];
        } else {
            $res = $req->setHeader('Accept', 'application/json')
                   ->setAuth($this->API_KEY, '')
                   ->send();

            $json = $res->json();

            if ($method === 'GET') {
                $this->_caches[$cacheKey] = $json;
            }
        }

        return $json['data'];
    }

    /**
     * Returns task objects.
     *
     * @param array $query
     * @param array $fields
     * @param array $filters
     * @return \Asana\Object\Task[] The array of task objects.
     */
    public function tasks(
        array $query = array(),
        array $fields = array(),
        array $filters = array()
    ) {
        $fields = array_merge(
            $fields,
            array('name', 'parent', 'projects', 'due_on'),
            array_keys($filters)
        );
        $query = array_merge($query, array('opt_fields' => implode(',', $fields)));
        $tasks = $this->request('GET', 'tasks', $query);

        $objects = array();

        foreach ($tasks as $task) {
            foreach ($filters as $key => $expect) {
                if (preg_match('/^(.+)\s!=$/', $key, $matches)) {
                    if ($task[$matches[1]] === $expect) continue 2;
                } else {
                    if ($task[$key] !== $expect) continue 2;
                }
            }
            $objects[] = new Object\Task($this, $task);
        }

        return $objects;
    }

    /**
     * Returns the project by ID.
     *
     * @param integer|string $id
     * @return Asana\Object\Project|false The project object if exists otherwise false.
     */
    public function getProject($id) {
        foreach ($this->_projects as $project) {
            if ($project->id == $id) return $project;
        }
        return false;
    }

    /**
     * Returns the user by ID.
     *
     * @param integer|string $id
     * @return Asana\Object\User|false The user object if exists otherwise false.
     */
    public function getUser($id) {
        foreach ($this->_users as $user) {
            if ($user->id == $id) return $user;
        }
        return false;
    }

}
