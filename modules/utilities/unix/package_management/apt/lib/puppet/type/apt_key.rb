require 'pathname'
require 'puppet/parameter/boolean'

Puppet::Type.newtype(:apt_key) do
  @doc = <<-MANIFEST
    @summary This type provides Puppet with the capabilities to manage GPG keys needed
      by apt to perform package validation. Apt has it's own GPG keyring that can
      be manipulated through the `apt-key` command.

    @example Basic usage
      apt_key { '6F6B15509CF8E59E6E469F327F438280EF8D349F':
        source => 'http://apt.puppetlabs.com/pubkey.gpg'
      }

    **Autorequires**

    If Puppet is given the location of a key file which looks like an absolute
    path this type will autorequire that file.

    @api private
  MANIFEST

  ensurable

  validate do
    if self[:refresh] == true && self[:ensure] == :absent
      raise(_('ensure => absent and refresh => true are mutually exclusive'))
    end
    if self[:content] && self[:source]
      raise(_('The properties content and source are mutually exclusive.'))
    end
    if self[:id].length < 40
      warning(_('The id should be a full fingerprint (40 characters), see README.'))
    end
  end

  newparam(:id, namevar: true) do
    desc 'The ID of the key you want to manage.'
    # GPG key ID's should be either 32-bit (short) or 64-bit (long) key ID's
    # and may start with the optional 0x, or they can be 40-digit key fingerprints
    newvalues(%r{\A(0x)?[0-9a-fA-F]{8}\Z}, %r{\A(0x)?[0-9a-fA-F]{16}\Z}, %r{\A(0x)?[0-9a-fA-F]{40}\Z})
    munge do |value|
      id = if value.start_with?('0x')
             value.partition('0x').last.upcase
           else
             value.upcase
           end
      id
    end
  end

  newparam(:content) do
    desc 'The content of, or string representing, a GPG key.'
  end

  newparam(:source) do
    desc 'Location of a GPG key file, /path/to/file, ftp://, http:// or https://'
    newvalues(%r{\Ahttps?://}, %r{\Aftp://}, %r{\A/\w+})
  end

  autorequire(:file) do
    if self[:source] && Pathname.new(self[:source]).absolute?
      self[:source]
    end
  end

  newparam(:server) do
    desc 'The key server to fetch the key from based on the ID. It can either be a domain name or url.'
    defaultto :'keyserver.ubuntu.com'

    newvalues(%r{\A((hkp|hkps|http|https)://)?([a-z\d])([a-z\d-]{0,61}\.)+[a-z\d]+(:\d{2,5})?$})
  end

  newparam(:options) do
    desc 'Additional options to pass to apt-key\'s --keyserver-options.'
  end

  newparam(:refresh, boolean: true, parent: Puppet::Parameter::Boolean) do
    desc 'When true, recreate an existing expired key'
    defaultto false
  end

  newproperty(:fingerprint) do
    desc <<-MANIFEST
      The 40-digit hexadecimal fingerprint of the specified GPG key.

      This property is read-only.
    MANIFEST
  end

  newproperty(:long) do
    desc <<-MANIFEST
      The 16-digit hexadecimal id of the specified GPG key.

      This property is read-only.
    MANIFEST
  end

  newproperty(:short) do
    desc <<-MANIFEST
      The 8-digit hexadecimal id of the specified GPG key.

      This property is read-only.
    MANIFEST
  end

  newproperty(:expired) do
    desc <<-MANIFEST
      Indicates if the key has expired.

      This property is read-only.
    MANIFEST
  end

  newproperty(:expiry) do
    desc <<-MANIFEST
      The date the key will expire, or nil if it has no expiry date.

      This property is read-only.
    MANIFEST
  end

  newproperty(:size) do
    desc <<-MANIFEST
      The key size, usually a multiple of 1024.

      This property is read-only.
    MANIFEST
  end

  newproperty(:type) do
    desc <<-MANIFEST
      The key type, one of: rsa, dsa, ecc, ecdsa

      This property is read-only.
    MANIFEST
  end

  newproperty(:created) do
    desc <<-MANIFEST
      Date the key was created.

      This property is read-only.
    MANIFEST
  end
end
