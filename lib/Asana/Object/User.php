<?php
/**
 * Asana user class
 *
 * @link        http://developer.asana.com/documentation/
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace Asana\Object;

/**
 * Asana user class
 *
 */
class User extends Base {

    /**
     * Returns tasks assigned to this user.
     *
     * @param array $fields
     * @param array $filters
     * @return \Asana\Object\Task[] The array of task objects.
     */
    public function tasks(array $fields = array(), array $filters = array()) {
        return $this->_client->tasks(array('assignee' => $this->id), $fields, $filters);
    }

}
