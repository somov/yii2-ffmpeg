<?php
/**
 *
 * User: develop
 * Date: 06.07.2018
 */

namespace somov\ffmpeg\events;

use somov\common\process\BaseProcess;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\Event;
use yii\helpers\ArrayHelper;

/**
 *
 * @property int $size;
 * @property int $fps
 * @property float $bitrate
 *
 *
 *
 * Class ProgressEvent
 * @package somov\ffmpeg\components\events
 */
class ProgressEvent extends Event
{
    /**
     * @var VideoInfoParser
     */
    public $info;

    /**
     * @var BaseProcess
     */
    public $process;

    /**
     * @var string
     */
    protected $time;

    /**
     * @var string
     *
     */
    protected $size;

    /**
     * @var string
     */
    protected $bitrate;

    /**
     * @var string
     */
    protected $frame;

    /**
     * @var string
     */
    protected $fps;

    /**
     * @param $raw
     * @return $this
     */
    public function setRaw($raw)
    {
        $raw = ArrayHelper::filter($raw, ['size', 'time', 'frame', 'bitrate', 'fps']);

        foreach (array_map('trim', $raw) as $property => $value) {
            $this->$property = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return (int)$this->size;
    }

    /**
     * @return string
     */
    public function getBitrate()
    {
        return (float)$this->bitrate;
    }

    /**
     * @return string
     */
    public function getFrame()
    {
        return (integer)$this->frame;
    }

    /**
     * @return string
     */
    public function getFps()
    {
        return (integer)$this->fps;
    }

    /**
     * @return false|int
     */
    public function getTimeSeconds()
    {
        $formattedTime = (strlen($this->time) <= 5) ? '00:' . $this->time : $this->time;
        return strtotime('1970-01-01 ' . $formattedTime . 'GMT');
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        $passed = $this->getTimeSeconds();
        $total = (int)$this->info->getDuration();
        return (int)round($passed * 100 / $total);
    }
}