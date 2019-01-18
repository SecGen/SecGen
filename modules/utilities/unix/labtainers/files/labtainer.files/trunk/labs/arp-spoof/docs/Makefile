arp-spoof.pdf: arp-spoof.tex header.tex
	latex arp-spoof
	pdflatex -jobname=arp-spoof arp-spoof
	pdflatex -jobname=arp-spoof arp-spoof

clean:
	rm -fr auto
	rm -f arp-spoof.aux arp-spoof.log arp-spoof.pdf arp-spoof.dvi arp-spoof.out
