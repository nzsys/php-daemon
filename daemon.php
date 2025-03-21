#!/usr/bin/env php
<?php

declare(strict_types=1);

$pidFile = '/var/run/myphpdaemon.pid';
$logFile = '/var/log/myphpdaemon.log';
$cmd = $argv[1] ?? '';

match ($cmd) {
    'start' => start(),
    'stop' => stop(),
    'restart' => restart(),
    'status' => status(),
    default => usage(),
};

function isRunning(): bool
{
    global $pidFile;
    if (!file_exists($pidFile)) {
        return false;
    }

    $pid = (int) trim(file_get_contents($pidFile));
    return 0 < $pid && posix_kill($pid, 0);
}

function start(): void {
    global $pidFile;
    if (isRunning()) {
        logMessage("Daemon already running.");
        return;
    }

    logMessage("Starting daemon...");

    $pid = pcntl_fork();
    if ($pid === -1) {
        logMessage("Failed to fork process.");
        exit(1);
    }

    if ($pid !== 0) {
        logMessage("Started daemon with PID $pid.");
        return;
    }

    // main
    posix_setsid();
    file_put_contents($pidFile, getmypid());
    while (true) {
        logMessage("Daemon is running...");
        sleep(10); // An example of a task that runs periodically
    }
}

function stop(): bool {
    global $pidFile;

    if (!isRunning()) {
        logMessage("Daemon not running.");
        return true;
    }

    logMessage("Stopping daemon...");
    $pid = (int)trim(file_get_contents($pidFile));
    posix_kill($pid, SIGTERM);

    $timeout = 5;
    while (0 < $timeout) {
        if (!isRunning()) {
            logMessage("Daemon stopped.");
            unlink($pidFile);
            return true;
        }
        sleep(1);
        --$timeout;
    }

    logMessage("Error: Failed to stop daemon.");
    return false;
}

function restart(): void {
    logMessage("Restarting daemon...");
    if (stop()) {
        sleep(1);
        start();
    }
}

function status(): void {
    logMessage(isRunning()
        ? "Daemon is running."
        : "Daemon is not running.");
}

function usage(): void {
    logMessage("usage: php " . basename(__FILE__) . " {start|stop|restart|status}");
}

function logMessage(string $message): void {
    global $logFile;
    file_put_contents($logFile, date('[Y-m-d H:i:s]') . ' ' . $message . PHP_EOL, FILE_APPEND);
}
