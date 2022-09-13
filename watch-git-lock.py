#!/usr/bin/env python

from os import getcwd, path
from time import sleep
import subprocess


GIT_DIRECTORY = '.git'
LOCK_FILE = 'index.lock'
LOCK_FILE_REL_PATH = GIT_DIRECTORY+'/'+LOCK_FILE
AUDIO_FILE_REL_PATH = 'notify.ogg'
SLEEP_DURATION_SEC = 1


def start():
	audio_file_path = path.dirname(path.realpath(__file__))+'/'+AUDIO_FILE_REL_PATH
	watch_directory(getcwd(), lambda exists: notify(exists, audio_file_path))


def watch_directory(directory, on_change):

	if not path.isdir(directory+'/'+GIT_DIRECTORY):
		print(prepare_message(MESSAGE_DIRECTORY_NOT_GIT_REPO, 'directory', directory))
		return

	lock_file_path = directory+'/'+LOCK_FILE_REL_PATH;

	file_exists = path.isfile(lock_file_path)
	print(get_initial_message(file_exists))
	print(prepare_message(MESSAGE_WATCHING_LOCK, 'lock', LOCK_FILE_REL_PATH))

	while True:
		sleep(SLEEP_DURATION_SEC)
		file_existed = file_exists
		file_exists = path.isfile(lock_file_path)
		if file_existed != file_exists:
			on_change(file_exists)

def get_initial_message(file_exists):
	message = (MESSAGE_LOCK_EXIST if file_exists else MESSAGE_LOCK_NOT_EXIST)
	return prepare_message(message, 'lock', LOCK_FILE_REL_PATH)


def get_message(file_exists):
	message = (MESSAGE_LOCK_ADDED if file_exists else MESSAGE_LOCK_REMOVED)
	return prepare_message(message, 'lock', LOCK_FILE_REL_PATH)


def notify(file_exists, audio_file_path):
	message = get_message(file_exists)
	print(message)
	show_toast(message)
	play_audio(audio_file_path)


def show_toast(message):
	subprocess.Popen(['notify-send', 'watch-git-lock', message])


def play_audio(audio_file_path):
	subprocess.Popen(['paplay', audio_file_path])


def prepare_message(string, search, replace):
	return string.replace('{'+search+'}', replace)


MESSAGE_DIRECTORY_NOT_GIT_REPO = 'The directory "{directory}" is not a git repository.'
MESSAGE_WATCHING_LOCK = 'Watching {lock}'
MESSAGE_LOCK_EXIST = '{lock} exists.'
MESSAGE_LOCK_NOT_EXIST = '{lock} does not exist.'
MESSAGE_LOCK_ADDED = '{lock} has been added.'
MESSAGE_LOCK_REMOVED = '{lock} has been removed.'


start()
