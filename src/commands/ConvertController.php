<?php

namespace somov\ffmpeg\commands;

use somov\ffmpeg\components\Ffmpeg;
use somov\ffmpeg\events\EndEvent;
use somov\ffmpeg\events\ProgressEvent;
use somov\ffmpeg\events\VideoInfoEvent;
use yii\helpers\Console;


class ConvertController extends \yii\console\Controller
{

    /**\
     * @param string $source
     * @param string $destination
     * @param string $format
     */
    public function actionIndex($source, $destination, $format = null)
    {

        /** @var Ffmpeg $ffmpeg */
        $ffmpeg = \Yii::createObject([
            'class' => Ffmpeg::class,
            //path to ffmpeg bin,
            //'ffmpegPath' => '/usr/local/bin',
            'on actionBegin' => function ($event) {
                /** @var  VideoInfoEvent $event */
                Console::output('Start convert ' . $event->info->getFileName());
                Console::output($event->info->getFormatName());
                Console::output('Duration:  ' . $event->info->getDurationTime());
                Console::output('File size ' . \Yii::$app->formatter->asSize($event->info->getFileSize()));
                Console::startProgress(0, 100);
            },
            'on progress' => function ($event) {
                /** @var ProgressEvent $event */
                Console::updateProgress($event->getProgress(), 100, $event->getTime());
            },
            'on actionEnd' => function ($event) {
                /**@var EndEvent $event */
                Console::endProgress(true, false);
                Console::output('Done ' . $event->destination->getFileName());
                Console::output($event->destination->getFormatName());
                Console::output('File size ' . \Yii::$app->formatter->asSize($event->destination->getFileSize()));
            }
        ]);

        try {
            $ffmpeg->convert($source, $destination, $format);
        } catch (\Exception $exception) {
            $this->stdout('Error convert ' . $source .
                ' to format ' . $format . ' ' . $exception->getMessage() . "\n",
                Console::FG_RED);
        }

    }

}