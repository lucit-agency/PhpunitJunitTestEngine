<?php

namespace LCUtils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Deployer {

    static function Deploy( \App\Commands\Deploy $lcBuildCommand )
    {
        $lcBuildCommand->info("Starting Deployment Process");
       
        $process = new Process("cd /usr/local/bin/phpunitjunittestengine && git pull");

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }

        $lcBuildCommand->info("Deployment Complete");

    }

}

?>