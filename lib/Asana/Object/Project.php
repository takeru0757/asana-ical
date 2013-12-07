<?php
/**
 * Asana project class
 *
 * @link        http://developer.asana.com/documentation/
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace Asana\Object;

/**
 * Asana project class
 *
 */
class Project extends Base {

    /**
     * Returns tasks belongs to this project.
     *
     * @param array $fields
     * @param array $filters
     * @return \Asana\Object\Task[] The array of task objects.
     */
    public function tasks(array $fields = array(), array $filters = array()) {
        return $this->_client->tasks(array('project' => $this->id), $fields, $filters);
    }

}
