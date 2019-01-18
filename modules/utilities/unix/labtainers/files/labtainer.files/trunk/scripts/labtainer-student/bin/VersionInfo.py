import subprocess
import os
def getFrom(dockerfile, registry):
    ''' given a docker file and registry, get the base image name, qualified by the registry '''
    image_name = None
    with open(dockerfile) as fh:
        for line in fh:
            if line.strip().startswith('FROM'):
                parts = line.strip().split()
                image_name = parts[1]
                image_name = image_name.replace("$registry", registry).strip()
                if image_name.endswith('.xtra'):
                    image_name = image_name[:len(image_name)-5]
                break
    ''' Remove xtra suffix if it exists.  We are only interested in the big base '''
    return image_name

def getImageId(image, quiet):
    ''' given an image name, use docker to determine the image ID present on this installation '''
    #cmd = 'docker images | grep %s' % image
    cmd = 'docker images -f=reference="%s:latest" -q ' % image
    ps = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE,stderr=subprocess.PIPE)
    output = ps.communicate()
    if len(output[1]) > 0:
        print(output[1])
        exit(1)
    if len(output[0]) > 0:
        return output[0].strip()
    elif quiet:
        cmd = 'docker pull %s' % image
        os.system(cmd)
    else:
        print('VersionInfo, getImageId: no image found for %s' % image)
        print('**************************************************')
        print('*  This lab will require a download of           *')
        print('*  several hundred megabytes.                    *')
        print('**************************************************')
        confirm = str(raw_input('Continue? (y/n)')).lower().strip()
        if confirm != 'y':
            print('Exiting lab')
            exit(0)
        else:
            print('Please wait for download to complete...')
            cmd = 'docker pull %s' % image
            os.system(cmd)
            print('Download has completed.  Wait for lab to start.')
            return getImageId(image, quiet)

