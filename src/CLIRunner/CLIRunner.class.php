<?php
declare(strict_types=1);

namespace APIcation\CLI;

// startup like normal app

use Nette\DI\Container;

class CRunner
{
    const lockFile = 'runner.lock';

    private Container $Container;

    function __construct(
      Container $Container
    )
    {
        $this->Container = $Container;
    }

    /**
     * Locks the runner from running multiple times at the same time
     * @throws \Exception If the runner is already locked
     */
    private function exec_lock(): void
    {
        if ($this->exec_is_locked()){
            throw new \Exception('Cannot lock the lock');
        }
        $f = @fopen( self::lockFile, 'w' );
        fwrite($f, '1');
        fclose($f);
    }

    /**
     * Unlocks the runner for further use
     * @return void
     */
    private function exec_unlock(): void
    {
        unlink(self::lockFile);
    }

    /**
     * @return bool Tells whether the runner is already locked
     */
    private function exec_is_locked(): bool
    {
        return file_exists(self::lockFile);
    }

    /**
     * Processes the CLI request
     *
     * @param array $argv Arguments from CLI
     *
     * @return void
     *
     * @throws \Throwable
     */
    public function run(array $argv)
    {
        if ($this->exec_is_locked()){
            throw new \Exception('CLI is already running');
        }
        // make sure the CLI is running only from the runcommand.php file
        $p = pathinfo($argv[ 0 ]);
        if ($p['basename'] !== 'runcommand.php') {
            throw new \Exception(sprintf('Not allowed to run from `%s`', $argv[0]), 403);
        }
        $this->exec_lock();

        $class = $argv[1] ?? '';
        try{
            if (!class_exists( $class )){
                throw new \Exception(sprintf('Class %s not found', $class), 404);
            }

            $service = $this->Container->createInstance( $argv[1] );
            $service->run( array_slice($argv, 2) );
        } catch(\Throwable $e){
            throw $e;
        } finally{
            // release the lock
            $this->exec_unlock();
        }
    }
}