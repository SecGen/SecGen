#!/usr/bin/ruby
require 'json'
require_relative '../../../../../lib/objects/local_string_encoder.rb'
class OrganisationGenerator < StringEncoder
  attr_accessor :business_name
  attr_accessor :business_motto
  attr_accessor :business_address
  attr_accessor :domain
  attr_accessor :office_telephone
  attr_accessor :office_email
  attr_accessor :industry
  attr_accessor :manager
  attr_accessor :employees
  attr_accessor :product_name

  def initialize
    super
    self.module_name = 'Organisation Generator'
    self.business_name = ''
    self.business_motto = ''
    self.business_address = ''
    self.domain = ''
    self.office_telephone = ''
    self.office_email = ''
    self.industry = ''
    self.manager = {}
    self.employees = []
    self.product_name = ''
  end

  def encode_all
    organisation_hash = {}
    organisation_hash['business_name'] = self.business_name
    organisation_hash['business_motto'] = self.business_motto
    organisation_hash['business_address'] = self.business_address
    organisation_hash['domain'] = self.domain
    organisation_hash['office_telephone'] = self.office_telephone
    organisation_hash['office_email'] = self.office_email
    organisation_hash['industry'] = self.industry
    organisation_hash['manager'] = self.manager
    organisation_hash['employees'] = self.employees
    organisation_hash['product_name'] = self.product_name

    self.outputs << organisation_hash.to_json
  end

  def get_options_array
    super + [['--business_name', GetoptLong::REQUIRED_ARGUMENT],
             ['--business_motto', GetoptLong::REQUIRED_ARGUMENT],
             ['--business_address', GetoptLong::REQUIRED_ARGUMENT],
             ['--domain', GetoptLong::REQUIRED_ARGUMENT],
             ['--office_telephone', GetoptLong::REQUIRED_ARGUMENT],
             ['--office_email', GetoptLong::REQUIRED_ARGUMENT],
             ['--industry', GetoptLong::REQUIRED_ARGUMENT],
             ['--manager', GetoptLong::REQUIRED_ARGUMENT],
             ['--employees', GetoptLong::REQUIRED_ARGUMENT],
             ['--product_name', GetoptLong::REQUIRED_ARGUMENT]]
  end

  def process_options(opt, arg)
    super
    case opt
      when '--business_name'
        self.business_name << arg;
      when '--business_motto'
        self.business_motto << arg;
      when '--business_address'
        self.business_address << arg;
      when '--domain'
        self.domain << arg;
      when '--office_telephone'
        self.office_telephone << arg;
      when '--office_email'
        self.office_email << arg;
      when '--industry'
        self.industry << arg;
      when '--manager'
        self.manager = JSON.parse(arg);
      when '--employees'
        self.employees << JSON.parse(arg);
      when '--product_name'
        self.product_name << arg;
    end
  end

  def encoding_print_string
    'business_name: ' + self.business_name.to_s + ',
    business_motto: ' + self.business_motto.to_s + ',
    business_address: ' + self.business_address.to_s + ',
    domain: ' + self.domain.to_s + ',
    office_telephone: ' + self.office_telephone.to_s + ',
    office_email: ' + self.office_email.to_s + ',
    industry: ' + self.industry.to_s + ',
    manager: ' + self.manager.to_s + ',
    employees: ' + self.employees.to_s + ',
    product_name: ' + self.product_name.to_s
  end
end

OrganisationGenerator.new.run
