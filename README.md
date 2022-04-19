# Watch git lock

## Purpose

While using git commands, we sometime encounter the following error:
```
Unable to create '.git/index.lock': File exists.
```
This can be annoying in large project as it occurs frequently and takes long time to get resolved.

You can use this tool to watch the lock file (`.git/index.lock`) in a git repository and get notified whenever the lock file is added or removed.

## Usage

1. Clone or download this repo.
1. Change your current directory to a git repository (not this one) in terminal.
   ```
   cd /path/to/a/git-repo
   ```
1. Run one of the script based on scripting language that is installed.

   For example, if you have python installed, run following in terminal:
   ```
   /path/to/watch-git-lock/watch-git-lock.py
   ```
1. After `.git/index.lock` is added or removed, you will be notified of the change.

You can use alias to shorten the command. To do so, include following in `~/.bash_aliases` file:
```
alias watch-git-lock='/path/to/watch-git-lock/watch-git-lock.py'
```
After the modification, open a new terminal and run `watch-git-lock` .
