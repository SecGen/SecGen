require_relative 'system.rb'

class SystemReader

	def initialize(systems_xml)
		@systems_xml = systems_xml
	end

	def systems
		systems = []
		doc = Nokogiri::XML(File.read(@systems_xml))
		doc.xpath("//systems/system").each do |system|
			id = system["id"]
			os = system["os"]
			basebox = system["basebox"]
			url = system["url"]
			vulns = []
			networks = []

			system.css('vulnerabilities vulnerability').each do |v|
				vulnerability = Vulnerability.new
				vulnerability.privilege = v['privilege']
				vulnerability.cve = v['cve']
				vulnerability.access = v['access']
				vulnerability.type = v['type']
				vulns << vulnerability
			end

			system.css('networks network').each do |n|
				network = Network.new
				network.name = n['name']
				networks << network
			end
		    # vulns / networks are passed through to their manager and the program will create valid vulnerabilities / networks
		    # depending on what the user has specified these two will return valid vulns to be used in vagrant file creation.
		    new_vulns = VulnerabilityManager.process(vulns, Conf.vulnerabilities)
		    new_networks = NetworkManager.process(networks, Conf.networks)

		    s = System.new(id, os, basebox, url, new_vulns, new_networks)
		    if s.is_valid_base == false
			   BaseManager.generate_base(s,Conf.bases)
		    end

		    systems << s
		end
		return systems
	end
end