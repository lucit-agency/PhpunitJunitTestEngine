<?php

namespace LCUtils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Builder {

    static function Build( \App\Commands\Build $lcBuildCommand )
    {
        $destination = "builds/PhpunitJunitTestEngine.zip";

        $lcBuildCommand->info("Starting Build Process to : ".$destination);

        //git archive -o builds/lucitwp_theme.zip HEAD
        $wc = $lcBuildCommand->getWorkingCopy();
        $wc->archive("HEAD", ["o" => $destination] );

        $lcBuildCommand->info("Build Complete");
    }

}

?>