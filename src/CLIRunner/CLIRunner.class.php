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

    private function exec_lock(): void
    {
        if ($this->exec_is_locked()){
            throw new \Exception('Tralala');
        }
        $f = @fopen( self::lockFile, 'w' );
        fwrite($f, '1');
        fclose($f);
    }

    private function exec_unlock(): void
    {
        unlink(self::lockFile);
    }

    private function exec_is_locked(): bool
    {
        return file_exists(self::lockFile);
    }

    public function run(array $argv)
    {
        if ($this->exec_is_locked()){
            throw new \Exception('Cannot run actions in parallel (yet)');
        }
        // get filename
        $p = pathinfo($argv[ 0 ]);
        if ($p['basename'] !== 'runcommand.php') {
            throw new \Exception('Not allowed to run everywhere' . $argv[0], 403);
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