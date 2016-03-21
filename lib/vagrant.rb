require_relative 'filecreator.rb'

class VagrantController

	# Executes vagrant up for the specified build
  # @param [Int] build_number to execute vagrant up on
	def vagrant_up(build_number)
		#executes vagrant up from the current build.
		puts 'Building now.....'
		command = "cd #{PROJECTS_DIR}/Project#{build_number}/; vagrant up"
		exec command 
	end
end
