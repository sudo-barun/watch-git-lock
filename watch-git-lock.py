#!/usr/bin/env python

from os import getcwd, path
from time import sleep
import pipes
import subprocess


class Watcher:
	index_file_path_rel = '.git/index.lock'
	sleep_duration = 1

	def __init__(self, dir):
		self.dir = dir
		self.index_file_path = dir+'/'+self.index_file_path_rel

	def watch(self):

		if not path.isdir(self.dir+'/.git'):
			print('The directory "' + self.dir + '" is not a git repository.')
			return

		file_exists = path.isfile(self.index_file_path)
		print(self.get_initial_message(file_exists))

		print('Watching '+self.index_file_path_rel)

		while True:
			sleep(self.sleep_duration)
			file_existed = file_exists
			file_exists = path.isfile(self.index_file_path)
			if file_existed != file_exists:
				self.notify(self.get_message(file_exists))

	def get_initial_message(self, file_exists):
		return self.index_file_path_rel + ' exists.' if file_exists \
			else self.index_file_path_rel + ' does not exist.'

	def get_message(self, file_exists):
		return self.index_file_path_rel + ' has been added.' if file_exists \
			else self.index_file_path_rel + ' has been removed.'

	def notify(self, msg):
		print(msg)
		subprocess.Popen(['notify-send', 'watch-git-lock', msg])
		subprocess.Popen(['paplay', path.dirname(path.realpath(__file__)) + '/notify.ogg'])


Watcher(getcwd()).watch()
