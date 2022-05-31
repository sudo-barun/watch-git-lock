#!/usr/bin/env python

from os import getcwd, path, system
from time import sleep
import pipes


class Watcher:
	indexFilePathRel = '.git/index.lock'
	sleepDuration = 1

	def __init__(self, dir):
		self.dir = dir
		self.indexFilePath = dir+'/'+self.indexFilePathRel

	def watch(self):

		if not path.isdir(self.dir+'/.git'):
			print('The directory "' + self.dir + '" is not a git repository.')
			return

		fileExists = path.isfile(self.indexFilePath)
		print(self.getInitialMessage(fileExists))

		print('Watching '+self.indexFilePathRel)

		while True:
			sleep(self.sleepDuration)
			fileExisted = fileExists
			fileExists = path.isfile(self.indexFilePath)
			if fileExisted != fileExists:
				self.notify(self.getMessage(fileExists))

	def getInitialMessage(self, fileExists):
		return self.indexFilePathRel + ' exists.' if fileExists \
			else self.indexFilePathRel + ' does not exist.'

	def getMessage(self, fileExists):
		return self.indexFilePathRel + ' has been added.' if fileExists \
			else self.indexFilePathRel + ' has been removed.'

	def notify(self, msg):
		print(msg)
		system('notify-send ' + pipes.quote(msg) + '')
		system('paplay ' + path.dirname(path.realpath(__file__)) + '/notify.ogg')


Watcher(getcwd()).watch()
