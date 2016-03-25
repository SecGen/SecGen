require 'xmlsimple'
class Xml_report_generator

  # Initialize the class with the systems array and the current build number
  # @param systems [Array]
  # @param build_number [Int]
  def initialize(systems, build_number)
    @systems = systems
    @build_number = build_number
  end

  ### Start of private methods ###
  private

  # Generates hashes as an array for all network interfaces showing the system's ip
  # @param system [Array] current system being generated
  # @return networks_array [Array] array of all network hashes
  def get_networks_hash(s)
    networks_array = Array.new
    networks_hash = Hash.new

    s.networks.each do |n|
      grab_system_number = s.id.gsub(/[^0-9]/i, "")
      n.range[9..9] = grab_system_number << '0'
      networks_hash['network'] = [n.range]

      networks_array << networks_hash
    end
    return networks_array
  end

  # Generates hashes as an array for all services to be installed on the specific system
  # @param system [Array] current system being generated
  # @return service_array [Array] array of all service hashes
  def get_services_hash(s)
    service_array = Array.new
    s.services.each do |v|
      service_hash = {'type' => [v.type], 'name' => [v.name], 'details' => [v.details]}
      v.puppets.each do |p|
        service_hash['puppet_file'] = ["#{p}.pp"]
      end
      service_array << service_hash
    end

    return service_array
  end

  # Generates hashes as an array for all vulnerabilities to be placed on the specific system
  # @param system [Array] current system being generated
  # @return vulns_array [Array] array of all vulnerability hashes
  def get_vulnerabilities_hash(s)
    vulns_array = Array.new
    s.vulns.each do |v|
      vulns_hash = {'type' => [v.type], 'details' => [v.details], 'privilege' => [v.privilege], 'access' => [v.access], 'cve' => [v.cve]}
      v.puppets.each do |p|
        vulns_hash['puppet_file'] = ["#{p['puppet'][0]}.pp"]
      end
      vulns_array << vulns_hash
    end
    return vulns_array
  end

  # Creates a hash in the specific format for the XmlSimple library
  # @return hash [Hash] compatible with XmlSimple
  def create_xml_hash
    hash = Hash.new
    @systems.each do |system|
      hash = {
          'id' => system.id, 'basebox' => system.basebox, 'os' => system.os, 'url' => system.url,
          'networks' => get_networks_hash(system),
          'services' => get_services_hash(system),
          'vulnerabilities' => get_vulnerabilities_hash(system)
      }
    end
    return hash
  end

  ### Start of public methods ###
  public

  # Write the system information to an xml file
  def write_xml_report
    XmlSimple.xml_out(create_xml_hash,{:rootname => 'system',:OutputFile => "#{PROJECTS_DIR}/Project#{@build_number}/Report.xml"})
  end

  # Return the xml as a string
  # @return Xml [String]
  def return_xml
    return XmlSimple.xml_out(create_xml_hash,{:rootname => 'system'})
  end

  # Print the xml to the console
  def print_xml
    puts XmlSimple.xml_out(create_xml_hash,{:rootname => 'system'})
  end
end