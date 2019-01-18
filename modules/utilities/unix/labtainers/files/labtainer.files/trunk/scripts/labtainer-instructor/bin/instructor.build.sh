#!/bin/bash
: <<'END'
This software was created by United States Government employees at 
The Center for the Information Systems Studies and Research (CISR) 
at the Naval Postgraduate School NPS.  Please note that within the 
United States, copyright protection is not available for any works 
created  by United States Government employees, pursuant to Title 17 
United States Code Section 105.   This software is in the public 
domain and is not subject to copyright. 
END
./buildInstructorImage.sh formatstring
./buildInstructorImage.sh bufoverflow
./buildInstructorImage.sh onewayhash
./buildInstructorImage.sh telnetlab client
./buildInstructorImage.sh telnetlab server
./buildInstructorImage.sh httplab client
./buildInstructorImage.sh httplab server
./buildInstructorImage.sh vpnlab client
./buildInstructorImage.sh vpnlab server
./buildInstructorImage.sh vpnlab router
