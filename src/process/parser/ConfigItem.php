<?php
/**
 *
 * User: develop
 * Date: 04.07.2018
 */

namespace somov\ffmpeg\process\parser;


use yii\base\BaseObject;

/**
 * @property string description
 * @property string name
 * @property bool muxind
 * @property bool deMuxind
 *
 */
class ConfigItem extends BaseObject
{

    const MODE_MUXING = 'E';
    const MODE_DEMUXING = 'D';

    const MODE_DECODING_SUPPORTED = 'D';
    const MODE_ENCODING_SUPPORTED = 'E';
    const MODE_VIDEO_CODEC = 'V';
    const MODE_AUDIO_CODEC = 'A';
    const MODE_SUBTITLE_CODEC = 'S';
    const MODE_INTRA_FRAME = 'I';
    const MODE_LOSSY_COMPRESSION = 'L';
    const MODE_LOSSLESS_COMPRESSION = 'C';

    private $_mode;

    private $_name;

    private $_description;

    /**
     * FormatItem constructor.
     * @param string $mode
     * @param string $name
     * @param string $description
     */
    public function __construct($mode, $name, $description)
    {
        $this->_name = trim($name);
        $this->_mode = trim($mode);
        $this->_mode = preg_replace('/S$/', 'C', $this->_mode);
        $this->_description = trim($description);
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @param string $mode
     * @return bool
     */
    private function hasMode($mode)
    {
        return strpos($this->_mode, $mode) !== false;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->_description;
    }


    /**
     * @return bool
     */
    public function getMuxing()
    {
        return $this->hasMode(self::MODE_MUXING);
    }

    /**
     * @return bool
     */
    public function getDeMuxing()
    {
        return $this->hasMode(self::MODE_DEMUXING);
    }

    public function getDecodingSupported()
    {
        return $this->hasMode(self::MODE_DECODING_SUPPORTED);
    }

    public function getEncodingSupported()
    {
        return $this->hasMode(self::MODE_ENCODING_SUPPORTED);
    }

    public function getVideoCodec()
    {
        return $this->hasMode(self::MODE_VIDEO_CODEC);
    }

    public function getAudioCodec()
    {
        return $this->hasMode(self::MODE_AUDIO_CODEC);
    }

    public function getSubTitleCodec()
    {
        return $this->hasMode(self::MODE_SUBTITLE_CODEC);
    }

    public function getIntraFrame()
    {
        return $this->hasMode(self::MODE_INTRA_FRAME);
    }

    public function getLossyCompression()
    {
        return $this->hasMode(self::MODE_LOSSY_COMPRESSION);
    }

    public function getLossLessCompression()
    {
        return $this->hasMode(self::MODE_LOSSLESS_COMPRESSION);
    }


}