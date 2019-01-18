dmz-example.pdf: dmz-lab.tex header.tex
	latex dmz-lab
	pdflatex -jobname=dmz-lab dmz-lab
	pdflatex -jobname=dmz-lab dmz-lab

clean:
	rm -fr auto
	rm -f dmz-lab.aux dmz-lab.log dmz-lab.pdf dmz-lab.dvi dmz-lab.out
