<?php
/**
 *
 * User: develop
 * Date: 03.07.2018
 */

namespace somov\ffmpeg\process;


use somov\common\interfaces\ParserInterface;
use somov\common\process\ArrayBuffered;
use somov\common\process\BaseProcess;
use somov\common\traits\ContainerCompositions;
use somov\ffmpeg\process\parser\ConfigCodecParser;
use somov\ffmpeg\process\parser\ConfigFormatParser;
use somov\ffmpeg\process\parser\ConfigItem;

class FfmpegVersionProcess extends BaseProcess implements ParserInterface
{
    use ArrayBuffered, ContainerCompositions;

    const GROUP_CODECS = 'codecs';

    const GROUP_FORMATS = 'formats';

    public $isGetFormats = true;

    public $isGetCodecs = false;


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->outputParser = $this;
        parent::init();
    }

    /**
     * @return string
     */
    protected function prepareCommand()
    {
        $this->addArgument('-version');
        $command = $this->joinCommandAndArguments();

        if ($this->isGetFormats) {
            $this->addArgument('-formats');
            $command .= ' && ' . $this->joinCommandAndArguments();
            $command .= ' && echo --end--';
        }

        if ($this->isGetCodecs) {
            $this->addArgument('-codecs');
            $command .= ' && ' . $this->joinCommandAndArguments();
        }

        return $command;
    }

    /**
     * @param array|object $data
     * @return $this
     */
    public function parse($data)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        preg_match('/^(.*?)\s{3,}/', $this->_buffer[0], $m);
        return $m[1];
    }

    /**
     * @return ConfigFormatParser
     */
    protected function getFormats()
    {
        return $this->getComposition(ConfigFormatParser::class, ['data' => $this->_buffer]);
    }

    /**
     * @return ConfigCodecParser
     */
    protected function getCodecs()
    {
        return $this->getComposition(ConfigCodecParser::class, ['data' => $this->_buffer]);
    }

    /**
     * @return array
     */
    public function getFormatsArray()
    {
        if (!$this->isGetFormats) {
            return [];
        }
        return $this->getFormats()->getListArray();
    }

    /**
     * @param $name
     * @return bool
     */
    public function formatExists($name)
    {
        if (!$this->isGetFormats) {
            return false;
        }
        return $this->getFormats()->itemExists($name);
    }

    /**
     * @return array
     */
    public function getCodecsArray()
    {
        if (!$this->isGetCodecs) {
            return [];
        }
        return $this->getCodecs()->getListArray();
    }


    /**
     * @param $name
     * @return bool
     */
    public function codecExists($name)
    {
        if (!$this->isGetCodecs) {
            return false;
        }
        return $this->getCodecs()->itemExists($name);
    }


    /**
     * @param string $group
     * @param $name
     * @return ConfigItem
     */
    public function getConfigItem($name, $group = self::GROUP_CODECS)
    {
        if (!$this->hasProperty($group)) {
            return null;
        }

        if ($group === self::GROUP_CODECS && !$this->isGetCodecs) {
            return null;
        }

        if ($group === self::GROUP_FORMATS && !$this->isGetFormats) {
            return null;
        }

        /** @var ConfigFormatParser|ConfigCodecParser $parser */
        $parser = $this->$group;
        return $parser->findItem($name);
    }

}