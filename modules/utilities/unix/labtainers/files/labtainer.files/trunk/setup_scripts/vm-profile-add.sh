#s!/bin/bash
#
# Modify the user profile to create a terminal on login that
# starts in the labtainer workspace.  The profile will also, run the Labtainer
# update script if the labtainer/.doupdate file exists.
# This script also creates the .doupdate file and modifieds gnome
# to shutdown when the virtual powerbutton is pressed.
#
cat >>~/.profile <<EOL
gnome-terminal --geometry 120x31+150+300 --working-directory=$HOME/labtainer/labtainer-student -e "bash -c \"/bin/cat README; exec bash\"" &
if [[ -f $HOME/labtainer/.doupdate ]]; then
    gnome-terminal --geometry 73x31+100+300 --working-directory=$HOME/labtainer -x ./update-labtainer.sh
fi
if [[ -f $HOME/labtainer/.dosmoke ]]; then
    gnome-terminal --geometry 120x31+150+300 --working-directory=$HOME/labtainer/trunk/setup_scripts -e "bash -c \"exec bash -c ./full-smoke-test.sh \"" &
fi

EOL
touch $HOME/labtainer/.doupdate 
gsettings set org.gnome.settings-daemon.plugins.power button-power 'shutdown'
gsettings set org.gnome.nm-applet disable-disconnected-notifications "true"
gsettings set org.gnome.nm-applet disable-connected-notifications "true"
gsettings set org.gnome.desktop.session idle-delay 0
gsettings set org.gnome.desktop.screensaver lock-enabled false

cd $HOME/Desktop
ln -s $HOME/labtainer/trunk/docs/student/labtainer-student.pdf
ln -s ~/labtainer_xfer


