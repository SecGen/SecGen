#!/usr/bin/ruby
require_relative '../../../../../lib/objects/local_string_encoder.rb'
require 'open3'

class SSHKeyPairGenerator < StringEncoder

  def initialize
    super
    self.module_name = 'GPG Key Pair Generator'
  end

  def encode_all
    _, gen_stderr, _ = Open3.capture3("gpg --batch --gen-key #{GENERATORS_DIR}key/gpg_key_pair/files/parameterfile")
    key_id = gen_stderr.split("\n").last.split(" ")[2]

    pubkey_stdout, _, _ = Open3.capture3("gpg --armor --export-secret-keys #{key_id}")
    privkey_stdout, _, _ = Open3.capture3("gpg --armor --export #{key_id}")

    self.outputs << {'private' => privkey_stdout, 'public' => pubkey_stdout}.to_json
  end

end

SSHKeyPairGenerator.new.run
