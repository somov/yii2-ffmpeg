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

    private $_videoSize = 0;

    private $_audioSize = 0;

    private $_data;

    /**
     * @param mixed $data
     * @return $this
     */
    public function parse($data)
    {
        $this->_data = $data;

        if (preg_match('/progress=(?\'progress\'\w+)\svideo:(?\'videoSize\'\w+)\saudio:(?\'audioSize\'\w+)/m',
            $data, $m)) {
            $this->progress = $m['progress'];
            $this->_videoSize = (int)rtrim($m['videoSize'], 'kb');
            $this->_audioSize = (int)rtrim($m['audioSize'], 'kb');
        }
        return $this;
    }

    /**
     * @return mixed
     */
    public function getVideoSize()
    {
        return $this->_videoSize;
    }

    /**
     * @return mixed
     */
    public function getAudioSize()
    {
        return $this->_audioSize;
    }

    public function getSuccess()
    {
        return ($this->progress === 'end' && $this->_videoSize > 0 && $this->getAudioSize() > 0);
    }

    /**
     * @return mixed
     */
    public function getEndMessage()
    {
        return $this->_data;
    }




}