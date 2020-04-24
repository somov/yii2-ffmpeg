<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 16.04.20
 * Time: 15:20
 */

namespace somov\ffmpeg\components;


use somov\common\exceptions\ProcessException;

/**
 * Class FfmpegException
 * @package somov\ffmpeg\components
 */
class FfmpegException extends ProcessException
{
    /**
     * @var Ffmpeg
     */
    public $ffmpeg;

    /**
     * @return string
     */
    public function getName()
    {
        return 'Ffmpeg exception';
    }
}