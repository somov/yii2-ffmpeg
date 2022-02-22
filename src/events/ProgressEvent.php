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
use yii\helpers\ArrayHelper;

/**
 *
 * @property int $size;
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
     * @var FfmpegBaseProcess
     */
    public $process;

    /**
     * @var string
     */
    private $_time_ms;

    /**
     * @var string
     *
     */
    private $_size;

    /**
     * @var string
     */
    private $_bitrate;

    /**
     * @var string
     */
    private $_speed;

    /**
     * @var string
     */
    private $_state;


    /**
     * ProgressEvent constructor.
     * @param array $raw
     * @param array $config
     */
    public function __construct(array $raw, array $config = [])
    {
        foreach (['time_ms', 'bitrate', 'size', 'speed', 'state'] as $property) {
            $this->{'_' . $property} = $raw[$property];
        }
        parent::__construct($config);
    }


    /**
     * @return string
     */
    public function getTime()
    {
        return gmdate("H:i:s", $this->getTimeSeconds());
    }

    /**
     * @return integer
     */
    public function getSize()
    {
        return (int)$this->_size;
    }

    /**
     * @return float
     */
    public function getBitrate()
    {
        return (float)$this->_bitrate;
    }


    /**
     * @return false|int
     */
    public function getTimeSeconds()
    {
        return (isset($this->_time_ms)) ? (integer)$this->_time_ms / 1000000 : 0;
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
     * @param integer $seconds
     * @return string
     */
    protected function toTime($seconds)
    {
        return gmdate($seconds > 3599 ? 'H:i:s' : 'i:s', $seconds);
    }

    /**
     * @return string
     */
    public function processingTime()
    {
        return $this->toTime($this->getStartTimeSeconds() + $this->getTimeSeconds());
    }


    /**
     * @return string
     */
    public function processingTimeEnd()
    {
        return $this->toTime($this->getEndTimeSeconds() + $this->getStartTimeSeconds());
    }

    /**
     * @return float
     */
    public function getProgress()
    {
        if ($this->isEnd()) {
            return 100;
        }

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

    /**
     * Прогнозируемое время
     * @return int
     */
    public function predictedSeconds()
    {
        if ($speed = floatval($this->getSpeed())) {
            return round($this->getEndTimeSeconds() / floatval($this->getSpeed()));
        }
        return 0;
    }

    /**
     * @return string
     */
    public function predictedTime()
    {
        return $this->toTime($this->predictedSeconds());
    }


    /** Оставшееся время
     * @return int
     */
    public function getRemainingSeconds()
    {
        $time = -(time() - ($this->process->getExecutingStartTime() + $this->predictedSeconds()));

        if ($time >= 0) {
            return $time;
        }

        return 0;
    }

    /**
     * @return string
     */
    public function getRemainingTime()
    {
        return $this->toTime($this->getRemainingSeconds());
    }

    /**
     * @return int
     */
    public function getExecutionSeconds()
    {
        return time() - $this->process->getExecutingStartTime();
    }

    /**
     * @return string
     */
    public function getExecutionTime()
    {
        return $this->toTime($this->getExecutionSeconds());
    }

    /**
     * @return string
     */
    public function getSpeed()
    {
        return $this->_speed;
    }

    /**
     * @return float
     */
    public function getState()
    {
        return $this->_state;
    }

    /**
     * @return bool
     */
    public function isEnd()
    {
        return $this->getState() === 'end';
    }

    /**
     * @return bool
     */
    public function isRunning()
    {
        return !$this->isEnd();
    }

}