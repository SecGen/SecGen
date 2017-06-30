require 'erb'
require_relative '../helpers/constants.rb'
require_relative 'xml_scenario_generator.rb'
require_relative 'xml_marker_generator.rb'
require 'fileutils'
require 'librarian'

class ProjectFilesCreator
# Creates project directory, uses .erb files to create a report and the vagrant file that will be used
# to create the virtual machines
  @systems
  @currently_processing_system
  @scenario_networks

# @param [Object] systems list of systems that have been defined and randomised
# @param [Object] out_dir the directory that the project output should be stored into
# @param [Object] scenario the file path used to as a basis
  def initialize(systems, out_dir, scenario, options)
    @systems = systems
    @out_dir = out_dir

    # if within the SecGen directory structure, trim that from the path displayed in output
    match = scenario.match(/#{ROOT_DIR}\/(.*)/i)
    if match && match.captures.size == 1
      scenario = match.captures[0]
    end
    @scenario = scenario
    @time = Time.new.to_s
    @options = options
    @scenario_networks = Hash.new { |h, k| h[k] = 0 }
  end

# Generate all relevant files for the project
  def write_files
    FileUtils.mkpath "#{@out_dir}" unless File.exists?("#{@out_dir}")
    FileUtils.mkpath "#{@out_dir}/puppet/" unless File.exists?("#{@out_dir}/puppet/")
    FileUtils.mkpath "#{@out_dir}/environments/production/" unless File.exists?("#{@out_dir}/environments/production/")

    threads = []
    # for each system, create a puppet modules directory using librarian-puppet
    @systems.each do |system|
      @currently_processing_system = system # for template access
      path = "#{@out_dir}/puppet/#{system.name}"
      FileUtils.mkpath(path) unless File.exists?(path)
      pfile = "#{path}/Puppetfile"
      Print.std "Creating Puppet modules librarian-puppet file: #{pfile}"
      template_based_file_write(PUPPET_TEMPLATE_FILE, pfile)
      Print.std 'Preparing puppet modules using librarian-puppet'
      GemExec.exe('librarian-puppet', path, 'install')

      system.module_selections.each do |selected_module|

        if selected_module.module_type == 'base'
          url = selected_module.attributes['url'].first

          unless url.nil? || url =~ /^http*/
            Print.std "Checking to see if local basebox #{url.split('/').last} exists"
            packerfile_path = "#{BASES_DIR}#{selected_module.attributes['packerfile_path'].first}"
            autounattend_path = "#{BASES_DIR}#{selected_module.attributes['packerfile_path'].first.split('/').first}/Autounattend.xml.erb"

            unless File.file? "#{VAGRANT_BASEBOX_STORAGE}/#{url}"
              Print.std "Basebox #{url.split('/').last} not found, searching for packerfile"

              if File.file? packerfile_path
                Print.info "Would you like to use the packerfile to create the packerfile from the given url (y/n)"
                (Print.info "Exiting as vagrant needs the basebox to continue"; exit) unless ['y','yes'].include?(STDIN.gets.chomp.downcase)

                Print.std "Packerfile #{packerfile_path.split('/').last} found, building basebox #{url.split('/').last} via packer"
                template_based_file_write(packerfile_path, packerfile_path.split(/.erb$/).first)
                template_based_file_write(autounattend_path, autounattend_path.split(/.erb$/).first)
                system "cd '#{packerfile_path.split(/\/[^\/]*.erb$/).first}' && packer build Packerfile && cd '#{ROOT_DIR}'"
              else
                Print.err "Packerfile not found, vagrant error may occur, please check the secgen metadata for the base module #{selected_module.name} for errors";
              end
            else
              Print.std "Vagrant basebox #{url.split('/').last} exists"
              selected_module.attributes['url'][0] = "#{VAGRANT_BASEBOX_STORAGE}/#{url}"
            end
          end
        end
      end
    end

    # Create environments/production/environment.conf - Required in Puppet 4+
    efile = "#{@out_dir}/environments/production/environment.conf"
    Print.std "Creating Puppet Environent file: #{efile}"
    FileUtils.touch(efile)

    vfile = "#{@out_dir}/Vagrantfile"
    Print.std "Creating Vagrant file: #{vfile}"
    template_based_file_write(VAGRANT_TEMPLATE_FILE, vfile)

    # Create the scenario xml file
    xfile = "#{@out_dir}/scenario.xml"

    xml_report_generator = XmlScenarioGenerator.new(@systems, @scenario, @time)
    xml = xml_report_generator.output
    Print.std "Creating scenario definition file: #{xfile}"
    begin
      File.open(xfile, 'w+') do |file|
        file.write(xml)
      end
    rescue StandardError => e
      Print.err "Error writing file: #{e.message}"
      exit
    end

    # Create the marker xml file
    x2file = "#{@out_dir}/marker.xml"

    xml_marker_generator = XmlMarkerGenerator.new(@systems, @scenario, @time)
    xml = xml_marker_generator.output
    Print.std "Creating marker file: #{x2file}"
    begin
      File.open(x2file, 'w+') do |file|
        file.write(xml)
      end
    rescue StandardError => e
      Print.err "Error writing file: #{e.message}"
      exit
    end

  end

# @param [Object] template erb path
# @param [Object] filename file to write to
  def template_based_file_write(template, filename)
    template_out = ERB.new(File.read(template), 0, '<>-')

    begin
      File.open(filename, 'w+') do |file|
        file.write(template_out.result(self.get_binding))
      end
    rescue StandardError => e
      Print.err "Error writing file: #{e.message}"
      exit
    end
  end

# Resolves the network based on the scenario and ip_range.
  def resolve_network(ip_range)
    # increment @scenario_networks{ip_range=>counter}
    if @scenario_networks[ip_range] == 0
      @scenario_networks[ip_range] = 1
    end
    @scenario_networks[ip_range] += 1

    # Split the range up and replace the last octet with the counter value
    split_ip = ip_range.split('.')
    last_octet = @scenario_networks[ip_range]
    last_octet = last_octet % 254

    # Replace the last octet in our split_ip array and return the IP
    split_ip[3] = last_octet.to_s
    split_ip.join('.')
  end

# Returns binding for erb files (access to variables in this classes scope)
# @return binding
  def get_binding
    binding
  end

end
