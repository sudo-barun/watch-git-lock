#include <stdio.h>
#include <unistd.h>
#include <stdlib.h>
#include <string.h>
#include <sys/stat.h>
#include <stdbool.h>

#include "macros.h"

char *str_replace(char *orig, char *rep, char *with);


const char GIT_DIRECTORY[] = ".git";
const char LOCK_FILE_REL_PATH[] = ".git/index.lock";
const char AUDIO_FILE_REL_PATH[] = "notify.ogg";
const int SLEEP_DURATION_SEC = 1;

const char MESSAGE_DIRECTORY_NOT_GIT_REPO[] = "The directory \"{directory}\" is not a git repository.";
const char MESSAGE_WATCHING_LOCK[] = "Watching {lock}";
const char MESSAGE_LOCK_EXIST[] = "{lock} exists.";
const char MESSAGE_LOCK_NOT_EXIST[] = "{lock} does not exist.";
const char MESSAGE_LOCK_ADDED[] = "{lock} has been added.";
const char MESSAGE_LOCK_REMOVED[] = "{lock} has been removed.";

bool is_file(char* file_path)
{
	struct stat sb;
	return (
		(stat(file_path, &sb) == 0)
		&&
		S_ISREG(sb.st_mode)
	);
}

bool is_dir(char* dir_path)
{
	struct stat sb;
	return (
		(stat(dir_path, &sb) == 0)
		&&
		S_ISDIR(sb.st_mode)
	);
}

char* prepare_message(const char* string, const char* search, const char* replace)
{
	char _search[strlen("{") + strlen(search) + strlen("}") + 1];
	sprintf(_search, "%s%s%s", "{", search, "}");
	return str_replace((char*) string, (char*) _search, (char*)replace);
}

char* get_initial_message(bool file_exists)
{
	const char* message_template = file_exists ? MESSAGE_LOCK_EXIST : MESSAGE_LOCK_NOT_EXIST;
	return prepare_message(message_template, "lock", LOCK_FILE_REL_PATH);
}

char* get_message(bool file_exists)
{
	const char* message_template = file_exists ? MESSAGE_LOCK_ADDED : MESSAGE_LOCK_REMOVED;
	return prepare_message(message_template, "lock", LOCK_FILE_REL_PATH);
}

void show_toast(char* message)
{
	char cmd[strlen("notify-send watch-git-lock \"") + strlen(message) + strlen("\"") + 1];
	sprintf(cmd, "%s%s%s", "notify-send watch-git-lock \"", message, "\"");
	system(cmd);
}

void play_audio(char* audio_file_path)
{
	char cmd[strlen("paplay \"") + strlen(audio_file_path) + strlen("\"") + 1];
	sprintf(cmd, "%s%s%s", "paplay \"", audio_file_path, "\"");
	system(cmd);
}

void notify(bool file_exists, char* audio_file_path)
{
	char* message = get_message(file_exists);

	printf("%s\n", message);
	show_toast(message);
	play_audio(audio_file_path);

	free(message);
}

void watch_directory(char* directory, void (*on_change)(bool file_exists))
{
	char git_dir[strlen(directory) + strlen("/") + strlen(GIT_DIRECTORY) + 1];
	sprintf(git_dir, "%s%s%s", directory, "/", GIT_DIRECTORY);

	if (! is_dir(git_dir)) {
		char* message = prepare_message(MESSAGE_DIRECTORY_NOT_GIT_REPO, "directory", git_dir);
		printf("%s", message);
		free(message);
	}

	char lock_file_path[strlen(directory) + strlen("/") + strlen(LOCK_FILE_REL_PATH) + 1];
	sprintf(lock_file_path, "%s%s%s", directory, "/", LOCK_FILE_REL_PATH);

	bool file_exists = is_file(lock_file_path);

	char* initial_message = get_initial_message(file_exists);
	printf("%s\n", initial_message);
	free(initial_message);

	char* message = prepare_message(MESSAGE_WATCHING_LOCK, "lock", LOCK_FILE_REL_PATH);
	printf("%s\n", message);
	free(message);

	while (1) {
		sleep(SLEEP_DURATION_SEC);
		int file_existed = file_exists;
		file_exists = is_file(lock_file_path);

		if (file_existed != file_exists) {
			on_change(file_exists);
		}
	}
}

int main()
{
	char audio_file_path[strlen(APP_DIR) + strlen("/") + strlen(AUDIO_FILE_REL_PATH) + 1];
	sprintf(audio_file_path, "%s%s%s", APP_DIR, "/", AUDIO_FILE_REL_PATH);
	char* cwd = getcwd(NULL, 0);

	void onChange(bool file_exists)
	{
		notify(file_exists, audio_file_path);
	}

	watch_directory(cwd, &onChange);

	free(cwd);

	return 0;
}


// source: https://stackoverflow.com/a/779960/2178351
// You must free the result if result is non-NULL.
char *str_replace(char *orig, char *rep, char *with) {
	char *result; // the return string
	char *ins;    // the next insert point
	char *tmp;    // varies
	int len_rep;  // length of rep (the string to remove)
	int len_with; // length of with (the string to replace rep with)
	int len_front; // distance between rep and end of last rep
	int count;    // number of replacements

	// sanity checks and initialization
	if (!orig || !rep)
		return NULL;
	len_rep = strlen(rep);
	if (len_rep == 0)
		return NULL; // empty rep causes infinite loop during count
	if (!with)
		with = "";
	len_with = strlen(with);

	// count the number of replacements needed
	ins = orig;
	for (count = 0; tmp = strstr(ins, rep); ++count) {
		ins = tmp + len_rep;
	}

	tmp = result = malloc(strlen(orig) + (len_with - len_rep) * count + 1);

	if (!result)
		return NULL;

	// first time through the loop, all the variable are set correctly
	// from here on,
	//    tmp points to the end of the result string
	//    ins points to the next occurrence of rep in orig
	//    orig points to the remainder of orig after "end of rep"
	while (count--) {
		ins = strstr(orig, rep);
		len_front = ins - orig;
		tmp = strncpy(tmp, orig, len_front) + len_front;
		tmp = strcpy(tmp, with) + len_with;
		orig += len_front + len_rep; // move to next "end of rep"
	}
	strcpy(tmp, orig);
	return result;
}
