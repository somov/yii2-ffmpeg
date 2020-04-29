<?php
/**
 *
 * User: develop
 * Date: 04.07.2018
 */

namespace somov\ffmpeg\process\parser;


use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Class ConfigParser
 * @package somov\ffmpeg\process\parser
 */
abstract class ConfigParser extends BaseObject
{
    /**
     * @var string
     */
    protected $rawPattern;
    /**
     * @var string
     */
    protected $fieldsPattern;
    /**
     * @var array
     */
    protected $listAttributes = [];
    /**
     * @var ConfigItem[]
     */
    private $_items = [];

    /**
     * FormatsParser constructor.
     * @param array $data
     * @param array $config
     */
    public function __construct($data, array $config = [])
    {
        $this->compile(implode('', $data));
        parent::__construct($config);
    }

    /**
     * @param $raw
     * @throws \Exception
     */
    private function compile($raw)
    {
        if (!preg_match($this->rawPattern, $raw, $m)) {
            throw new \Exception('Error compile raw ' . StringHelper::basename(static::class));
        }
        $raw = $m[1];

        preg_match_all($this->fieldsPattern, $raw, $m);

        foreach ($m[0] as $code => $v) {
            $format = new ConfigItem($m['mode'][$code], $m['name'][$code], $m['description'][$code]);
            $this->_items[$format->name] = $format;
        }
    }

    /**
     * @return array
     */
    public function getListArray()
    {
        return ArrayHelper::toArray($this->_items,
            [
                ConfigItem::class => ['name', 'description'] + $this->listAttributes
            ],
            true);
    }

    /**
     * @param $name
     * @return bool
     */
    public function itemExists($name)
    {
        return $this->findItem($name) !== null;
    }

    /**
     * @param $name
     * @return mixed|ConfigItem
     */
    public function findItem($name)
    {
        return (isset($this->_items[$name])) ? $this->_items[$name] : null;
    }


}