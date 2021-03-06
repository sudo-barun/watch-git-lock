#!/usr/bin/env node

var fs = require('fs');
var execSync = require('child_process').execSync;

var Watcher = function (dir) {
	this.dir = dir;
	this.indexFilePathRel = '.git/index.lock';
	this.indexFilePath = dir+'/'+this.indexFilePathRel;
	this.sleepDuration = 1;
};

Watcher.prototype.watch = function () {
	if (! (
		fs.existsSync(this.dir+'/.git')
		&&
		fs.lstatSync(this.dir+'/.git').isDirectory()
	)) {
		console.log('The directory "'+this.dir+ '" is not a git repository.');
		return;
	}

	var fileExists = fs.existsSync(this.indexFilePath);
	console.log(this.getInitialMessage(fileExists));

	console.log('Watching '+this.indexFilePathRel);

	while (true) {
		execSync('sleep '+this.sleepDuration);

		var fileExisted = fileExists;
		fileExists = fs.existsSync(this.indexFilePath);
		if (fileExisted !== fileExists) {
			this.notify(this.getMessage(fileExists));
		}
	}
};

Watcher.prototype.getInitialMessage = function (fileExists) {
	return fileExists
		? this.indexFilePathRel + ' exists.'
		: this.indexFilePathRel + ' does not exist.';
};

Watcher.prototype.getMessage = function (fileExists) {
	return fileExists
		? this.indexFilePathRel + ' has been added.'
		: this.indexFilePathRel + ' has been removed.';
};

Watcher.prototype.notify = function (msg) {
	console.log(msg);
	execSync('notify-send '+JSON.stringify(msg));
	execSync('paplay '+JSON.stringify(__dirname+'/notify.ogg'));
};

(new Watcher(process.cwd())).watch();
