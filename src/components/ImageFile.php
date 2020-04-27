<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 02.09.19
 * Time: 15:13
 */

namespace somov\ffmpeg\components;


use somov\ffmpeg\process\ImageProcess;
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
     * @var  ImageProcess
     */
    private $_process;


    /**
     * ImageFile constructor.
     * @param ImageProcess $process
     * @param array $config
     */
    public function __construct(ImageProcess $process, array $config = [])
    {
        $this->_process = $process;
        parent::__construct($config);
    }

    /**
     * @return ImageProcess
     */
    public function getProcess()
    {
        return $this->_process;
    }

}