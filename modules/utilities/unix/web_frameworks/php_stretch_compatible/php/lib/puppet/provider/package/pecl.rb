require 'puppet/provider/package'

Puppet::Type.type(:package).provide :pecl, parent: :pear do
  desc 'Package management via `pecl`.'

  has_feature :versionable
  has_feature :upgradeable
  has_feature :install_options

  commands pear: 'pear'

  def self.instances
    pear_packages = super

    pear_packages.select do |pkg|
      pkg.properties[:vendor] == 'pecl.php.net'
    end
  end

  def convert_to_pear
    @resource[:source] = "pecl.php.net/#{@resource[:name]}"
  end

  def install(useversion = true)
    convert_to_pear
    super(useversion)
  end

  def latest
    convert_to_pear
    super
  end

  def uninstall
    convert_to_pear
    super
  end
end
