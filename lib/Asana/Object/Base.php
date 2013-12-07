<?php
/**
 * Asana object base class
 *
 * @link        http://developer.asana.com/documentation/
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace Asana\Object;

use Asana\Client;

/**
 * Asana object base class
 *
 */
class Base {

    /**
     * Base URL
     *
     * @var string
     */
    const BASE_URL = 'https://app.asana.com/0';

    /**
     * Asana client
     *
     * @var \Asana\Client
     */
    protected $_client;

    /**
     * Original object
     *
     * @var array
     */
    protected $_origin;

    /**
     * Constructor
     *
     * @param \Asana\Client $client
     * @param array $object
     * @return void
     */
    public function __construct(Client $client, array $object) {
        $this->_client = $client;
        $this->_origin = $object;
    }

    /**
     * Returns original properties.
     *
     * @param string $name
     * @return mixed
     */
    public function __get($name) {
        return $this->_origin[$name];
    }

}
