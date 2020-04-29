<?php
/**
 * Created by PhpStorm.
 * User: web
 * Date: 28.04.20
 * Time: 13:32
 */

namespace somov\ffmpeg\events;


use somov\common\helpers\ArrayHelper;
use somov\ffmpeg\process\parser\VideoInfoParser;

/**
 * Class EndEventMultiple
 * @package somov\ffmpeg\events
 */
class EndEventMultiple extends EndEvent
{
    /**
     * @var VideoInfoParser[]
     */
    public $destination = [];

    /**
     * @var VideoInfoParser[]
     */
    public $source = [];


    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->normalizeResult();

        parent::init();
    }

    /**
     *
     */
    private function normalizeResult()
    {
        foreach (['source', 'destination'] as $property) {
            $this->{$property} = [];
            foreach (ArrayHelper::getValue($this->process, $property, []) as $file) {
                array_push($this->{$property}, $this->process->getFfmpeg()->getVideoInfo($file));
            }
        }
    }

}