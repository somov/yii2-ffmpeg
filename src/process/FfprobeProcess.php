<?php
/**
 *
 * User: develop
 * Date: 05.07.2018
 */

namespace somov\ffmpeg\process;


use somov\common\process\BaseProcess;
use somov\common\process\StringBuffered;
use somov\ffmpeg\process\parser\VideoInfoParser;
use yii\base\InvalidConfigException;

class FfprobeProcess extends BaseProcess
{
    use StringBuffered;


    public $outputParser = VideoInfoParser::class;

    /**
     * @var null|string
     */
    public $source = null;

    protected function initSource()
    {
        if (empty($this->source)) {
            throw new InvalidConfigException('Empty source');
        }

        $this->source = \Yii::getAlias($this->source);

        if (!file_exists($this->source)) {
            throw new InvalidConfigException('File not exists ' . $this->source);
        }
    }

    protected function prepareCommand()
    {
        $this->initSource();

        $this->addArgument('-v', 'quiet')
            ->addArgument('-print_format', 'json')
            ->addArgument('-show_format')
            ->addArgument('-show_streams', $this->source);

        return parent::prepareCommand();
    }


}