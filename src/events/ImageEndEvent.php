<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 01.09.19
 * Time: 23:09
 */

namespace somov\ffmpeg\events;


use somov\ffmpeg\components\ImageFile;

/**
 * Class ImageEndEvent
 * @package somov\ffmpeg\events
 */
class ImageEndEvent extends EndEvent
{
    /**
     * @var ImageFile[]
     */
    public $images = [];
}