<?php
/**
 *
 * User: develop
 * Date: 05.07.2018
 */

namespace somov\ffmpeg\process\parser;


use Imagine\Image\Box;
use somov\common\interfaces\ParserInterface;
use somov\common\process\BaseProcess;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

class VideoInfoParser extends BaseObject implements ParserInterface
{

    /**
     * @var bool|array
     */
    protected $videoStream = false;

    /**
     * @var bool|array
     */
    protected $audioStream = false;

    /**
     * @var bool|array
     */
    protected $format = false;

    /**
     * @param mixed $data
     * @param BaseProcess $process
     * @return $this
     */
    public function parse($data, BaseProcess $process)
    {
        $a = Json::decode($data);

        if (empty($a)) {
            return $this;
        }

        $this->initStream('video', $a);
        $this->initStream('audio', $a);

        if (isset($a['format'])) {
            $this->format = $a['format'];
        }

        return $this;
    }

    private function initStream($type, array $a)
    {
        $key = array_search($type, ArrayHelper::getColumn($a['streams'], 'codec_type'));
        if ($key !== false) {
            $property = $type . 'Stream';
            if ($this->canSetProperty($property)) {
                $this->$property = $a['streams'][$key];
            }
        };
    }

    /**
     * @return array|bool
     */
    public function getVideoStream()
    {
        return $this->videoStream;
    }

    /**
     * @return array|bool
     */
    public function getAudioStream()
    {
        return $this->audioStream;
    }

    /**
     * @return array|bool
     */
    public function getFormat()
    {
        return $this->format;
    }


    /**
     * @param bool $short
     * @return mixed|string
     */
    public function getFormatName($short = false)
    {
        $attribute = ($short) ? 'format_name' : 'format_long_name';

        return (isset($this->format[$attribute])) ? $this->format[$attribute] : '';
    }


    /**
     * @return Box|null
     */
    public function getBox()
    {
        if (isset($this->videoStream)) {
            return new Box($this->videoStream['width'], $this->videoStream['height']);
        }
        return null;
    }

    /**
     * @return float
     */
    public function getDuration()
    {
        return (isset($this->format['duration'])) ? (float)$this->format['duration'] : 0;
    }

    /**
     * @param float $value
     */
    public function setDuration($value)
    {
        if (isset($this->format)) {
            $this->format['duration'] = $value;
        }
    }

    /**
     * @param string $format
     * @return false|string
     */
    public function getDurationTime($format = "H:i:s")
    {
        return gmdate($format, $this->getDuration());
    }


    /**
     * @return float
     */
    public function getBitRate()
    {
        return (isset($this->videoStream['bit_rate'])) ? (float)$this->videoStream['bit_rate'] : 0;
    }

    /**
     * @return int
     */
    public function getQuality()
    {
        if (isset($this->videoStream)) {
            return (integer)$this->videoStream['height'];
        }
        return 0;
    }

    /**
     * @return \DateTime|null
     */
    public function createDateTime()
    {
        if (isset($this->videoStream['tags']['creation_time'])) {
            return new \DateTime($this->videoStream['tags']['creation_time']);
        }

        return null;
    }

    /**
     * @return int
     */
    public function getFileSize()
    {
        return (isset($this->format['size'])) ? (int)$this->format['size'] : 0;

    }


    /**
     * @return bool|string
     */
    public function getFileName()
    {
        return (isset($this->format['filename'])) ? $this->format['filename'] : false;
    }

    /**
     * @return int
     */
    public function getFps()
    {
        foreach (['avg_frame_rate', 'r_frame_rate'] as $item) {
            if (isset($this->videoStream[$item])) {
                $parts = array_filter(explode('/', $this->videoStream[$item]));
                if (count($parts) === 2) {
                    list($a, $b) = $parts;
                    return (integer) round($a / (integer)$b, 0);
                }
            }
        }
        return 0;
    }

}