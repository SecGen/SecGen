#!/usr/bin/ruby
require_relative '../../../../../../lib/objects/local_string_encoder.rb'
require 'json'
require 'open3'
require 'fileutils'
require 'openssl'

class SimpleSSHDecrypt < StringEncoder
  attr_accessor :ssh_key_pair
  attr_accessor :tmp_path
  attr_accessor :subdirectory

  def initialize
    super
    self.module_name = 'Simple SSH Decryption Challenge'
    self.subdirectory = ''
    self.ssh_key_pair = {}
    self.tmp_path = File.expand_path(File.dirname(__FILE__)).split("/")[0...-1].join('/') + '/tmp/'
    Dir.mkdir self.tmp_path unless Dir.exists? self.tmp_path
    self.tmp_path += Time.new.strftime("%Y%m%d_%H%M%S")
    Dir.mkdir self.tmp_path unless Dir.exists? self.tmp_path
  end

  def encode_all
    begin
      public_ascii = self.ssh_key_pair['public']
      private_ascii = self.ssh_key_pair['private']

      # save strings_to_encode to a file
      File.open("#{self.tmp_path}/strings_to_encode", "w+") do |file|
        self.strings_to_encode.each do |line|
          file.write(line)
        end
        file.close
      end

      # Save ascii pubkey to file
      File.open("#{self.tmp_path}/id_rsa.pub", "w+") do |file|
        file.write(public_ascii.chomp)
      end

      # Convert public key to PEM so OpenSSL can consume it
      _, _, _ = Open3.capture3("ssh-keygen -f #{self.tmp_path}/id_rsa.pub -e -m pem > #{self.tmp_path}/id_rsa.pem.pub")
      ciphertext, _, _ = Open3.capture3("cat #{self.tmp_path}/strings_to_encode | openssl rsautl -encrypt -pubin -inkey #{self.tmp_path}/id_rsa.pem.pub")

      self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(ciphertext.chomp), :filename => 'cipher', :ext => 'txt', :subdirectory => self.subdirectory}}.to_json
      self.outputs << {:secgen_leaked_data => {:data => Base64.strict_encode64(private_ascii), :filename => 'id_rsa', :ext => 'txt', :subdirectory => self.subdirectory}}.to_json
    ensure
      # Delete the local key files to avoid batch clashes
      # FileUtils.rm_r self.tmp_path
    end
  end

  def process_options(opt, arg)
    super
    case opt
    when '--subdirectory'
      self.subdirectory << arg;
    when '--ssh_key_pair'
      self.ssh_key_pair = JSON.parse(arg);
    end
  end

  def get_options_array
    super + [['--subdirectory', GetoptLong::REQUIRED_ARGUMENT],
             ['--ssh_key_pair', GetoptLong::REQUIRED_ARGUMENT]]
  end


  def encoding_print_string
    'strings_to_encode: ' + self.strings_to_encode.to_s + print_string_padding +
    'subdirectory: ' + self.subdirectory.to_s + print_string_padding +
    'ssh_key_pair: ' + self.ssh_key_pair.to_json
  end
end

SimpleSSHDecrypt.new.run