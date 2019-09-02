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
     * @return double
     */
    public function getStartTimeSeconds()
    {
        if (empty($this->process)) {
            return 0;
        }
        return ArrayHelper::getValue($this->process->getActionParams(), 'addArguments.-ss', 0);
    }

    /**
     * @return double
     */
    public function getEndTimeSeconds()
    {
        if (empty($this->process)) {
            return (int)$this->info->getDuration();
        }

        return ArrayHelper::getValue($this->process->getActionParams(), 'addArguments.-t',
            (int)$this->info->getDuration());
    }


    /**
     * @return string
     */
    public function processingTime()
    {
        return gmdate('H:i:s', $this->getStartTimeSeconds() + $this->getTimeSeconds());
    }


    /**
     * @return string
     */
    public function processingTimeEnd()
    {
        return gmdate('H:i:s', $this->getEndTimeSeconds() + $this->getStartTimeSeconds());
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        $passed = $this->getTimeSeconds();

        $total = $this->getEndTimeSeconds();

        if ($passed > $total) {
            $passed = $total;
        }

        if ($total > 0) {
            return (int)round($passed * 100 / $total);
        }

        return -1;

    }

}