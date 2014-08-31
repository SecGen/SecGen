require 'nokogiri'
# assign constants
ROOT_DIR = File.dirname(__FILE__)

BOXES_XML = "#{ROOT_DIR}/lib/xml/boxes.xml"
NETWORKS_XML = "#{ROOT_DIR}/lib/xml/networks.xml"
VULN_XML = "#{ROOT_DIR}/lib/xml/vulns.xml"
BASE_XML = "#{ROOT_DIR}/lib/xml/bases.xml"
MOUNT_DIR = "#{ROOT_DIR}/mount/"

class System
     attr_accessor :id, :os, :url,:basebox, :networks, :vulns

    def initialize(id, os, basebox, url, vulns=[], networks=[])
        @id = id
        @os = os
        @url = url
        @basebox = basebox
        @vulns = vulns
        @networks = networks
    end

    def is_valid_base
        valid_base = Conf.bases
        #if bases match, add the url to system so it can be used for vagrant file
        valid_base.each do |b|
            if @basebox == b.vagrantbase
                @url = b.url
                return true
            end
        end
        return false
    end
end


class Network
    attr_accessor :name, :range

    def initialize(name="", range="")
        @name = name
        @range = range
    end

    def id
        hash = @name + @range
        return hash
        # return string that connects everything to 1 massive string
    end

    def eql? other
        # checks if name matches networks.xml from boxes.xml
        other.kind_of?(self.class) && @name == other.name
    end

    def hash
        @type.hash
    end
end

class NetworkManager
    # the user will either specify a blank network type or a knownnetwork type
    def self.process(networks,valid_network)
        new_networks = {}
        # intersection of valid networks / user defined networks
        legal_networks = valid_network & networks
        networks.each do |network|
            # checks to see string is blank if so valid network into a new hash map of vulnerabilities 
            if network.name == ""
                random = valid_network.sample
                 new_networks[random.id] = random
            else
                has_found = false
                # shuffle randomly selects first match 
                legal_networks.shuffle.each do |valid|
                     if network.name == valid.name
                        network.range = valid.range unless not network.range.empty?
                        # valid network into a new hash map of networks 
                        new_networks[network.id] = network
                        has_found = true
                        break
                     end
                end
                if not has_found
                    STDERR.puts "Network was not found please check the xml boxes.xml"
                    exit
                end
            end
        end
        return new_networks.values
    end
end

class Basebox
    attr_accessor :name, :os, :distro, :vagrantbase, :url
end

class BaseManager
    def self.generate_base(system,bases)
        # takes a sample from bases.xml and then assigns it to system 
        box = bases.sample
        system.basebox = box.vagrantbase 
        system.url = box.url
    return system
    end
end

class Vulnerability
    attr_accessor :type, :privilege, :access ,:puppets, :details, :ports, :cve

    def eql? other
        # checks if type matches vulns.xml from boxes.xml
        other.kind_of?(self.class) && @type == other.type
    end

    def hash
        @type.hash
    end

    def initialize(type="", privilege="", access="", puppets=[], details="", ports=[], cve="")
        @type = type
        @privilege = privilege
        @access = access
        @puppets = puppets
        @details = details
        @ports = ports
        @cve = cve
    end

    def id
        return @type + @privilege + @access
    end

end

class VulnerabilityManager
    # the user will either specify a blank vulnerability or will check it against vulns.xml and will append 
    # specific information to system if the system information is empty
    def self.process(vulns,valid_vulns)
        new_vulns = {}

        
        legal_vulns = valid_vulns & vulns
        vulns.each do |vuln|

        if vuln.type == ""
            random = valid_vulns.sample
            # valid vulnerability into a new hash map of vulnerabilities 
            new_vulns[random.id] = random
        else
            has_found = false
            # shuffle randomly selects first match of type and then abandon by break
            legal_vulns.shuffle.each do |valid|
             if vuln.type == valid.type
                vuln.puppets = valid.puppets unless not vuln.puppets.empty?
                vuln.ports = valid.ports unless not vuln.ports.empty?
                vuln.cve = valid.cve unless not vuln.cve.empty?
                vuln.privilege = valid.privilege unless not vuln.privilege.empty?
                vuln.access = valid.access unless not vuln.access.empty?
                vuln.details = valid.details
                # valid vulnerability into a new hash map of vulnerabilities 
                new_vulns[vuln.id] = vuln
                has_found = true
                break
             end
        end
            if not has_found
                STDERR.puts "vulnerability was not found please check the xml boxes.xml"
                exit
            end
        end
        end
        return new_vulns.values
    end
end

class Conf
    # this class uses nokogiri to grab all of the information from network.xml, bases.xml, and vulns.xml
    # then adds them to their specific class to do checking for legal in Manager.process
    def self.networks
        if defined? @@networks
            return @@networks
        end
        return @@networks = self._get_list(NETWORKS_XML, "//networks/network", Network)
    end

    def self.bases
        if defined? @@bases
            return @@bases
        end
        return @@bases = self._get_list(BASE_XML, "//bases/base", Basebox)
    end

    def self.vulnerabilities
        if defined? @@vulnerabilities
            return @@vulnerabilities
        end
        return @@vulnerabilities = self._get_list(VULN_XML, "//vulnerabilities/vulnerability", Vulnerability)
    end

    def self._get_list(xmlfile, xpath, cls)
        # this will search nokogiri by first reading the XML file, searching through the //root/child node
        # and the append to the specific 'cls' class
        itemlist = []

        doc = Nokogiri::XML(File.read(xmlfile))
        doc.xpath(xpath).each do |item|
            # new class e.g networks
        	obj = cls.new
            # checks to see if there are children puppet and add string to obj.puppets
            if defined? obj.puppets
                item.xpath("puppets/puppet").each { |c| obj.puppets << c.text.strip if not c.text.strip.empty? }
                item.xpath("ports/port").each { |c| obj.ports << c.text.strip if not c.text.strip.empty? }
            end

            item.each do |attr, value|

                obj.send "#{attr}=", value
            end

            itemlist << obj
        end
        return itemlist
    end
end