<?php

namespace SugoiCloud\Common;

use Throwable;

/**
 * Debugging utilities.
 */
class Debug
{
    protected static array $timers = [];

    /**
     * Dumps the data to the console and stops execution.
     *
     * @param $data
     * @return never
     */
    public static function dd($data): never
    {
        self::dump($data);
        die;
    }

    /**
     * Dumps the data to the console.
     *
     * @param $data
     * @return void
     */
    public static function dump($data): void
    {
        echo print_r($data, true) . PHP_EOL;
    }

    /**
     * Output a message to the console.
     *
     * @param $message
     * @return void
     */
    public static function print($message): void
    {
        echo print_r($message, true) . PHP_EOL;
    }

    /**
     * Prints a trace of the current execution.
     *
     * @return void
     */
    public static function printTrace(): void
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        array_shift($trace);
        self::print($trace);
    }

    /**
     * Print the backtrace.
     *
     * @return void
     */
    public static function printBackTrace(): void
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        self::print($backtrace);
    }

    /**
     * Print the current memory usage of the application.
     *
     * @param bool $real
     * @return void
     */
    public static function printMemoryUsage(bool $real = false): void
    {
        self::print(
            "Memory usage: " . self::formatBytes(memory_get_usage($real))
            . ", " . self::formatBytes(memory_get_peak_usage($real))
        );
    }

    /**
     * Prints an exception.
     *
     * @param Throwable $exception
     * @return void
     */
    public static function exception(Throwable $exception): void
    {
        self::print("Exception: " . $exception->getMessage());
        self::print("Stack trace: " . $exception->getTraceAsString());
    }

    /**
     * Starts a named timer, if a timer with the same name is already running
     * the elapsed time will be printed instead.
     *
     * @param string $label
     * @return void
     */
    public static function timer(string $label): void
    {
        if (!isset(self::$timers[$label])) {
            self::$timers[$label] = microtime(true);
            self::print("[timer:$label]: started");
        } else {
            $duration = (microtime(true) - self::$timers[$label]) * 1000;
            self::print("[timer:$label]: {$duration}ms");
        }
    }

    /**
     * Stops a named timer and prints the elapsed time.
     *
     * @param $label
     * @return void
     */
    public static function timerStop($label): void
    {
        if (!isset(self::$timers[$label])) {
            self::print("[timer:$label]: does not exist");
            return;
        }

        $duration = (microtime(true) - self::$timers[$label]) * 1000;
        unset(self::$timers[$label]);
        self::print("[timer:$label]: {$duration}ms (stopped)");
    }

    /**
     * Measures the execution time of the callable.
     *
     * @param callable $callback
     * @param string $label
     * @return mixed
     */
    public static function measure(callable $callback, string $label = 'measure'): mixed
    {
        self::timer($label);
        $result = $callback();
        self::timerStop($label);
        return $result;
    }

    /**
     * Manually trigger garbage collection.
     *
     * @return void
     */
    public static function gc(): void
    {
        gc_collect_cycles();
        self::print("Garbage collection started");
    }

    protected static function formatBytes($bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return number_format($bytes / pow(1024, $power), 2) . ' ' . $units[$power];
    }
}