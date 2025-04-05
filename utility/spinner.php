<?php

namespace App\Utility;

class Spinner {
    private $message;
    private $running = false;
    private $pid;
    private $startTime;
    private $endTime;
    private $duration;
    private $exited;

    public function __construct($message = 'Processing: ', bool $exited = true) {
        $this->message = $message;
        $this->exited = $exited;
    }

    public static function loading(int $seconds = 10, string $message = 'Processing: ', bool $isEcho = true) {
        $spinner = new self($message);
        $spinner->start();
        sleep($seconds);
        $spinner->stop($isEcho);
        self::doExit();
        return $spinner;
    }

    public function start() {
        $this->running = true;
        $this->pid = pcntl_fork();
        $this->startTime = microtime(true);

        if ($this->pid == -1) {
            die("Could not fork process");
        }

        if ($this->pid == 0) { // Child process
            $spinner = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
            $i = 0;

            while ($this->running) {
                echo "\r {$this->message} {$spinner[$i]}     ";
                $i = ($i + 1) % count($spinner);
                usleep(100000); // Sleep for 100ms
            }
            exit();
        }
        return $this;
    }

    public function stop(bool $isEcho = true) {
        $this->running = false;
        if ($this->pid) {
            posix_kill($this->pid, SIGTERM);
            pcntl_wait($status); // Prevent zombie process
        }
        $this->endTime = microtime(true);
        $this->duration = $this->endTime - $this->startTime;
        $this->getDuration($isEcho);
        $this->doExit();
        return $this;
    }

    public function getDuration(bool $isEcho) {
        if($isEcho) echo "\n\n--------------* Time taken: " . number_format($this->duration * 1000, 3) . " milliseconds *---------------\n";     
        return $this->duration;
    }

    public function doExit() {
        if($this->exited) {
            exit("--------------* Programme Successfully Executed *--------------\n");
        }

        echo "\nDo you want to exit? (y/n): ";
        $input = trim(fgets(STDIN));
        if($input == 'y') {
            exit("--------------* Programme Exited *--------------\n");
        }
    }
}