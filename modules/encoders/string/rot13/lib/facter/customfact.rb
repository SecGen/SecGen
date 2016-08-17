Facter.add(:rot13_encoded_value) do
  setcode do
    # distid = Facter.value(:lsbdistid)
    # case distid
    #   when /RedHatEnterprise|CentOS|Fedora/
    #     'redhat'
    #   when 'ubuntu'
    #     'debian'
    #   else
    #     distid
    # end

    # Facter::Core::Execution.exec('/bin/uname --hardware-platform')
    "TEST".tr!("A-Za-z", "N-ZA-Mn-za-m")
  end
end
