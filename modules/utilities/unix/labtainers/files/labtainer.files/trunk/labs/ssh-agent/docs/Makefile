ssh-agent.pdf: ssh-agent.tex header.tex
	latex ssh-agent
	pdflatex -jobname=ssh-agent ssh-agent
	pdflatex -jobname=ssh-agent ssh-agent

clean:
	rm -fr auto
	rm -f ssh-agent.aux ssh-agent.log ssh-agent.pdf ssh-agent.dvi ssh-agent.out
