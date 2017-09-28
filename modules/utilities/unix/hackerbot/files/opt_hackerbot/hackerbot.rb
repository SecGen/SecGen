require 'cinch'
require 'nokogiri'
require 'nori'
require './print.rb'
require 'open3'
require 'programr'
require 'getoptlong'
require 'thwait'

def check_output_conditions(bot_name, bots, current, lines, m)
  condition_met = false
  bots[bot_name]['attacks'][current]['condition'].each do |condition|
    if !condition_met && condition.key?('output_matches') && lines =~ /#{condition['output_matches']}/
      condition_met = true
      m.reply "#{condition['message']}"
    end
    if !condition_met && condition.key?('output_not_matches') && lines !~ /#{condition['output_not_matches']}/
      condition_met = true
      m.reply "#{condition['message']}"
    end
    if !condition_met && condition.key?('output_equals') && lines == condition['output_equals']
      condition_met = true
      m.reply "#{condition['message']}"
    end

    if condition_met
      # Repeated logic for trigger_next_attack
      if condition.key?('trigger_next_attack')
        # is this the last one?
        if bots[bot_name]['current_attack'] < bots[bot_name]['attacks'].length - 1
          bots[bot_name]['current_attack'] += 1
          bots[bot_name]['current_quiz'] = nil
          current = bots[bot_name]['current_attack']

          sleep(1)
          # prompt for current hack
          m.reply bots[bot_name]['attacks'][current]['prompt']
        else
          m.reply bots[bot_name]['messages']['last_attack'].sample
        end
      end

      if condition.key?('trigger_quiz')
        m.reply bots[bot_name]['attacks'][current]['quiz']['question']
        m.reply bots[bot_name]['messages']['say_answer']
        bots[bot_name]['current_quiz'] = 0
      end
    end
  end
  unless condition_met
    if bots[bot_name]['attacks'][current]['else_condition']
      m.reply bots[bot_name]['attacks'][current]['else_condition']['message']
    end
  end
  current
end

def read_bots (irc_server_ip_address)
  bots = {}
  Dir.glob("config/*.xml").each do |file|
    print "#{file}"

    begin
      doc = Nokogiri::XML(File.read(file))
    rescue
      Print.err "Failed to read hackerbot file (#{file})"
      print "Failed to read hackerbot file (#{file})"

      exit
    end
    #
    # # TODO validate scenario XML against schema
    # begin
    #   xsd = Nokogiri::XML::Schema(File.read(schema_file))
    #   xsd.validate(doc).each do |error|
    #     Print.err "Error in bot config file (#{file}):"
    #     Print.err '    ' + error.message
    #     exit
    #   end
    # rescue Exception => e
    #   Print.err "Failed to validate bot config file (#{file}): against schema (#{schema_file})"
    #   Print.err e.message
    #   exit
    # end

    # remove xml namespaces for ease of processing
    doc.remove_namespaces!

    doc.xpath('/hackerbot').each_with_index do |hackerbot|

      bot_name = hackerbot.at_xpath('name').text
      Print.debug bot_name
      bots[bot_name] = {}

      chatbot_rules = hackerbot.at_xpath('AIML_chatbot_rules').text
      Print.debug "Loading chatbot ai from #{chatbot_rules}"
      bots[bot_name]['chat_ai'] = ProgramR::Facade.new
      bots[bot_name]['chat_ai'].learn([chatbot_rules])

      get_shell = hackerbot.at_xpath('get_shell').text
      Print.debug get_shell
      bots[bot_name]['get_shell'] = get_shell

      bots[bot_name]['messages'] = Nori.new.parse(hackerbot.at_xpath('//messages').to_s)['messages']
      Print.debug bots[bot_name]['messages'].to_s

      bots[bot_name]['attacks'] = []
      hackerbot.xpath('//attack').each do |attack|
        bots[bot_name]['attacks'].push Nori.new.parse(attack.to_s)['attack']
      end
      bots[bot_name]['current_attack'] = 0

      bots[bot_name]['current_quiz'] = nil

      Print.debug bots[bot_name]['attacks'].to_s

      bots[bot_name]['bot'] = Cinch::Bot.new do
        configure do |c|
          c.nick = bot_name
          c.server = irc_server_ip_address
          # joins a channel named after the bot, and #bots
          c.channels = ["##{bot_name}", '#bots']
        end

        on :message, /hello/i do |m|
          m.reply "Hello, #{m.user.nick} (#{m.user.host})."
          m.reply bots[bot_name]['messages']['greeting']
          current = bots[bot_name]['current_attack']

          # prompt for the first attack
          m.reply bots[bot_name]['attacks'][current]['prompt']
          m.reply bots[bot_name]['messages']['say_ready'].sample
        end

        on :message, /help/i do |m|
          m.reply bots[bot_name]['messages']['help']
        end

        on :message, 'next' do |m|
          m.reply bots[bot_name]['messages']['next'].sample

          # is this the last one?
          if bots[bot_name]['current_attack'] < bots[bot_name]['attacks'].length - 1
            bots[bot_name]['current_attack'] += 1
            bots[bot_name]['current_quiz'] = nil
            current = bots[bot_name]['current_attack']

            # prompt for current hack
            m.reply bots[bot_name]['attacks'][current]['prompt']
            m.reply bots[bot_name]['messages']['say_ready'].sample
          else
            m.reply bots[bot_name]['messages']['last_attack'].sample
          end

        end

        on :message, /^(goto|attack) [0-9]+$/i do |m|
          m.reply bots[bot_name]['messages']['goto'].sample
          requested_index = m.message.chomp().split[1].to_i - 1

          Print.debug "requested_index = #{requested_index}, bots[bot_name]['attacks'].length = #{bots[bot_name]['attacks'].length}"

          # is this a valid attack number?
          if requested_index < bots[bot_name]['attacks'].length
            bots[bot_name]['current_attack'] = requested_index
            bots[bot_name]['current_quiz'] = nil
            current = bots[bot_name]['current_attack']

            # prompt for current hack
            m.reply bots[bot_name]['attacks'][current]['prompt']
            m.reply bots[bot_name]['messages']['say_ready'].sample
          else
            m.reply bots[bot_name]['messages']['invalid']
          end

        end

        on :message, /^(the answer is|answer):? .+$/i do |m|
          answer = m.message.chomp().split[1].to_i - 1
          answer = m.message.chomp().match(/(the answer is|answer):? (.+)$/i)[2]

          # current_quiz = bots[bot_name]['current_quiz']
          current = bots[bot_name]['current_attack']

          quiz = nil
          # is there ONE quiz question?
          if bots[bot_name]['attacks'][current].key?('quiz') && bots[bot_name]['attacks'][current]['quiz'].key?('answer')
            quiz = bots[bot_name]['attacks'][current]['quiz']
          # multiple quiz questions?
          # elsif bots[bot_name]['attacks'][current]['quiz'][current_quiz].key?('answer')
          #   quiz = bots[bot_name]['attacks'][current]['quiz'][current_quiz]
          end

          if quiz != nil
            correct_answer = quiz['answer'].
                gsub(/{{post_command_output}}/, bots[bot_name]['attacks'][current]['post_command_output']).
                gsub(/{{shell_command_output_first_line}}/, bots[bot_name]['attacks'][current]['get_shell_command_output'].split("\n").first).
                gsub(/{{pre_shell_command_output_first_line}}/, bots[bot_name]['attacks'][current]['get_shell_command_output'].split("\n").first)


            if answer.match(correct_answer)
              m.reply bots[bot_name]['messages']['correct_answer']
              m.reply quiz['correct_answer_response']

              # Repeated logic for trigger_next_attack
              if quiz.key?('trigger_next_attack')
                if bots[bot_name]['current_attack'] < bots[bot_name]['attacks'].length - 1
                  bots[bot_name]['current_attack'] += 1
                  bots[bot_name]['current_quiz'] = nil
                  current = bots[bot_name]['current_attack']

                  sleep(1)
                  # prompt for current hack
                  m.reply bots[bot_name]['attacks'][current]['prompt']
                  m.reply bots[bot_name]['messages']['say_ready'].sample
                else
                  m.reply bots[bot_name]['messages']['last_attack'].sample
                end
              end

            else
              m.reply bots[bot_name]['messages']['incorrect_answer']
            end
          else
            m.reply bots[bot_name]['messages']['no_quiz']
          end

        end

        on :message, 'previous' do |m|
          m.reply bots[bot_name]['messages']['previous'].sample

          # is this the last one?
          if bots[bot_name]['current_attack'] > 0
            bots[bot_name]['current_attack'] -= 1
            bots[bot_name]['current_quiz'] = nil
            current = bots[bot_name]['current_attack']

            # prompt for current hack
            m.reply bots[bot_name]['attacks'][current]['prompt']
            m.reply bots[bot_name]['messages']['say_ready'].sample

          else
            m.reply bots[bot_name]['messages']['first_attack'].sample
          end

        end

        on :message, 'list' do |m|
          bots[bot_name]['attacks'].each_with_index {|val, index|
            uptohere = ''
            if index == bots[bot_name]['current_attack']
              uptohere = '--> '
            end

            m.reply "#{uptohere}attack #{index+1}: #{val['prompt']}"
          }
        end

        # fallback to AIML ALICE chatbot responses
        on :message do |m|

          # Only process messages not related to controlling attacks
          if m.message !~ /hello|help|next|previous|list|^(goto|attack) [0-9]|(the answer is|answer)/
            reaction = ''
            begin
              reaction = bots[bot_name]['chat_ai'].get_reaction(m.message.gsub /([^a-z0-9\- ]+)/i, '')
            rescue Exception => e
              puts e.message
              puts e.backtrace.inspect
              reaction = ''
            end
            if reaction != ''
              m.reply reaction
            else
              if m.message.include?('?')
                m.reply bots[bot_name]['messages']['non_answer']
              end
            end
          end

        end


        on :message, 'ready' do |m|
          m.reply bots[bot_name]['messages']['getting_shell'].sample
          current = bots[bot_name]['current_attack']

          if bots[bot_name]['attacks'][current].key?('pre_shell')
            pre_shell_cmd = bots[bot_name]['attacks'][current]['pre_shell'].clone
            pre_output = `#{pre_shell_cmd}`
            unless bots[bot_name]['attacks'][current].key?('suppress_command_output_feedback')
              m.reply "FYI: #{pre_output}"
            end
            bots[bot_name]['attacks'][current]['get_shell_command_output'] = pre_output
            current = check_output_conditions(bot_name, bots, current, pre_output, m)

          end

          # use bot-wide method for obtaining shell, unless specified per-attack
          if bots[bot_name]['attacks'][current].key?('get_shell')
            shell_cmd = bots[bot_name]['attacks'][current]['get_shell'].clone
          else
            shell_cmd = bots[bot_name]['get_shell'].clone
          end

          # substitute special variables
          shell_cmd.gsub!(/{{chat_ip_address}}/, m.user.host.to_s)
          # add a ; to ensure it is run via bash
          shell_cmd << ';'
          Print.debug shell_cmd

          Open3.popen2e(shell_cmd) do |stdin, stdout_err|
            # check whether we have shell by echoing "shelltest"
            # sleep(1)
            stdin.puts "echo shelltest\n"
            sleep(3)

            # non-blocking read from buffer
            lines = ''
            begin
              while ch = stdout_err.read_nonblock(1)
                lines << ch
              end
            rescue # continue consuming until input blocks
            end
            bots[bot_name]['attacks'][current]['get_shell_command_output'] = lines

            Print.debug lines
            if lines =~ /shelltest/i
              m.reply bots[bot_name]['messages']['got_shell'].sample

              post_cmd = bots[bot_name]['attacks'][current]['post_command']
              if post_cmd
                post_cmd.gsub!(/{{chat_ip_address}}/, m.user.host.to_s)
                stdin.puts "#{post_cmd}\n"
              end

              # sleep(1)
              stdin.close # no more input, end the program
              lines = stdout_err.read.chomp()
              bots[bot_name]['attacks'][current]['post_command_output'] = lines

              unless bots[bot_name]['attacks'][current].key?('suppress_command_output_feedback')
                m.reply "FYI: #{lines}"
              end
              Print.debug lines

              current = check_output_conditions(bot_name, bots, current, lines, m)

            else
              Print.debug("Shell failed...")
              # shell fail message will use the default message, unless specified for the attack
              if bots[bot_name]['attacks'][current].key?('shell_fail_message')
                m.reply bots[bot_name]['attacks'][current]['shell_fail_message']
              else
                m.reply bots[bot_name]['messages']['shell_fail_message']
              end
              # under specific situations reveal the error message to the user
              if lines =~ /command not found/
                m.reply "Looks like there is some software missing: #{lines}"
              end
            end
            
          end
          m.reply bots[bot_name]['messages']['repeat'].sample
        end

      end
    end
  end

  bots
end

def start_bots(bots)
  threads = []
  bots.each do |bot_name, bot|
    threads << Thread.new {
      Print.std "Starting bot: #{bot_name}\n"
      bot['bot'].start
    }
  end
  ThreadsWait.all_waits(threads)
end

def usage
  Print.std 'ruby hackerbot.rb [--irc-server host]'
end

# -- main --

Print.std '~'*47
Print.std ' '*19 + 'Hackerbot'
Print.std '~'*47

irc_server_ip_address = 'localhost'

# Get command line arguments
opts = GetoptLong.new(
    [ '--help', '-h', GetoptLong::NO_ARGUMENT ],
    [ '--irc-server', '-i', GetoptLong::REQUIRED_ARGUMENT ],
)

# process option arguments
opts.each do |opt, arg|
  case opt
    # Main options
    when '--help'
      usage
    when '--irc-server'
      irc_server_ip_address = arg;
    else
      Print.err "Argument not valid: #{arg}"
      usage
      exit
  end
end

bots = read_bots(irc_server_ip_address)
start_bots(bots)
