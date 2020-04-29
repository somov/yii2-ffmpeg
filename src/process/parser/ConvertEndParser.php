<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 05.07.2018
 * Time: 23:25
 */

namespace somov\ffmpeg\process\parser;


use somov\common\helpers\ArrayHelper;
use somov\common\interfaces\ParserInterface;
use somov\common\process\BaseProcess;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\process\FfmpegBaseProcess;
use yii\base\BaseObject;

/**
 * @property int videoSize
 * @property int audioSize
 * @property int success
 * Class ConvertEndParser
 */
class ConvertEndParser extends BaseObject implements ParserInterface
{

    /**
     * @var string
     */
    protected $progress = '';

    /**
     * @var string
     */
    private $_data;

    /**
     * @var string|array|callable
     */
    public $event = EndEvent::class;


    /**
     * @param VideoInfoParser $source
     * @param FfmpegBaseProcess $process
     * @return EndEvent|object
     */
    public function createEvent($source, FfmpegBaseProcess $process)
    {

        $options = [
            'result' => $this,
            'source' => $source,
            'parser' => $this,
            'process' => $process
        ];

        if (is_string($this->event)) {
            $options['class'] = $this->event;
        } else {
            $options = ArrayHelper::merge($this->event, $options);
        }

        if ($destination = ArrayHelper::getValue($process->getActionParams(), 'destination')) {
            $options['destination'] = $process->ffmpeg->getVideoInfo($destination);
        }

        return \Yii::createObject($options);

    }

    /**
     * @param mixed $data
     * @param BaseProcess $process
     * @return $this
     */
    public function parse($data, BaseProcess $process)
    {
        $this->_data = $data;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoSize()
    {
        return 0;
    }

    /**
     * @return mixed
     */
    public function getAudioSize()
    {
        return 0;
    }

    public function getSuccess()
    {
        return $this->getEndMessage() === 'progress=end';
    }

    /**
     * @return mixed
     */
    public function getEndMessage()
    {
        $raw = explode("\n", $this->getBuffer());
        if (count($raw) > 0) {
            $raw = array_filter($raw, 'trim');
            return end($raw);
        }

        return $this->getBuffer();
    }


    public function getBuffer()
    {
        return $this->_data;
    }

    /**
     * return float
     */
    public function getEndDuration()
    {
        if (preg_match('/out_time_ms=(\d+)/', $this->getBuffer(), $m)) {
            return $m[1] / 1000000;
        }
        return 0;
    }
}