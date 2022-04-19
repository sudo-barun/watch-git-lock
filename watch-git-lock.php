#! /usr/bin/php
<?php

class Watcher
{
	protected $dir;
	protected $indexFilePathRel = '.git/index.lock';
	protected $indexFilePath;
	protected $sleepDuration = 1;

	public function __construct($dir)
	{
		$this->dir = $dir;
		$this->indexFilePath = $dir.'/'.$this->indexFilePathRel;
	}

	public function watch()
	{
		if (! is_dir($this->dir.'/.git')) {
			echo "The directory \"$this->dir\" is not a git repository.\n";
			return;
		}

		$fileExists = file_exists($this->indexFilePath);
		echo $this->getInitialMessage($fileExists);
		echo PHP_EOL;

		echo "Watching $this->indexFilePathRel";
		echo PHP_EOL;

		while (true) {
			sleep($this->sleepDuration);

			$fileExisted = $fileExists;
			$fileExists = file_exists($this->indexFilePath);
			if ($fileExisted xor $fileExists) {
				$this->notify($this->getMessage($fileExists));
			}
		}
	}

	protected function notify($msg)
	{
		echo $msg;
		echo PHP_EOL;
		exec('notify-send '.escapeshellarg($msg));
		exec('paplay ' . escapeshellarg(__DIR__.'/notify.ogg'));
	}

	protected function getInitialMessage($fileExists)
	{
		return $fileExists ? "$this->indexFilePathRel exists." : "$this->indexFilePathRel does not exist.";
	}

	protected function getMessage($fileExists)
	{
		return $fileExists ? "$this->indexFilePathRel has been added." : "$this->indexFilePathRel has been removed.";
	}
}

$watcher = new Watcher(getcwd());
$watcher->watch();
