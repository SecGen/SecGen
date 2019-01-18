all: InstallingVB-LinuxWin.pdf labtainer-student.pdf

InstallingVB-LinuxWin.pdf: InstallingVB-LinuxWin.docx
	soffice --convert-to pdf InstallingVB-LinuxWin.docx --headless

labtainer-student.pdf: InstallingVB-LinuxWin.pdf labtainer-student.tex
	latex labtainer-student
	pdflatex labtainer-student
clean:
	rm -fr auto
	rm -fr labtainer-student.aux labtainer-student.pdf *.ps *.dvi
