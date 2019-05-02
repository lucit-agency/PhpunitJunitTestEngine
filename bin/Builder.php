<?php

namespace LCUtils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Customer Builder class for use with Lucit LCUtils CLI Library
 */
class Builder {

    static function Build( \App\Commands\Build $lcBuildCommand )
    {
        $destination = "builds/PhpunitJunitTestEngine.zip";

        $lcBuildCommand->info("Starting Build Process to : ".$destination);

        $wc = $lcBuildCommand->getWorkingCopy();
        $wc->archive("HEAD", ["o" => $destination] );

        $lcBuildCommand->info("Build Complete");
    }

}

?>