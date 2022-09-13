#!/usr/bin/env ruby

class Watcher
  def initialize(dir)
    @dir = dir
    @index_file_path_rel = '.git/index.lock'
    @sleep_duration = 1
    @index_file_path = "#{@dir}/#{@index_file_path_rel}"
    @audio_file_path = "#{__dir__}/notify.ogg"
  end

  def watch
    return print "The directory #{@dir} is not a git repository.\n" unless File.directory?("#{@dir}/.git")

    file_exists = File.exist?(@index_file_path)
    initial_message = file_exists ? "#{@index_file_path_rel} exists." : "#{@index_file_path_rel} does not exists."

    print "#{initial_message}\n"
    print "Watching #{@index_file_path_rel}.\n"

    while true
      sleep(@sleep_duration)

      file_existed = file_exists
      file_exists = File.exist?(@index_file_path)

      if file_existed != file_exists
        message = file_exists ? "#{@index_file_path_rel} has been added." : "#{@index_file_path_rel} has been removed."
        notify(message)
      end
    end
  end

  def notify(message)
    print "#{message}\n"
    show_toast(message)
    play_audio()
  end

  def show_toast(message)
    system("notify-send watch-git-lock '#{message}'")
  end

  def play_audio()
    system("paplay #{@audio_file_path}")
  end
end

Watcher.new(Dir.pwd).watch
