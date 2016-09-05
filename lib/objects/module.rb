require_relative '../helpers/constants.rb'
require 'digest/md5'
require 'securerandom'

class Module
  #Vulnerability attributes hash
  attr_accessor :module_path # vulnerabilities/unix/ftp/vsftp_234_backdoor
  attr_accessor :module_type # vulnerability|service|utility
  attr_accessor :attributes  # attributes are hashes that contain arrays of values
  # Each attribute is stored in a hash containing an array of values (because elements such as author can repeat).
  # Module *selectors*, store filters in the attributes hash.
  # XML validity ensures valid and complete information.

  attr_accessor :write_to_module_with_id # the module instance that this module writes to
  attr_accessor :write_output_variable # the variable/fact written to
  attr_accessor :output # the result of local processing
  attr_accessor :unique_id # the unique id for this module *instance*
  attr_accessor :received_inputs # any locally calculated inputs fed into this module instance
  # (if not calculated at VM run time using puppet)

  attr_accessor :conflicts
  attr_accessor :requires
  attr_accessor :puppet_file
  attr_accessor :puppet_other_path
  attr_accessor :local_calc_file

  # @param [Object] module_type: such as 'vulnerability', 'base', 'service', 'network'
  def initialize(module_type)
    self.module_type = module_type
    self.conflicts = []
    self.requires = []
    self.attributes = {}
    self.output = "dynamic"
    self.write_to_module_with_id = write_output_variable = ''
    self.received_inputs = {}

    # self.attributes['module_type'] = module_type # add as an attribute for filtering
  end

  # @return [Object] a string for console output
  def to_s
    (<<-END)
    #{module_type}: #{module_path}
      attributes: #{attributes.inspect}
      conflicts: #{conflicts.inspect}
      requires: #{requires.inspect}
      puppet file: #{puppet_file}
      puppet path: #{puppet_other_path}
    END
  end

  # @return [Object] a string for Vagrant/Ruby file comments
  def to_s_comment
    (<<-END)
    # #{module_type}: #{module_path}
    #   attributes: #{attributes.inspect}
    #   conflicts: #{conflicts.inspect}
    #   requires: #{requires.inspect}
    END
  end

  # @return [Object] the leaf directory (last part of the module path)
  def module_path_end
    match = module_path.match(/.*?([^\/]*)$/i)
    match.captures[0]
  end

  # @return [Object] the module path with _ rather than / for use as a variable name
  def module_path_name
    module_path_name = module_path.clone
    module_path_name.gsub!('/','_')
  end

  # # pre-calculate any secgen_local/local.rb outputs
  # def local_processing
  #   if self.local_calc_file
  #     self.output = `#{self.local_calc_file}`.chomp
  #   else
  #     self.output = 'dynamic'
  #   end
  # end

  # @return [Object] a list of attributes that can be used to re-select the same modules
  def attributes_for_scenario_output
    attr_flattened = {}

    attributes.each do |key, array|
      unless "#{key}" == 'module_type' || "#{key}" == 'conflict'
        # creates a valid regexp that can match the original module
        attr_flattened["#{key}"] = Regexp.escape(array.join('~~~')).gsub(/\n\w*/, '.*').gsub(/\\ /, ' ').gsub(/~~~/, '|')
      end
    end

    attr_flattened
  end

  # # resolve randomisation of inputs
  # def select_inputs
  #   inputs.each do |input|
  #   #   TODO TODO
  #     Print.verbose "Input #{input["name"][0]}"
  #     Print.verbose "Rand type: #{input["randomisation_type"][0]}"
  #     case input["randomisation_type"][0]
  #       when "one_from_list"
  #         if input["value"].size == 0
  #           Print.err "Randomisation not possible for #{module_path} (one_from_list with no values)"
  #           exit
  #         end
  #         one_value = [input["value"].shuffle![0]]
  #         input["value"] = one_value
  #       when "flag_value"
  #         # if no value suppied, generate one
  #         unless input["value"]
  #           input["value"] = ["THE_FLAG_IS:#{SecureRandom.hex}"]
  #         else
  #           input["value"] = ["THE_FLAG_IS:#{input["value"][0]}"]
  #         end
  #       when "none"
  #         # nothing...
  #
  #     end
  #
  #     # if an encoding is specified
  #     if input["encoding"]
  #       if input["encoding"].size > 1
  #         input["encoding"] = [input["encoding"].shuffle![0]]
  #       else
  #         enc = input["encoding"][0]
  #       end
  #       #
  #       # TODO?? case enc
  #       #   when "base64_encode"
  #       #     require "base64"
  #       #     unless input["value"]
  #       #       input["value"] = [Base64.encode64(SecureRandom.hex)]
  #       #     else
  #       #       input["value"] = [Base64.encode64(input["value"][0])]
  #       #     end
  #       #   when "MD5_calc_hash"
  #       #     unless input["value"]
  #       #       input["value"] = [Digest::MD5.hexdigest(SecureRandom.hex)]
  #       #     else
  #       #       input["value"] = [Digest::MD5.hexdigest(input["value"][0])]
  #       #     end
  #       # end
  #     end
  #
  #   end
  #
  #   Print.err inputs.inspect
  # end

  # A one directional test for conflicts
  # Returns whether this module specifies it conflicts with the other_module.
  # Each conflict can have multiple conditions which must all be met for this
  # to be considered a conflict. However, only one conflict needs to be satisfied.
  # @param [Object] other_module to compare with
  # @return [Object] boolean
  def conflicts_with(other_module)
    # for each conflict
    self.conflicts.each do |conflict|
      if other_module.matches_attributes_requirement(conflict)
        return true
      end
    end
    false
  end

  def matches_attributes_requirement(required)
    all_conditions_met = true
    required.keys.each do |require_key|
      key_matched = false

      if self.attributes["#{require_key}"] != nil
        # for each corresponding value in the previously selected module
        self.attributes["#{require_key}"].each do |value|
          # for each value in the required list
          required["#{require_key}"].each do |required_value|
            if Regexp.new(required_value).match(value)
              key_matched = true
            end
          end
        end
      end
      # any failure to match
      unless key_matched
        return false
      end
    end
    all_conditions_met
  end

  def printable_name
    "#{self.attributes['name'][0]} (#{self.module_path})"
  end

end
