<?php
/**
 * iCalendar component base class
 *
 * @link        http://tools.ietf.org/html/rfc5545
 * @copyright   Takeru Hirose <takeru0757@gmail.com>
 * @license     MIT License (http://opensource.org/licenses/MIT)
 */
namespace iCalendar;

/**
 * iCalendar component base class
 *
 */
class Base {

    /**
     * This component type.
     *
     * @var string
     */
    const TYPE = '';

    /**
     * Set properties.
     *
     * @var array
     */
    protected $_properties = array();

    /**
     * Added components.
     *
     * @var \iCalendar\Base[]
     */
    protected $_components = array();

    /**
     * Set a property to this component.
     *
     * @param string $key
     * @param string $value
     * @param array $params Property parameters.
     * @return void
     */
    public function setProperty($key, $value, $params = array()) {
        $value = str_replace('\\', '\\\\', $value);
        $value = str_replace('"', '\\"', $value);
        $value = str_replace(',', '\\,', $value);
        $value = str_replace(';', '\\;', $value);
        $value = preg_replace("/\r\n|\r|\n/", '\\n', $value);

        if ($params) {
            foreach ($params as $pKey => $pValue) {
                $key .= ";{$pKey}={$pValue}";
            }
        }

        $this->_properties[$key] = $value;
    }

    /**
     * Add a component to this component.
     *
     * @param \iCalendar\Base $component
     * @return void
     */
    public function addComponent(Base $component) {
        $this->_components[] = $component;
    }

    /**
     * Build this component.
     *
     * @return array The array of each lines.
     */
    public function build() {
        $lines = array();

        $lines[] = sprintf('BEGIN:%s', static::TYPE);

        foreach ($this->_properties as $key => $value) {
            $lines[] = "{$key}:{$value}";
        }

        foreach ($this->_components as $component) {
            $lines = array_merge($lines, $component->build());
        }

        $lines[] = sprintf('END:%s', static::TYPE);

        return $lines;
    }

    /**
     * Render this component.
     *
     * @return string The lines wrapped and concatenated with CR+LF.
     */
    public function render() {
        $lines = $this->build();

        foreach ($lines as &$line) {
            $line = $this->wrap($line);
        }

        return implode("\r\n", $lines);
    }

    /**
     * Wrap a string per 75 bytes.
     *
     * @param string $string
     * @return string The lines wrapped per 75 bytes.
     */
    public function wrap($string) {
        $lines = array();
        $array = preg_split("//u", $string, -1, PREG_SPLIT_NO_EMPTY);

        $line = '';
        foreach ($array as $char) {
            $charLen = strlen($char);
            $lineLen = strlen($line);
            if ($lineLen + $charLen > 75) {
                $lines[] = $line;
                $line = ' ' . $char;
            } else {
                $line .= $char;
            }
        }
        $lines[] = $line;

        return implode("\r\n", $lines);
    }

}
