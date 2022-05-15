#!/usr/bin/env ruby

class Watcher
  def initialize(dir)
    @dir = dir
    @index_file_path_rel = '.git/index.lock'
    @sleep_duration = 1
    @index_file_path = "#{@dir}/#{@index_file_path_rel}"
  end

  def watch
    return p "The directory #{@dir} is not a git repository." unless File.directory?("#{@dir}/.git")

    file_exists = File.exist?(@index_file_path)
    initial_message = file_exists ? "#{@index_file_path_rel} exists." : "#{@index_file_path_rel} does not exists."

    p initial_message
    p "Watching #{@index_file_path_rel}."

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
    p message
    system("notify-send '#{message}'")
    system("paplay #{Dir.pwd}/notify.ogg")
  end
end

Watcher.new(Dir.pwd).watch
