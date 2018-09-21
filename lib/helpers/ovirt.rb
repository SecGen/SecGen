require 'timeout'
require 'rubygems'
require 'process_helper'
require 'ovirtsdk4'
require_relative './print.rb'

class OVirtFunctions

  # TODO supply this as a parameter/option instead
  def self.authz
    '@aet.leedsbeckett.ac.uk-authz'
  end

  # @param [Hash] options -- command-line opts
  # @return [Boolean] is this secgen process using oVirt as the vagrant provider?
  def self.provider_ovirt?(options)
    options[:ovirtuser] and options[:ovirtpass] and options[:ovirturl]
  end

  # Helper for removing VMs which Vagrant lost track of, i.e. exist but are reported as 'have not been created'.
  # @param [String] destroy_output_log -- logfile from vagrant destroy process which contains loose VMs
  # @param [String] options -- command-line opts, used for building oVirt connection
  def self.remove_uncreated_vms(destroy_output_log, options, scenario)
    retry_count = 0
    max_retries = 5
    while retry_count <= max_retries
      begin
        # Build an ovirt connection
        ovirt_connection = get_ovirt_connection(options)
        # Determine the oVirt name of the uncreated VMs and Build the oVirt VM names
        ovirt_vm_names = build_ovirt_names(scenario, options[:prefix], get_uncreated_vms(destroy_output_log))
        ovirt_vm_names.each do |vm_name|
          # Find the oVirt VM objects
          vms = vms_service(ovirt_connection).list(search: "name=#{vm_name}")
          # Shut down and remove the VMs
          vms.each do |vm|
            begin
              Timeout.timeout(60*5) do
                while vm_exists(ovirt_connection, vm)
                  shutdown_vm(ovirt_connection, vm)
                  remove_vm(ovirt_connection, vm)
                end
                Print.info 'Successfully removed VM: ' + vm.name + ' -- ID: ' + vm.id
              end
            rescue Timeout::Error
              Print.err "Error: Removal of #{vm.name} timed-out. (ID: #{vm.id})"
              next
            end
          end
        end
      rescue OvirtSDK4::Error => ex
        if retry_count < max_retries
          Print.err 'Error: Retrying... #' + (retry_count + 1).to_s + ' of #' + max_retries.to_s
        end
        retry_count += 1
        puts ex
      end
    end
  end

  def self.vm_exists(ovirt_connection, vm)
    # Check if VM has been removed
    begin
      service = vms_service(ovirt_connection).vm_service(vm.id)
      service.get
      return true
    rescue OvirtSDK4::Error => err
      if err.code == 404
        return false
      else
        puts err
        exit(1)
      end
    end
  end

  def self.get_userrole_role(ovirt_connection)
    roles_service(ovirt_connection).list.each do |role_item|
      if role_item.name == "UserRole"
        return role_item
      end
    end
  end

  def self.roles_service(ovirt_connection)
    ovirt_connection.system_service.roles_service
  end

  def self.users_service(ovirt_connection)
    ovirt_connection.system_service.users_service
  end

  def self.vms_service(ovirt_connection)
    ovirt_connection.system_service.vms_service
  end

  def self.shutdown_vm(ovirt_connection, vm)
    service = vms_service(ovirt_connection).vm_service(vm.id)
    while service.get.status == 'up'
      service.stop
      puts 'Stopping VM: ' + vm.name
      sleep(15)
    end
  end

  def self.remove_vm(ovirt_connection, vm)
    service = vms_service(ovirt_connection).vm_service(vm.id)
    begin
      service.remove(force: true)
      puts 'Removing VM: ' + vm.name
      sleep(15)
    rescue Exception
      # ignore oVirt exception, it gets raised regardless of success / failure
    end
  end

  def self.build_ovirt_names(scenario_path, prefix, vm_names)
    ovirt_vm_names = []
    scenario_name = scenario_path.split('/').last.split('.').first
    prefix = prefix ? (prefix + '-' + scenario_name) : ('SecGen-' + scenario_name)
    vm_names.each do |vm_name|
      ovirt_vm_names << "#{prefix}-#{vm_name}".tr('_', '-')
    end
    ovirt_vm_names
  end

  def self.get_uncreated_vms(output_log)
    split = output_log.split('==> ')
    failures = []
    split.each do |line|
      if line.include? ': VM is not created. Please run `vagrant up` first.'
        failed_vm = line.split(':').first
        failures << failed_vm
      end
    end
    failures.uniq
  end

  def self.create_snapshot(options, scenario_path, vm_names)
    vms = []
    ovirt_connection = get_ovirt_connection(options)
    ovirt_vm_names = build_ovirt_names(scenario_path, options[:prefix], vm_names)
    ovirt_vm_names.each do |vm_name|
      vms << vms_service(ovirt_connection).list(search: "name=#{vm_name}")
    end

    vms.each do |vm_list|
      vm_list.each do |vm|
        Print.std " VM: #{vm.name}"
        # find the service that manages that vm
        vm_service = vms_service(ovirt_connection).vm_service(vm.id)
        Print.std "  Creating snapshot: #{vm.name}"
        begin
          vm_service.snapshots_service.add(
              OvirtSDK4::Snapshot.new(
                  description: "Automated snapshot: #{Time.new.to_s}"
              )
          )
        rescue Exception => e
          Print.err '****************************************** Skipping'
          Print.err e.message
        end
      end
    end
  end

  def self.assign_permissions(options, scenario_path, vm_names)
    ovirt_connection = get_ovirt_connection(options)
    username = options[:prefix].chomp
    user = get_user(ovirt_connection, username)
    if user
      vms = []

      ovirt_vm_names = build_ovirt_names(scenario_path, username, vm_names)
      Print.std "Searching for VMs owned by #{username} #{ovirt_vm_names}"
      ovirt_vm_names.each do |vm_name|
        vms << vms_service(ovirt_connection).list(search: "name=#{vm_name}")
      end

      vms.each do |vm_list|
        vm_list.each do |vm|
          Print.std " Found VM: #{vm.name}"

          # find the service that manages that vm
          vm_service = vms_service(ovirt_connection).vm_service(vm.id)

          # find the service that manages the permissions of that vm
          perm_service = vm_service.permissions_service

          # add a permission for that user to use that VM
          perm_attr = {}
          perm_attr[:comment] = 'Automatic assignment'
          perm_attr[:role] = get_userrole_role(ovirt_connection)
          perm_attr[:user] = user
          Print.std "  Adding permissions"
          begin
            perm_service.add OvirtSDK4::Permission.new(perm_attr)
          rescue Exception => e
            Print.err '****************************************** Skipping'
            Print.err e.message
          end
        end
      end
    else
      Print.info "No account with username #{username} found, skipping ..."
    end
  end

    # @param [String] username
    # @return [OvirtUser]
  def self.get_user(ovirt_connection, username)
    un = username.chomp
    search_string = "usrname=#{un}#{authz}"
    puts "Searching for VMs owned by #{un}"
    user = users_service(ovirt_connection).list(search: search_string).first
    if user
      Print.std "Found user '#{un}' on oVirt"
      user
    else
      Print.err "User #{un} not found"
      nil
    end
  end

    # @param [String] options -- command-line opts, contains oVirt username, password and url
  def self.get_ovirt_connection(options)
    if provider_ovirt?(options)
      conn_attr = {}
      conn_attr[:url] = options[:ovirturl]
      conn_attr[:username] = options[:ovirtuser]
      conn_attr[:password] = options[:ovirtpass]
      conn_attr[:debug] = true
      conn_attr[:insecure] = true
      conn_attr[:headers] = {'Filter' => true}
      OvirtSDK4::Connection.new(conn_attr)
    else
      Print.err('Fatal: oVirt connections require values for the --ovirtuser and --ovirtpass command line arguments')
      exit(1)
    end
  end

end

## TODO: Remove me
#
## Included cliffe's permissions script to modify tomorrow!
#
# require 'ovirtsdk4'
#
# create_snapshots = true
# assign_permissions = true
#
# authz = '@aet.leedsbeckett.ac.uk-authz'
#
# # read in the list of users (one username per line)
# localuserslist = File.readlines('./userlist.complete')
#
# # connect to oVirt
# conn_attr = {}
# conn_attr[:url] = 'https://aet-ovirt.aet.leedsbeckett.ac.uk/ovirt-engine/api'
# conn_attr[:username] = 'secgen@aet.leedsbeckett.ac.uk'
# conn_attr[:password] = 'assay4?ravel'
# conn_attr[:debug] = true
# conn_attr[:headers] = {'Filter' => true }
#
# ovirt_connection = OvirtSDK4::Connection.new(conn_attr)
# # get the service that manages the VMs
# vms_service = ovirt_connection.system_service.vms_service
# # puts vms_service.to_s
#
# # get the service that manages the users
# users_service = ovirt_connection.system_service.users_service
# # puts users_service.list
#
# # get the service that manages the roles
# roles_service = ovirt_connection.system_service.roles_service
# # puts roles_service.list
#
# # find the UserRole role
# role = "";
# roles_service.list().each do |role_item|
#   if role_item.name == "UserRole"
#     role = role_item
#   end
# end
#
# # iterate through our local list of users
# localuserslist.each do |username|
#   # find the user on oVirt
#   search_string = "usrname=#{username.chomp()}#{authz}"
#   puts "Searching for VMs owned by #{username.chomp()}"
#   user = users_service.list(search: search_string).first
#   # puts user.to_s
#
#   if user
#     puts " Found user on oVirt"
#     # find any VMs we have access to that start with their username
#     vms = vms_service.list(search: "name=#{username.chomp()}-7-*")
#     vms.each do |vm|
#       puts " VM: #{vm.name}"
#
#       # find the service that manages that vm
#       vm_service = vms_service.vm_service(vm.id)
#
#       if assign_permissions
#         # find the service that manages the permissions of that vm
#         perm_service = vm_service.permissions_service
#
#         # add a permission for that user to use that VM
#         perm_attr = {}
#         perm_attr[:comment] = 'Automatic assignment'
#         perm_attr[:role] = role
#         perm_attr[:user] = user
#         puts "  Adding permissions"
#         begin
#           perm_service.add OvirtSDK4::Permission.new(perm_attr)
#         rescue Exception => e
#           puts "****************************************** Skipping"
#           puts e.message
#         end
#       end
#
#       if create_snapshots
#         puts "  Creating snapshot"
#         begin
#           vm_service.snapshots_service.add(
#               OvirtSDK4::Snapshot.new(
#                   description: "Automated snapshot: #{Time.new.to_s}"
#               )
#           )
#         rescue Exception => e
#           puts "****************************************** Skipping"
#           puts e.message
#         end
#       end
#
#     end
#   else
#     puts "Skipping missing user: #{username}"
#   end
#
# end