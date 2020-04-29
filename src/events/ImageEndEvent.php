<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.09.19
 * Time: 23:09
 */

namespace somov\ffmpeg\events;


use somov\ffmpeg\components\ImageFile;
use somov\ffmpeg\process\parser\ConvertEndImageParser;

/**
 * Class ImageEndEvent
 * @package somov\ffmpeg\events
 * @property ImageFile[] $images
 */
class ImageEndEvent extends EndEvent
{
    /**
     * @var ConvertEndImageParser
     */
    public $parser;

    /**
     * @return ImageFile[]
     */
    public function getImages()
    {
        if (isset($this->parser)) {
           return  $this->parser->getImages();
        }
        return [];
    }

}