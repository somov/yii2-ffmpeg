<?php
/**
 *
 * User: develop
 * Date: 06.07.2018
 */

namespace somov\ffmpeg\events;

use somov\ffmpeg\process\FfmpegBaseProcess;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\Event;

class VideoInfoEvent extends Event
{
    /**
     * @var VideoInfoParser
     */
    public $info;

    /** @var  FfmpegBaseProcess */
    public $process;

}