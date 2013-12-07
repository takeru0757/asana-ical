<?php
/**
 * Asana task class
 *
 * @link        http://developer.asana.com/documentation/
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace Asana\Object;

use Asana\Client;

/**
 * Asana task class
 *
 * @property-read boolean $public Whether this task is public
 * @property-read float $estimate The estimated days
 * @property-read string $summary The summary includes parent task names and project names
 * @property-read string[] $urls URLs
 * @property-read \DateTime $start Start time (due_on - (estimate - 1d))
 * @property-read \DateTime $end End time (due_on)
 */
class Task extends Base {

    /**
     * Projects this task belongs to
     *
     * @var Asana\Object\Project[]
     */
    protected $_projects = array();

    /**
     * Parent tasks
     *
     * @var array[]
     */
    protected $_parents = array();

    /**
     * The estimated days
     *
     * @var float
     */
    protected $_estimate = 0;

    /**
     * The summary includes parent task names and project names
     *
     * @var string
     */
    protected $_summary = '';

    /**
     * URLs
     *
     * @var string[]
     */
    protected $_urls = array();

    /**
     * Start date (due_on - (estimate - 1d))
     *
     * @var \DateTime
     */
    protected $_start = null;

    /**
     * End date (due_on)
     *
     * @var \DateTime
     */
    protected $_end = null;

    /**
     * Constructor
     *
     * @param \Asana\Client $client
     * @param array $object
     * @return void
     */
    public function __construct(Client $client, array $object) {
        parent::__construct($client, $object);
        $this->_parseAncestors($object);

        // Set estimate
        if (preg_match('/^\[(\d*\.?\d+)\]/', $this->name, $matches)) {
            $this->_estimate = (float) $matches[1];
        }

        // Set summary
        $summary = preg_replace('/^\[(\d*\.?\d+)\]\s*/', '', $this->name);

        if ($this->_parents) {
            $parents = array();
            foreach ($this->_parents as $parent) {
                $parents[] = $parent['name'];
            }
            $summary = implode(' > ', array_reverse($parents)) . ' > ' . $summary;
        }

        if ($this->_projects) {
            $projects = array();
            foreach ($this->_projects as $project) {
                $projects[] = $project->name;
            }
            $summary = '[' . implode('|', $projects) . '] ' . $summary;
        }

        $this->_summary = $summary;

        // Set URLs
        foreach ($this->_projects as $project) {
            $this->_urls[] = self::BASE_URL . "/{$project->id}/{$this->id}";
        }

        if ($this->due_on) {
            // Set start date
            $this->_start = new \DateTime($this->due_on);
            if ($this->_estimate > 1) {
                $interval = floor($this->_estimate) - 1;
                $this->_start->sub(new \DateInterval("P{$interval}D"));
            }

            // Set end date
            $this->_end = new \DateTime($this->due_on);
        }
    }

    /**
     * Set parent tasks and projects.
     *
     * @param array $task
     * @return void
     */
    protected function _parseAncestors($task) {
        if (!empty($task['parent'])) {
            $parent = $this->_client->request('GET', "tasks/{$task['parent']['id']}", array(), true);
            $this->_parents[] = $parent;
            $this->_parseAncestors($parent);
        } else if (!empty($task['projects'])) {
            foreach ($task['projects'] as $project) {
                $this->_projects[] = $this->_client->getProject($project['id']);
            }
        }
    }

    /**
     * Returns extended properties or original properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        switch ($name) {
            case 'public':
                return !empty($this->_projects);
            case 'estimate':
            case 'summary':
            case 'urls':
            case 'start':
            case 'end':
                return $this->{"_{$name}"};
        }
        return parent::__get($name);
    }

}
