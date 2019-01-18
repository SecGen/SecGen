Labtainer Regression Testing Guide
==================================

This manual is intended for use by lab designers wanting
to perform regression testing, i.e., verify labs previously
created will have the same result (grades.txt) after code changes
(for example code changes to instructor's side related to grading, etc.)

Regression Testing root (TESTSETS_ROOT) directory will be located in
directory ../../../testsets/labs (this is relative to the script
regresstest.py that is located in ...../labtainer/trunk/scripts/labtainer-instructor

Under the TESTSETS_ROOT directory, there will be directory for each labs.
In each of the lab directories, there will be zip files and corresponding '<labname>.grades.txt'

The regresstest.py script will use the zip files for each lab,
spawns the corresponding instructor's containers for the lab,
the zip files are copied into the instructor's container,
then the 'instructor.py' script is ran to create/generate the '<labname>.grades.txt'
The generated grade file is compared to the one stored in the TESTSETS_ROOT for that lab,
if they are the same, then the regression test for that lab is considered successful.
otherwise, it is considered as failure and regresstest.py script will terminate.

1. Preparing the test sets or Populating the TESTSETS_ROOT directory

   The zip files that are stored in the TESTSETS_ROOT directory are obtained
by running the lab as a student, i.e., performing the tasks required by the lab
as a student. Once the container for the lab is stopped, the zip files for the lab
is created (and stored in the host transfer directory).

   The zip files must be copied to the TESTSETS_ROOT directory.

Note: It might be easier to perform each task for a lab as separate user (i.e., using
      different e-mail addresses).
      For example: for the formatstring lab, crash the vulnerable program as joe@nps.edu
                   stop the container and save the zip file
                   then, modify the secret1 value as ann@nps.edu
                   stop the container and save the zip file
      The zip file will have the e-mail address as part of the name

2. Creating the 'GOLD' grades.txt file

   Once the zip files are created from step 1 above, perform the grading using the
corresponding instructor's container for the lab (by running the 'instructor.py' script)

The 'instructor.py' script will create the '<labname>.grades.txt' file for each 'student'
corresponding to the e-mail addresses found as part of the zip files.

Verify that the grades file generated is correct.
Once the grades file is verified as correct, when the instructor's container is stopped,
the grades file (<labname>.grades.txt) is copied to the host transfer directory.

This grades file must then be copied into the TESTSETS_ROOT directory for that lab
and will be treated as the 'GOLD' grades.txt file, i.e., when regresstest.py is re-run
(after some code changes), the generated grades file will be compared against that GOLD file

3. Running Regression Testing

   To run the regression testing (if there is any change to the code), it is simply done
by running the 'regresstest.py' script.

The regresstest.py script maybe given one argument (reflecting a specific labname) to test
or no argument (in which case all labs will be tested).


   




