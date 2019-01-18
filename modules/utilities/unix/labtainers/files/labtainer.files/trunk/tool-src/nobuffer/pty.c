#define _XOPEN_SOURCE 600
#include <stdio.h>
#include <stdlib.h>
#include <fcntl.h>
#include <unistd.h>
#include <errno.h>
#include <sys/types.h>
#include <sys/wait.h>
#include <signal.h>
int child_pid;
FILE *masterfp;
int child_retval;
int dobuf(){
    //fprintf(stderr, "in dobuf\n");
    char line[BUFSIZ];
    while (fgets(line, BUFSIZ, masterfp) != NULL) {
      /* convert ending \r\n to \n */
      int i;
      for (i = 0; line[i]; i++) {
	if (line[i] == '\r' && line[i+1] == '\n' && line[i+2] == '\0') {
	  line[i] = '\n';
	  line[i+1] = '\0';
	}
      }
      fputs(line, stdout);
    }
    wait(&child_retval);

    if (child_retval) {
      fprintf(stdout, "\nProgram terminated with exit code %d.\n", child_retval);
    }
    return child_retval;
}
void sig_handler(int signo)
{
   if (signo == SIGINT)
   {
       // TBD, just mask it
       //fprintf(stderr, "got sigint, send to %d\n", child_pid);
       //kill(child_pid, SIGINT);
       //fprintf(stderr, "back from kill\n");
       dobuf();
   }
}

int main (int argc, char * const argv[]) {
  int masterfd, slavefd;
  char *slavedevice;

  int child_retval;
  char line[BUFSIZ];

  if (argc < 2) {
    fprintf(stderr, "Usage: ./pty <program> [<arg>*]\n");
    return 0;
  }
  masterfd = posix_openpt(O_RDWR|O_NOCTTY);
  if (masterfd == -1
      || grantpt(masterfd) == -1
      || unlockpt(masterfd) == -1
      || (slavedevice = ptsname(masterfd)) == NULL) {
    fprintf(stderr, "Unable to open pty.\n");
    return -1;
  }

  if ((child_pid = fork()) == -1) {
    fprintf(stderr, "Unable to fork.\n");
    return -1;
  }

  if (child_pid) {

    /* open master end of pty and unbuffer stdout */
    setvbuf(stdout, (char *) NULL, _IONBF, 0);
    masterfp = fdopen(masterfd, "r");
    signal(SIGINT, sig_handler);
    //fprintf(stderr, "call to dobuf\n");
    dobuf();
  } else {
    /* open slave end of pty */
    slavefd = open(slavedevice, O_RDWR|O_NOCTTY);
    if (slavefd < 0) {
      fprintf(stderr, "Unable to open slave end.\n");
      return -1;
    }
    dup2(slavefd, 1); /* replace stdout with slave end of pty */
    close(masterfd);
    close(slavefd);

    execvp(argv[1], &argv[1]);

    /* if execv returns, then it failed to execute the program */
    fprintf(stderr, "%s: command not found", argv[1]);
    return -1;
  }

  return 0;
}
