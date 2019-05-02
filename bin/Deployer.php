<?php

namespace LCUtils;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * Customer Deployer class for use with Lucit LCUtils CLI Library
 */
class Deployer {

    static function Deploy( \App\Commands\Deploy $lcBuildCommand )
    {
        $lcBuildCommand->info("Starting Deployment Process");
        
        $lcBuildCommand->info("Deploying locally via git pull in /usr/local/bin/phpunitjunittestengine/");
        $process = new Process("cd /usr/local/bin/phpunitjunittestengine && git pull");

        try {
            $process->setTty(true);
            $process->mustRun(function ($type, $buffer) {
                echo $buffer;
            });
        } catch (ProcessFailedException $e) {
            echo $e->getMessage();
        }


        $lcBuildCommand->info("Publishing to github");
        $process = new Process("git push github master");

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