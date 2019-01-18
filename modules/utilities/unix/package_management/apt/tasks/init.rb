#!/opt/puppetlabs/puppet/bin/ruby
require 'json'
require 'open3'
require 'puppet'

def apt_get(action)
  cmd = ['apt-get', action]
  cmd << '-y' if action == 'upgrade'
  stdout, stderr, status = Open3.capture3(*cmd)
  raise Puppet::Error, stderr if status != 0 # rubocop:disable GetText/DecorateFunctionMessage
  { status: stdout.strip }
end

params = JSON.parse(STDIN.read)
action = params['action']

begin
  result = apt_get(action)
  puts result.to_json
  exit 0
rescue Puppet::Error => e
  puts({ status: 'failure', error: e.message }.to_json)
  exit 1
end
