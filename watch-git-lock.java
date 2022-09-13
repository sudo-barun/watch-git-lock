import java.io.File;
import java.io.IOException;
import java.lang.InterruptedException;
import java.util.function.Consumer;
import java.nio.file.Paths;


final class WatchGitLock {

	private static final String GIT_DIRECTORY = ".git";
	private static final String LOCK_FILE = "index.lock";
	private static final String LOCK_FILE_REL_PATH = GIT_DIRECTORY+'/'+LOCK_FILE;
	private static final String AUDIO_FILE_REL_PATH = "notify.ogg";
	private static final int SLEEP_DURATION_SEC = 1;

	public static void main(String[] args) throws InterruptedException {

		String appDir = getAppDirectory();
		String audioFilePath = appDir+'/'+AUDIO_FILE_REL_PATH;
		String cwd = Paths.get("").toAbsolutePath().toString();

		watchDirectory(cwd, (Boolean fileExists) -> notify(fileExists, audioFilePath));
	}

	private static void watchDirectory(String directory, Consumer<Boolean> onChange) throws InterruptedException {
		if (! (new File(directory+"/"+GIT_DIRECTORY)).isDirectory()) {
			System.out.println(prepareMessage(MESSAGE_DIRECTORY_NOT_GIT_REPO, "directory", directory)+'\n');
			return;
		}

		String lockFilePath = directory+'/'+LOCK_FILE_REL_PATH;

		boolean fileExists = new File(lockFilePath).isFile();
		System.out.println(getInitialMessage(fileExists));
		System.out.println(prepareMessage(MESSAGE_WATCHING_LOCK, "lock", LOCK_FILE_REL_PATH));

		while (true) {
			Thread.sleep(SLEEP_DURATION_SEC*1000);

			boolean fileExisted = fileExists;
			fileExists = new File(lockFilePath).isFile();
			if (fileExisted != fileExists) {
				onChange.accept(fileExists);
			}
		}
	}

	private static void notify(boolean fileExists, String audioFilePath) {

		String message = getMessage(fileExists);
		System.out.println(message);
		showToast(message);
		playAudio(audioFilePath);
	}

	private static void showToast(String message) {

		ProcessBuilder pb = new ProcessBuilder("notify-send", "watch-git-lock", message);
		try {
			Process p = pb.start();
		} catch (IOException ex) {
			System.err.println("Error when executing command: "+String.join(" ", pb.command()));
		}
	}

	private static void playAudio(String audioFilePath) {

		ProcessBuilder pb = new ProcessBuilder("paplay", audioFilePath);
		try {
			Process p = pb.start();
		} catch (IOException ex) {
			System.err.println("Error when executing command: "+String.join(" ", pb.command()));
		}
	}

	private static String getInitialMessage(boolean fileExists) {

		String message = fileExists ? MESSAGE_LOCK_EXIST : MESSAGE_LOCK_NOT_EXIST;
		return prepareMessage(message, "lock", LOCK_FILE_REL_PATH);
	}

	private static String getMessage(boolean fileExists) {

		String message = fileExists ? MESSAGE_LOCK_ADDED : MESSAGE_LOCK_REMOVED;
		return prepareMessage(message, "lock", LOCK_FILE_REL_PATH);
	}

	private static String prepareMessage(String string, String search, String replace) {

		return string.replace("{"+search+"}", replace);
	}

	private static String getAppDirectory() {
		Class<?> c = WatchGitLock.class;
		String classFile = c.getSimpleName()+".class";
		return c.getResource(classFile).getPath().replace("/"+classFile, "");
	}

	private static final String MESSAGE_DIRECTORY_NOT_GIT_REPO = "The directory \"{directory}\" is not a git repository.";
	private static final String MESSAGE_WATCHING_LOCK = "Watching {lock}";
	private static final String MESSAGE_LOCK_EXIST = "{lock} exists.";
	private static final String MESSAGE_LOCK_NOT_EXIST = "{lock} does not exist.";
	private static final String MESSAGE_LOCK_ADDED = "{lock} has been added.";
	private static final String MESSAGE_LOCK_REMOVED = "{lock} has been removed.";
}
