#!/usr/bin/env php
<?php

namespace Watch_Git_Lock;


const GIT_DIRECTORY = '.git';
const LOCK_FILE = 'index.lock';
const LOCK_FILE_REL_PATH = GIT_DIRECTORY.'/'.LOCK_FILE;
const AUDIO_FILE_REL_PATH = 'notify.ogg';
const SLEEP_DURATION_SEC = 1;


function start()
{
	$audio_file_path = __DIR__.'/'.AUDIO_FILE_REL_PATH;
	watch_directory(getcwd(), function (bool $file_exists) use ($audio_file_path) {
		notify($file_exists, $audio_file_path);
	});
};


function watch_directory(string $directory, callable $on_change)
{
	if (! is_dir($directory.'/'.GIT_DIRECTORY)) {
		echo prepare_message(MESSAGE_DIRECTORY_NOT_GIT_REPO, 'directory', $directory)."\n";
		return;
	}

	$lock_file_path = $directory.'/'.LOCK_FILE_REL_PATH;

	$file_exists = file_exists($lock_file_path);
	echo get_initial_message($file_exists)."\n";
	echo prepare_message(MESSAGE_WATCHING_LOCK, 'lock', LOCK_FILE_REL_PATH)."\n";

	while (true) {
		sleep(SLEEP_DURATION_SEC);

		$file_existed = $file_exists;
		$file_exists = file_exists($lock_file_path);
		if ($file_existed !== $file_exists) {
			$on_change($file_exists);
		}
	}
}


function get_initial_message(bool $file_exists): string
{
	return prepare_message(
		$file_exists ? MESSAGE_LOCK_EXIST : MESSAGE_LOCK_NOT_EXIST,
		'lock',
		LOCK_FILE_REL_PATH
	);
}


function get_message(bool $file_exists): string
{
	return prepare_message(
		$file_exists ? MESSAGE_LOCK_ADDED : MESSAGE_LOCK_REMOVED,
		'lock',
		LOCK_FILE_REL_PATH
	);
}


function notify(bool $file_exists, $audio_file_path)
{
	$message = get_message($file_exists);
	echo $message."\n";
	show_toast($message);
	play_audio($audio_file_path);
}


function show_toast(string $message)
{
	exec('notify-send watch-git-lock '.escapeshellarg($message));
}


function play_audio(string $audio_file_path)
{
	exec('paplay '.escapeshellarg($audio_file_path));
}


function prepare_message(string $string, string $search, string $replace): string
{
	return str_replace(sprintf('{%s}', $search), $replace, $string);
}


const MESSAGE_DIRECTORY_NOT_GIT_REPO = 'The directory "{directory}" is not a git repository.';
const MESSAGE_WATCHING_LOCK = 'Watching {lock}';
const MESSAGE_LOCK_EXIST = '{lock} exists.';
const MESSAGE_LOCK_NOT_EXIST = '{lock} does not exist.';
const MESSAGE_LOCK_ADDED = '{lock} has been added.';
const MESSAGE_LOCK_REMOVED = '{lock} has been removed.';


start();
