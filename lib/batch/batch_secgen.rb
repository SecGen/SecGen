require 'getoptlong'
require 'open3'
require 'pg'

require_relative '../helpers/print.rb'
require_relative '../helpers/constants.rb'

# Globals
@db_conn = nil
@secgen_args = ''

# Displays secgen_batch usage data
def usage
  Print.std "Usage:
   #{$0} <command> [--options]

   COMMANDS:
   add, a: Adds a job to the queue
   start: Starts the service, works through the job queue
   list: Lists the current entries in the job queue
   delete: Delete job(s) from the queue table

   OPTIONS:
   [add]
   --instances [integer n]: Number of instances of the scenario to create with default project naming format
   --instances [prefix,prefix, ...]: Alternatively supply a comma separated list of strings to prefix to project output
   ---: Delimiter, anything after this will be passed to secgen.rb as an argument.
   Example: `ruby batch_secgen.rb add --instances here,are,some,prefixes --- -s scenarios/default_scenario.xml run`

   [start]
   --max_threads [integer n] (optional): Maximum number of worker threads, defaults to 1

   [list]
   --id [integer n] (optional): List the entry for a specific Job ID
   --all: List all jobs in the queue table

   [delete]
   --id [integer n]: Delete the entry for a specific Job ID
   --all: Delete all jobs from the queue table

   [misc]
   --help, -h: Shows this usage information

"
  exit
end

def misc_opts
  [['--help', '-h', GetoptLong::NO_ARGUMENT]]
end

def get_add_opts
  add_options = misc_opts + [['--instances', '-i', GetoptLong::REQUIRED_ARGUMENT]]
  options = parse_opts(GetoptLong.new(*add_options))
  if options[:instances] == ''
    Print.err 'Error: The add command requires an argument.'
    usage
  else
    options
  end
end

def get_start_opts
  start_options = misc_opts + [['--max_threads', GetoptLong::REQUIRED_ARGUMENT]]
  parse_opts(GetoptLong.new(*start_options))
end

def get_list_opts
  list_options = misc_opts + [['--id', GetoptLong::REQUIRED_ARGUMENT],
                              ['--all', GetoptLong::OPTIONAL_ARGUMENT]]
  parse_opts(GetoptLong.new(*list_options))
end

def get_delete_opts
  delete_options = misc_opts + [['--id', GetoptLong::REQUIRED_ARGUMENT],
                                ['--all', GetoptLong::OPTIONAL_ARGUMENT]]
  options = parse_opts(GetoptLong.new(*delete_options))
  if options[:id] == '' and options[:all] == false
    Print.err 'Error: The delete command requires an argument.'
    usage
  else
    options
  end
end

def parse_opts(opts)
  options = {:instances => '', :max_threads => 1, :id => '', :all => false}
  opts.each do |opt, arg|
    case opt
      when '--instances'
        options[:instances] = arg
      when '--max_threads'
        options[:max_threads] = arg
      when '--id'
        options[:id] = arg
      when '--all'
        options[:all] = true
      else
        Print.err 'Invalid argument'
        exit(false)
    end
  end
  options
end

# Command Functions

def add(options)
  # Handle --instances
  instances = options[:instances]
  if (instances.to_i.to_s == instances) and instances.to_i > 1
    instances.to_i.times do |count|
      instance_args = "--prefix batch_job_#{(count+1).to_s} " + @secgen_args
      insert_row(count.to_s, instance_args)
    end
  elsif instances.include?(',')
    named_prefixes = instances.split(',')
    named_prefixes.each_with_index do |named_prefix, count|
      instance_secgen_args = "--prefix #{named_prefix} " + @secgen_args
      insert_row(count.to_s, instance_secgen_args)
    end
  else
    insert_row('batch_job_1', @secgen_args)
  end
end

def start(options)
  # Start in SecGen's ROOT_DIR
  Dir.chdir ROOT_DIR

  # Start the service and call secgen.rb
  current_threads = []
  while true
    if (get_jobs.size > 0) and (current_threads.size < options[:max_threads].to_i)
      current_threads << Thread.new {
        current_job = get_jobs[0]
        job_id = current_job['id']
        update_status(job_id, :running)

        secgen_args = current_job['secgen_args']

        # execute secgen
        puts "Running job_id(#{job_id}): secgen.rb #{secgen_args}"
        stdout, stderr, status = Open3.capture3("ruby secgen.rb #{secgen_args}")
        puts "Job #{job_id} Complete"

        if status.exitstatus == 0
          update_status(job_id, :success)
          log_prefix = ''
        else
          update_status(job_id, :error)
          log_prefix = 'ERROR_'
        end

        # Log output
        Dir.mkdir 'log' unless Dir.exists? 'log'
        project_path = stdout.split('Creating project: ')[1].split('...')[0]
        project_id = project_path.split('projects/')[1]
        log = File.new("log/#{log_prefix}#{project_id}", 'w')
        log.write("SecGen project path::: #{project_path}\n\n\n")
        log.write("SecGen arguments::: #{secgen_args}\n\n\n")
        log.write("SecGen output::: \n\n\n")
        log.write(stdout)
        log.write("\n\n\nGenerator local output::: \n\n\n")
        log.write(stderr)
        log.close
      }
      sleep(1)
    else
      current_threads.delete_if { |thread| !thread.alive? }
      sleep(2) # don't use a busy-waiting loop, choose a blocking sleep that frees up CPU
    end
  end

end

def list(options)
  if options[:id] == ''
    items = select_all
    items.each do |row|
      Print.info row
    end
  else
    Print.info select_id(options[:id])
  end

end

def delete(options)
  if options[:id] != ''
    delete_id(options[:id])
  elsif options[:all]
    delete_all
  end
end

def get_jobs
  select_all_todo.to_a
end

# Database interactions
def insert_row(statement_id, secgen_args)
  statement = "insert_row_#{statement_id}"
  # Add --shutdown and strip trailing whitespace
  secgen_args = '--shutdown ' + secgen_args.strip
  Print.info "Adding to queue: '#{statement}' '#{secgen_args}' 'todo'"
  @db_conn.prepare(statement, 'insert into queue (secgen_args, status) values ($1, $2)')
  @db_conn.exec_prepared(statement, [secgen_args, 'todo'])
end

def select_all
  @db_conn.exec_params('SELECT * FROM queue;')
end

def select_all_todo
  @db_conn.exec_params("SELECT * FROM queue where status = 'todo';")
end

def select_id(id)
  statement = "select_id_#{id}"
  @db_conn.prepare(statement, 'SELECT * FROM queue where id = $1;')
  @db_conn.exec_prepared(statement, [id]).first
end

def update_status(job_id, status)
  status_enum = {:todo => 'todo', :running => 'running', :success => 'success', :error => 'error'}

  statement = "update_status_#{job_id}_#{status}"
  @db_conn.prepare(statement, 'UPDATE queue SET status = $1 WHERE id = $2')
  @db_conn.exec_prepared(statement,[status_enum[status], job_id])
end

def delete_all
  Print.info 'Are you sure you want to DELETE all jobs from the queue table? [y/N]'
  input = STDIN.gets.chomp
  if input == 'Y' or input == 'y'
    Print.info 'Deleting all jobs from Queue table'
    @db_conn.exec_params('DELETE FROM queue;')
  else
    exit
  end
end

def delete_id(id)
  Print.info "Deleting job_id: #{id}"
  statement = "delete_job_id_#{id}"
  @db_conn.prepare(statement, 'DELETE FROM queue where id = $1')
  @db_conn.exec_prepared(statement, [id])
end

Print.std '~'*47
Print.std 'SecGen Batch - Batch VM Generation Service'
Print.std '~'*47

# Connect to database
@db_conn = PG::Connection.open(:dbname => 'secgen_batch')

# Capture SecGen options
delimiter_index = ARGV.find_index('---')
if delimiter_index
  ARGV[delimiter_index+1..ARGV.length].each { |secgen_arg|
    @secgen_args += secgen_arg + ' '
  }
  # Reassign ARGV to values before delimiter (and suppress constant reassignment warning)
  original_verbose, $VERBOSE = $VERBOSE, nil
  ARGV = ARGV[0..delimiter_index - 1]
  $VERBOSE = original_verbose
end

# Run command
case ARGV[0]
  when 'add'
    add(get_add_opts)
  when 'start'
    start(get_start_opts)
  when 'list'
    list(get_list_opts)
  when 'delete'
    delete(get_delete_opts)
  else
    usage
end