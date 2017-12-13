<?php
namespace Vpg\Disturb\Core\Dto;

use \Phalcon\Mvc\User\Component;
use \Phalcon\Config\Adapter\Json;


/**
 * Class Dto
 *
 * @package  Disturb\Core\Dto
 * @author   Jérome BOURGEAIS <jbourgeais@voyageprive.com>
 * @license  https://github.com/vpg/disturb/blob/master/LICENSE MIT Licence
 */
abstract class AbstractDto extends Component
{
    /**
     * @const string TYPE_STRING
     */
    const TYPE_STRING = 'string';

    /**
     * @const string TYPE_HASH
     */
    const TYPE_HASH = 'array';

    /**
     * @const string TYPE_PHALCON_CONFIG
     */
    const TYPE_PHALCON_CONFIG = 'Phalcon\Config';

    /**
     * @const string TYPE_PHALCON_JSON_CONFIG
     */
    const TYPE_PHALCON_JSON_CONFIG = 'Phalcon\Config\Adapter\Json';

    /**
     * @const string TYPE_PHALCON_PHP_CONFIG
     */
    const TYPE_PHALCON_PHP_CONFIG = 'Phalcon\Config\Adapter\Php';

    /**
     * Raw hash
     *
     * @var array
     */
    protected $rawHash = [];

    /**
     * Instanciates a new Message Dto according to the given data
     *
     * @param mixed $rawMixed could either be a string (json) or an array
     *
     * @throws InvalidInputTypeException
     */
    public function __construct($rawMixed)
    {
        $this->di->get('logr')->debug(json_encode(func_get_args()));
        $type = gettype($rawMixed);
        if ($type == 'object') {
            $type = get_class($rawMixed);
        }
        // Load conf according to the type
        $this->di->get('logr')->debug("Type : $type");
        switch ($type) {
            case self::TYPE_HASH:
                $this->rawHash = $rawMixed;
            break;
            case self::TYPE_STRING:
                // check json
                if (($rawHash = json_decode($rawMixed, true))) {
                    $this->rawHash = $rawHash;
                } elseif (is_readable($rawMixed)) {
                    $jsonConfigFilePath = $rawMixed;
                    $configJson = new Json($jsonConfigFilePath);
                    $this->rawHash = $configJson->toArray();
                } else {
                    throw new InvalidInputTypeException('Json file path not loadable' . $rawMixed);
                }
            break;
            case self::TYPE_PHALCON_CONFIG:
            case self::TYPE_PHALCON_JSON_CONFIG:
            case self::TYPE_PHALCON_PHP_CONFIG:
                $this->rawHash = $rawMixed->toArray();
            break;
            default:
                throw new InvalidInputTypeException("$type is not supported as Dto input");
        }
    }

    /**
     * Checks if the given props list exist in the current dto data
     *
     * @param array $requiredPropList the list of mandatory props
     *
     * @return array list of error
     */
    public function getMissingPropertyList($requiredPropList)
    {
        $missingPropList = [];
        foreach ($requiredPropList as $prop) {
            // xxx w/ refacto could be recursive
            if (is_array($prop)) {
                if (empty($this->rawHash[$prop[0]])) {
                    $missingPropList[] = [$prop[0]];
                    continue;
                }
                $deepProp = $this->rawHash[$prop[0]];
                $missingDeepProp = [$prop[0]];
                unset($prop[0]);
                foreach ($prop as $key) {
                    $missingDeepProp[] = $key;
                    if (empty($deepProp[$key])) {
                        $missingPropList[] = $missingDeepProp;
                        break;
                    }
                    $deepProp = $deepProp[$key];
                }
                continue;
            }
            if (empty($this->rawHash[$prop])) {
                $missingPropList[] = $prop;
            }

        }

        return $missingPropList;
    }

    /**
     * Returns the raw data hash
     *
     * @return array the raw data hash
     */
    public function getRawHash() : array
    {
        return $this->rawHash;
    }
}
