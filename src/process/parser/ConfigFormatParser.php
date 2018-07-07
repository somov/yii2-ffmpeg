<?php
/**
 *
 * User: develop
 * Date: 04.07.2018
 */

namespace somov\ffmpeg\process\parser;


class ConfigFormatParser extends ConfigParser
{
    protected $rawPattern = '/File formats:.*?--(.*?)--end--/ms';

    protected $fieldsPattern = '/^(?\'mode\'.{4})(?\'name\'.{16})(?\'description\'.*?)$/m';

    protected $listAttributes = [
        'muxind', 'deMuxind'
    ];

}