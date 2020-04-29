<?php
/**
 *
 * User: develop
 * Date: 06.07.2018
 */

namespace somov\ffmpeg\events;

use somov\ffmpeg\process\FfmpegBaseProcess;
use somov\ffmpeg\process\parser\ConvertEndParser;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\ArrayAccessTrait;
use yii\base\Event;

/**
 * Class EndEvent
 * @package somov\ffmpeg\events
 */
class EndEvent extends Event implements \ArrayAccess
{
    use ArrayAccessTrait;


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

    /**
     * @var ConvertEndParser
     */
    public $parser;

    /**
     * @var FfmpegBaseProcess
     */
    public $process;


}