<?php
/**
 *
 * User: develop
 * Date: 06.07.2018
 */

namespace somov\ffmpeg\events;

use somov\ffmpeg\process\parser\ConvertEndParser;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\Event;

class EndEvent extends Event
{

    /** @var  ConvertEndParser */
    public $result;

    /**
     * @var VideoInfoParser
     */
    public $source;

    /**
     * @var VideoInfoParser
     */
    public $destination;
}