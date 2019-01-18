capabilities.pdf: Capability_Exploration.tex Capability_Exploration.tex header.tex
	latex Capability_Exploration
	pdflatex -jobname=capabilities Capability_Exploration
	pdflatex -jobname=capabilities Capability_Exploration

clean:
	rm -fr auto
	rm -f Capability_Exploration.aux Capability_Exploration.log capabilities.pdf
