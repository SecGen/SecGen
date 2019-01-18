iptables-ics.pdf: iptables-ics.tex header.tex
	latex iptables-ics
	pdflatex -jobname=iptables-ics iptables-ics
	pdflatex -jobname=iptables-ics iptables-ics

clean:
	rm -fr auto
	rm -f iptables-ics.aux iptables-ics.log iptables-ics.pdf iptables-ics.dvi iptables-ics.out
