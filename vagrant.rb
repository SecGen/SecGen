class VagrantController

	def vagrant_up(build_number)
		#executes vagrant up from the current build.
		p 'building now.....'
		command = "cd #{PROJECTS_DIR}/Project#{build_number}/; vagrant up"
		exec command 
	end
end