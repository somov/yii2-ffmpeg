<?php
/**
 * Created by PhpStorm.
 * User: develop
 * Date: 05.07.2018
 * Time: 23:25
 */

namespace somov\ffmpeg\process\parser;


use somov\common\interfaces\ParserInterface;
use yii\base\BaseObject;

/**
 * @property int videoSize
 * @property int audioSize
 * @property int success
 * Class ConvertEndParser
 */
class ConvertEndParser extends BaseObject implements ParserInterface
{

    protected $progress = '';

    private $_data;


    /**
     * @param mixed $data
     * @return $this
     */
    public function parse($data)
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