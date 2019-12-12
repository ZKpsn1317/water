<?php
namespace app\common\tool;
use PHPZip\Zip\Stream\ZipStream;

class Zip
{
    public static function dir($dir)
    {
        ob_start();
        $zip = new ZipStream(time().".zip");


        $zip->addDirectoryContent($dir,"fiels");

        $zip->finalize();

        if (ob_get_length() > 0) {
            ob_end_flush();
        }
    }
}