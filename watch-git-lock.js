#!/usr/bin/env node

var fs = require('fs');
var execSync = require('child_process').execSync;


var GIT_DIRECTORY = '.git';
var LOCK_FILE = 'index.lock';
var LOCK_FILE_REL_PATH = GIT_DIRECTORY+'/'+LOCK_FILE;
var AUDIO_FILE_REL_PATH = 'notify.ogg';
var SLEEP_DURATION_SEC = 1;


function start()
{
	var audioFilePath = __dirname+'/'+AUDIO_FILE_REL_PATH;
	watchDirectory(process.cwd(), function (fileExists) {
		notify(fileExists, audioFilePath);
	});
}


function watchDirectory(directory, onChange)
{
	var gitPath = directory+'/'+GIT_DIRECTORY;
	if (! (fs.existsSync(gitPath) && fs.lstatSync(gitPath).isDirectory())) {
		console.log(prepareMessage(MESSAGE_DIRECTORY_NOT_GIT_REPO, 'directory', directory));
		return;
	}

	var lockFilePath = directory+'/'+LOCK_FILE_REL_PATH;

	var fileExists = fs.existsSync(lockFilePath);
	console.log(getInitialMessage(fileExists));

	console.log(prepareMessage(MESSAGE_WATCHING_LOCK, 'lock', LOCK_FILE_REL_PATH));

	while (true) {
		execSync('sleep '+SLEEP_DURATION_SEC);

		var fileExisted = fileExists;
		fileExists = fs.existsSync(lockFilePath);
		if (fileExisted !== fileExists) {
			onChange(fileExists);
		}
	}
}


function getInitialMessage(fileExists)
{
	var message = fileExists ? MESSAGE_LOCK_EXIST : MESSAGE_LOCK_NOT_EXIST;
	return prepareMessage(message, 'lock', LOCK_FILE_REL_PATH);
}


function getMessage(fileExists)
{
	var message = fileExists ? MESSAGE_LOCK_ADDED : MESSAGE_LOCK_REMOVED;
	return prepareMessage(message, 'lock', LOCK_FILE_REL_PATH);
}


function notify(fileExists, audioFilePath)
{
	var message = getMessage(fileExists);
	console.log(message);
	showToast(message);
	playAudio(audioFilePath);
}


function showToast(message)
{
	execSync('notify-send watch-git-lock '+JSON.stringify(message));
}


function playAudio(audioFilePath)
{
	execSync('paplay '+JSON.stringify(audioFilePath));
}


function prepareMessage(string, search, replace)
{
	return string.replace('{'+search+'}', replace);
}


var MESSAGE_DIRECTORY_NOT_GIT_REPO = 'The directory "{directory}" is not a git repository.';
var MESSAGE_WATCHING_LOCK = 'Watching {lock}';
var MESSAGE_LOCK_EXIST = '{lock} exists.';
var MESSAGE_LOCK_NOT_EXIST = '{lock} does not exist.';
var MESSAGE_LOCK_ADDED = '{lock} has been added.';
var MESSAGE_LOCK_REMOVED = '{lock} has been removed.';


start();
