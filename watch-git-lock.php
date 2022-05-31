#!/usr/bin/env php
<?php

class Watcher
{
	protected $dir;
	protected $indexFilePathRel = '.git/index.lock';
	protected $indexFilePath;
	protected $sleepDuration = 1;
	protected $audioFilePath = __DIR__.'/notify.ogg';

	public function __construct($dir)
	{
		$this->dir = $dir;
		$this->indexFilePath = $dir.'/'.$this->indexFilePathRel;
	}

	public function watch()
	{
		if (! is_dir($this->dir.'/.git')) {
			echo 'The directory "'.$this->dir.'" is not a git repository.'."\n";
			return;
		}

		$fileExists = file_exists($this->indexFilePath);
		echo $this->getInitialMessage($fileExists)."\n";
		echo 'Watching '.$this->indexFilePathRel."\n";

		while (true) {
			sleep($this->sleepDuration);

			$fileExisted = $fileExists;
			$fileExists = file_exists($this->indexFilePath);
			if ($fileExisted xor $fileExists) {
				$this->notify($this->getMessage($fileExists));
			}
		}
	}

	protected function getInitialMessage($fileExists)
	{
		return $fileExists
			? $this->indexFilePathRel.' exists.'
			: $this->indexFilePathRel.' does not exist.';
	}

	protected function getMessage($fileExists)
	{
		return $fileExists
			? $this->indexFilePathRel.' has been added.'
			: $this->indexFilePathRel.' has been removed.';
	}

	protected function notify($msg)
	{
		echo $msg."\n";
		$this->showToast($msg);
		$this->playAudio();
	}

	protected function showToast($msg)
	{
		exec('notify-send watch-git-lock '.escapeshellarg($msg));
	}

	protected function playAudio()
	{
		exec('paplay ' . escapeshellarg($this->audioFilePath));
	}
}

(new Watcher(getcwd()))->watch();
