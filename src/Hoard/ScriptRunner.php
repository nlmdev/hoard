<?php

namespace Hoard;

class ScriptRunner
{

    /**
     * The path to the script to run. Set in the constructor.
     * @var string
     */
    protected $filePath;

    /**
     * The targets loaded from the script.
     */
    protected $targets = null;

    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    protected function loadFile()
    {
        $this->targets = array();
        $lines = file($this->filePath);
        $currentTarget = "all";

        foreach($lines as $line) {
            // trim comments
            if(strpos($line, '#') !== false) {
                $line = substr($line, 0, strpos($line, '#'));
            }

            // skip blank lines
            if(trim($line) == '') {
                continue;
            }

            // target
            if(preg_match('/^[^\s]+/', $line)) {
                list($targetName, $targetDeps) = explode(':', $line);
                $currentTarget = trim($targetName);
                $deps = preg_split('/\s+/', trim($targetDeps));
                if(trim($targetDeps) == '') {
                    $deps = array();
                }

                $this->targets[$currentTarget] = array(
                    'deps' => $deps,
                    'commands' => array()
                );
                continue;
            }

            // command
            $this->targets[$currentTarget]['commands'][] = trim($line);
        }
    }

    public function drop(array $args)
    {
        foreach($args as $arg) {
            if(strpos($arg, ':') === false) {
                $pool = CacheManager::getPool($arg);
                $pool->clear();
            }
            else {
                list($poolName, $keys) = explode(':', $arg);
                $pool = CacheManager::getPool($poolName);
                $keys = preg_split('/\s*,\s*/', $keys);
                foreach($keys as $key) {
                    $item = $pool->getItem($key);
                    $item->delete();
                }
            }
        }
    }

    public function run($target)
    {
        if(null === $this->targets) {
            $this->loadFile();
        }

        // run dependencies
        foreach($this->targets[$target]['deps'] as $dep) {
            $this->run($dep);
        }

        // run commands
        echo "Running target '{$target}'\n";
        if(count($this->targets[$target]['commands']) > 0) {
            foreach($this->targets[$target]['commands'] as $command) {
                $parts = preg_split('/\s+/', $command);
                echo "  $command... ";
                $this->{$parts[0]}(array_slice($parts, 1));
                echo "Done\n";
            }
        }
        else {
            echo "  Nothing to do.\n";
        }
        echo "Success.\n";
    }

}

