class System

  attr_accessor :name
  attr_accessor :attributes # (basebox selection)
  attr_accessor :module_selectors # (filters)
  attr_accessor :module_selections # (after resolution)

  # Initalizes System object
  # @param [Object] name of the system
  # @param [Object] attributes such as base box selection
  # @param [Object] module_selectors these are modules that define filters for selecting the actual modules to use
  def initialize(name, attributes, module_selectors)
    self.name = name
    self.attributes = attributes
    self.module_selectors = module_selectors
    self.module_selections = []
  end

  # selects from the available modules, based on the selection filters that have been specified
  # @param [Object] available_modules all available modules (vulnerabilities, services, bases)
  # @param [Object] recursion_count (retry count -- used for resolving conflicts by bruteforce randomisation)
  # @return [Object] the list of selected modules
  def resolve_module_selection(available_modules, recursion_count)

    selected_modules = []
    num_actioned_module_conflicts = 0

    # for each module specified in the scenario
    module_selectors.each do |module_filter|
      # select based on selected type, access, cve...

      search_list = available_modules.clone
      # shuffle order of available vulnerabilities
      search_list.shuffle!

      # remove any not the type of module we are adding (vulnerabilty/service)
      search_list.delete_if{|x| "#{x.module_type}_selecter" != module_filter.module_type}

      # remove non-options due to conflicts
      search_list.delete_if{|module_for_possible_exclusion|
        found_conflict = false
        selected_modules.each do |prev_selected|
          if module_for_possible_exclusion.conflicts_with(prev_selected) ||
              prev_selected.conflicts_with(module_for_possible_exclusion)
            Print.verbose "Excluding incompatible module: #{module_for_possible_exclusion.module_path}"
            num_actioned_module_conflicts += 1
            found_conflict = true
          end
        end
        found_conflict
      }

      # for each filter to apply for this module
      module_filter.attributes.keys.each do |filter_attribute_key|
        search_for = module_filter.attributes[filter_attribute_key]
        if search_for != nil && search_for !=''
          search_list.delete_if{|module_for_possible_exclusion|
            found_match = false
            if module_for_possible_exclusion.attributes["#{filter_attribute_key}"] != nil
              module_for_possible_exclusion.attributes["#{filter_attribute_key}"].each do |value|
                # Print.verbose "comparing #{value} and #{search_for}"
                if Regexp.new(search_for).match(value)
                  found_match = true
                end
              end
            else
              found_match = true
            end
            !found_match
          }
          Print.verbose "Filtered to modules matching: #{filter_attribute_key} ~= #{search_for} (->#{search_list.size})"
        else
          true
        end
      end

      if search_list.length == 0
        Print.err 'Could not find a matching module. Please check the scenario specification'
        # bruteforce conflict resolution (could be more intelligent)
        if recursion_count < 10 && num_actioned_module_conflicts > 0
          Print.err "During scenario generation #{num_actioned_module_conflicts} module conflict(s) occured..."
          Print.err 'Re-attempting to resolve scenario...'
          return resolve_module_selection(available_modules, recursion_count + 1)
        else
          exit
        end
      end
      # use from the top of the randomised list
      selected = search_list[0]
      selected_modules.push selected

      Print.std "Selected module: #{selected.attributes['name'][0]} (#{selected.module_path})"
    end
    selected_modules
  end
end