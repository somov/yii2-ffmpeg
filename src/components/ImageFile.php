<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 02.09.19
 * Time: 15:13
 */

namespace somov\ffmpeg\components;


use somov\ffmpeg\process\parser\ConvertEndImageParser;
use yii\base\BaseObject;

/**
 * Class ImageFile
 * @package somov\ffmpeg\components
 */
class ImageFile extends BaseObject
{
    /**
     * @var integer
     */
    public $index;

    /**
     * @var string
     */
    public $file;

    /**
     * @var string $size
     */
    public $size;

    /**
     * @var float
     */
    public $time;

    /**
     * @var  ConvertEndImageParser
     */
    private $_parser;


    /**
     * ImageFile constructor.
     * @param ConvertEndImageParser $parser
     * @param array $config
     */
    public function __construct(ConvertEndImageParser $parser, array $config = [])
    {
        $this->_parser = $parser;

        parent::__construct($config);
    }

    /**
     * @return ConvertEndImageParser
     */
    public function getParser()
    {
        return $this->_parser;
    }

}