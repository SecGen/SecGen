require 'bcrypt'

# Convert systems objects into a format that can be imported into CTFd
class CTFdGenerator

  POINTS_PER_FLAG = 100
  FREE_POINTS = 200
  
  # How much of the total reward is offset by the cost of all the hints for that flag
  # Since CTFd doesn't force hints to be taken in order, we penalise bigger hints much more, to the point that
  #  they need to think before taking a hint as they can't afford to take all of them
  PERCENTAGE_COST_FOR_ALL_HINTS = 0.8 # 80 / number of hints (normal nudge hints are cheap)
  PERCENTAGE_COST_FOR_BIG_HINTS = 0.5 # 50% cost for a big hint (bigger hints are less so)
  PERCENTAGE_COST_FOR_REALLY_BIG_HINTS = 0.7 # 50% cost for a really big hint (the name of the SecGen module)
  PERCENTAGE_COST_FOR_SOLUTION_HINTS = 0.8 # 80% cost for a solution (msf exploit, etc)


  # @param [Object] systems the list of systems
  # @param [Object] scenario the scenario file used to generate
  # @param [Object] time the current time as a string
  def initialize(systems, scenario, time)
    @systems = systems
    @scenario = scenario
    @time = time
  end

  # outputs a hash of filenames with JSON contents
  # @return [Object] hash of files
  def ctfd_files
    
    challenges = []
    hints = []
    keys = []
    
    challenges << {
              "id"=> 1,
              "name"=>"Free points", 
              "description"=>"Some free points to get you started (and for purchasing hints)!\n Enter flag{FREEPOINTS}",
              "max_attempts"=>0,
              "value"=>FREE_POINTS,
              "category"=>"Freebie",
              "type"=>"standard",
              "hidden"=>0}
    keys << {
              "id"=>1,
              "chal"=>1,
              "type"=>"static",
              "flag"=>"flag{FREEPOINTS}",
              "data"=>nil}

    @systems.each { |system|
      system.module_selections.each { |selected_module|
        # start by finding a flag, and work the way back providing hints
        selected_module.output.each { |output_value|
          if output_value.match(/^flag{.*$/)
            challenge_id = challenges.length + 1
            challenges << {
              "id"=> challenge_id,
              "name"=>"", 
              "description"=>"Remember, search for text in the format of flag{SOMETHING}, and submit it for points. If you are stuck a hint may help!",
              "max_attempts"=>0,
              "value"=>POINTS_PER_FLAG,
              "category"=>"#{system.name} VM (#{system.module_selections.first.attributes['platform'].first})",
              "type"=>"standard",
              "hidden"=>0}
            key_id =  keys.length + 1
            keys << {
              "id"=>key_id,
              "chal"=>challenge_id,
              "type"=>"static",
              "flag"=>output_value,
              "data"=>nil}

            collected_hints = []
            system.module_selections.each { |search_module_for_hints|
              if search_module_for_hints.unique_id == selected_module.write_to_module_with_id
                collected_hints = get_module_hints(search_module_for_hints, collected_hints, system.module_selections)
              end
            }
            
            collected_hints.each { |collected_hint|
              hint_id = hints.length + 1
              # weight hints for big_hint
              if collected_hint["hint_type"] == "solution"
                cost=(POINTS_PER_FLAG * PERCENTAGE_COST_FOR_SOLUTION_HINTS).round
              elsif collected_hint["hint_type"] == "really_big_hint"
                cost=(POINTS_PER_FLAG * PERCENTAGE_COST_FOR_REALLY_BIG_HINTS).round
              elsif collected_hint["hint_type"] == "big_hint"
                cost=(POINTS_PER_FLAG * PERCENTAGE_COST_FOR_BIG_HINTS).round
              else
                cost=(POINTS_PER_FLAG * PERCENTAGE_COST_FOR_ALL_HINTS / collected_hints.length).round
              end
              hints << {
                "id"=> hint_id,
                "type"=>0,
                "chal"=>challenge_id,
                "hint"=>collected_hint["hint_text"],
                "cost"=>cost
              }
            }
          end
        }
      }
    }
    
    output_hash = {
      "alembic_version.json" => "",
      "awards.json" => "",
      "challenges.json" => challenges_json(challenges),
      "config.json" => config_json(),
      "files.json" => files_json(),
      "hints.json" => hints_json(hints),
      "keys.json" => keys_json(keys),
      "pages.json" => pages_json(),
      "solves.json" => "",
      "tags.json" => "",
      "teams.json" => teams_json(),
      "tracking.json" => "",
      "unlocks.json" => "",
      "wrong_keys.json" => "",
    }
    
    output_hash

  end
  
  def files_json
    return ''
  end

  def challenges_json(challenges)
    {"count"=>challenges.length,
     "results"=>challenges,
     "meta"=>{}
    }.to_json
  end
  
  def config_json
    config_json_hash = {
      "count" => 31,
      "results" => [
        {
          "id"=>1,
          "key"=>"next_update_check",
          "value"=>"1529096764"
        },
        {
          "id"=>2,
          "key"=>"ctf_version",
          "value"=>"1.2.0"
        },
        {
          "id"=>3,
          "key"=>"ctf_theme",
          "value"=>"core"
        },
        {
          "id"=>4,
          "key"=>"ctf_name",
          "value"=>"SecGenCTF"
        },
        {
          "id"=>5,
          "key"=>"ctf_logo",
          "value"=>nil #"fca9b07e1f3699e07870b86061815b1c/logo.svg"
        },
        {
          "id"=>6,
          "key"=>"workshop_mode",
          "value"=>"0"
        },
        {
          "id"=>7,
          "key"=>"hide_scores",
          "value"=>"0"
        },
        {
          "id"=>8,
          "key"=>"prevent_registration",
          "value"=>"0"
        },
        {
          "id"=>9,
          "key"=>"start",
          "value"=>nil
        },
        {
          "id"=>10,
          "key"=>"max_tries",
          "value"=>"0"
        },
        {
          "id"=>11,
          "key"=>"end",
          "value"=>nil
        },
        {
          "id"=>12,
          "key"=>"freeze",
          "value"=>nil
        },
        {
          "id"=>13,
          "key"=>"view_challenges_unregistered",
          "value"=>"0"
        },
        {
          "id"=>14,
          "key"=>"verify_emails",
          "value"=>"0"
        },
        {
          "id"=>15,
          "key"=>"mail_server",
          "value"=>nil
        },
        {
          "id"=>16,
          "key"=>"mail_port",
          "value"=>nil
        },
        {
          "id"=>17,
          "key"=>"mail_tls",
          "value"=>"0"
        },
        {
          "id"=>18,
          "key"=>"mail_ssl",
          "value"=>"0"
        },
        {
          "id"=>19,
          "key"=>"mail_username",
          "value"=>nil
        },
        {
          "id"=>20,
          "key"=>"mail_password",
          "value"=>nil
        },
        {
          "id"=>21,
          "key"=>"mail_useauth",
          "value"=>"0"
        },
        {
          "id"=>22,
          "key"=>"setup",
          "value"=>"1"
        },
        {
          "id"=>23,
          "key"=>"css",
          "value"=>File.read(ROOT_DIR + '/lib/templates/CTFd/css.css')
        },
        {
          "id"=>24,
          "key"=>"view_scoreboard_if_authed",
          "value"=>"0"
        },
        {
          "id"=>25,
          "key"=>"prevent_name_change",
          "value"=>"1"
        },
        {
          "id"=>26,
          "key"=>"version_latest",
          "value"=>nil
        },
        {
          "id"=>27,
          "key"=>"mailfrom_addr",
          "value"=>nil
        },
        {
          "id"=>28,
          "key"=>"mg_api_key",
          "value"=>nil
        },
        {
          "id"=>29,
          "key"=>"mg_base_url",
          "value"=>nil
        },
        {
          "id"=>30,
          "key"=>"view_after_ctf",
          "value"=>"1"
        },
        {
          "id"=>31,
          "key"=>"paused",
          "value"=>"0"
        }
      ],
      "meta"=>{}
    }
    
    config_json_hash.to_json
  end

  def hints_json(hints)
    {"count"=>hints.length,
     "results"=>hints,
     "meta"=>{}
    }.to_json
  end

  def keys_json(keys)
    {"count"=>keys.length,
     "results"=>keys,
     "meta"=>{}
    }.to_json
  end

  def pages_json
    pages_json_hash = {
      "count" => 2,
      "results" => [
        {
          "id"=>1,
          "route"=>"index",
          "html"=>File.read(ROOT_DIR + '/lib/templates/CTFd/index.html'),
          "auth_required"=>0,
          "draft"=>0,
          "title"=>"Welcome"
        },
        {
          "id"=>2,
          "route"=>"submit",
          "html"=>File.read(ROOT_DIR + '/lib/templates/CTFd/submit.html'),
          "auth_required"=>0,
          "draft"=>0,
          "title"=>"Flag submission"
        }
      ],
      "meta"=>{}
    }
    
    pages_json_hash.to_json
  end

  def teams_json
    
    teams_json_hash = {
      "count" => 2,
      "results" => [
        {
          "id"=>1,
          "name"=>"adminusername",
          "email"=>"admin@email.com",
          "password"=>password_hash_string("adminpassword"),
          "website"=>nil,
          "affiliation"=>nil,
          "country"=>nil,
          "bracket"=>nil,
          "banned"=>0,
          "verified"=>1,
          "admin"=>1,
          "joined"=>"2018-06-22T10:46:26"
        },
        {
          "id"=>2,
          "name"=>"Me",
          "email"=>"email@email.com",
          "password"=>password_hash_string("mypassword"),
          "website"=>nil,
          "affiliation"=>nil,
          "country"=>nil,
          "bracket"=>nil,
          "banned"=>0,
          "verified"=>1,
          "admin"=>1,
          "joined"=>"2018-06-22T10:46:26"
        }
      ],
      "meta"=>{}
    }
    
    teams_json_hash.to_json
    
  end

  # fix difference between ruby and python bcrypt formats used by libraries
  # $bcrypt-sha256$variant,rounds$salt$checksum
  # python lib used by CTFd expects , between variant and rounds, the ruby lib puts a $ there...
  def password_hash_string(pass)
    hash_string = "$bcrypt-sha256" + BCrypt::Password.create(pass)
    hash_string[17]= ","
    hash_string
  end

  def get_module_hints(search_module_for_hints, collected_hints, all_module_selections)

    if search_module_for_hints.write_to_module_with_id != ""
      # recursion -- show hints for any parent modules
      all_module_selections.each { |search_module_for_hints_recursive|
        if search_module_for_hints_recursive.unique_id == search_module_for_hints.write_to_module_with_id
          get_module_hints(search_module_for_hints_recursive, collected_hints, all_module_selections)
        end
      }
    end

    case search_module_for_hints.module_type
      when "vulnerability"
        case search_module_for_hints.attributes['access'].first
          when "remote"
            collected_hints = collect_hint("A vulnerability that can be accessed/exploited remotely. Perhaps try scanning the system/network?", "#{search_module_for_hints.unique_id}remote", "normal", collected_hints)
          when "local"
            collected_hints = collect_hint("A vulnerability that can only be accessed/exploited with local access. You need to first find a way in...", "#{search_module_for_hints.unique_id}local", "normal", collected_hints)
        end
        type = search_module_for_hints.attributes['type'].first
        unless type == 'system' or type == 'misc' or type == 'ctf' or type == 'local' or type == 'ctf_challenge'
          collected_hints = collect_hint("The system is vulnerable in terms of its #{search_module_for_hints.attributes['type'].first}", "#{search_module_for_hints.unique_id}firsttype", "big_hint", collected_hints)
        end
        collected_hints = collect_hint("The system is vulnerable to #{search_module_for_hints.attributes['name'].first}", "#{search_module_for_hints.unique_id}name", "really_big_hint", collected_hints)
        if search_module_for_hints.attributes['hint']
          search_module_for_hints.attributes['hint'].each_with_index { |hint, i|
            collected_hints = collect_hint(clean_hint(hint), "#{search_module_for_hints.unique_id}hint#{i}", "big_hint", collected_hints)  # .gsub(/\s+/, ' ')
          }
        end
        if search_module_for_hints.attributes['solution']
          solution = search_module_for_hints.attributes['solution'].first
          collected_hints = collect_hint(clean_hint(solution), "#{search_module_for_hints.unique_id}solution", "solution", collected_hints)
        end
        if search_module_for_hints.attributes['msf_module']
          collected_hints = collect_hint("Can be exploited using the Metasploit module: #{search_module_for_hints.attributes['msf_module'].first}", "#{search_module_for_hints.unique_id}msf_module", "big_hint", collected_hints)
        end

      when "service"
        collected_hints = collect_hint("The flag is hosted using #{search_module_for_hints.attributes['type'].first}", "#{search_module_for_hints.unique_id}type", "normal", collected_hints)
      when "encoder"
        collected_hints = collect_hint("The flag is encoded/hidden somewhere", "#{search_module_for_hints.unique_id}itsanencoder", "normal", collected_hints)
        if search_module_for_hints.attributes['type'].include? 'string_encoder'
          collected_hints = collect_hint("There is a layer of encoding using a standard encoding method, look for an unusual string of text and try to figure out how it was encoded, and decode it", "#{search_module_for_hints.unique_id}stringencoder", "normal", collected_hints)
        end
        if search_module_for_hints.attributes['solution'] == nil
          collected_hints = collect_hint("The flag is encoded using a #{search_module_for_hints.attributes['name'].first}", "#{search_module_for_hints.unique_id}name", "really_big_hint", collected_hints)
        end
        if search_module_for_hints.attributes['hint']
          search_module_for_hints.attributes['hint'].each_with_index { |hint, i|
            collected_hints = collect_hint(clean_hint(hint), "#{search_module_for_hints.unique_id}hint#{i}", "big_hint", collected_hints)
          }
        end
        if search_module_for_hints.attributes['solution']
          solution = search_module_for_hints.attributes['solution'].first
          collected_hints = collect_hint(clean_hint(solution), "#{search_module_for_hints.unique_id}solution", "solution", collected_hints)
        end
      when "generator"
        if search_module_for_hints.attributes['hint']
          search_module_for_hints.attributes['hint'].each_with_index { |hint, i|
            collected_hints = collect_hint(clean_hint(hint), "#{search_module_for_hints.unique_id}hint#{i}", "big_hint", collected_hints)
          }
        end
        if search_module_for_hints.attributes['solution']
          solution = search_module_for_hints.attributes['solution'].first
          collected_hints = collect_hint(clean_hint(solution), "#{search_module_for_hints.unique_id}solution", "solution", collected_hints)
        end
    end

    collected_hints
  end
end

def collect_hint(hint_text, hint_id, hint_type, collected_hints)
  collected_hints << {
    "hint_text"=>hint_text,
    "hint_type"=>hint_type,
    "hint_id"=>hint_id
  }
end

def clean_hint str
  str.tr("\n",'').gsub(/\s+/, ' ')
end
