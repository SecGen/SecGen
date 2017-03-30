# XSS Cookie Stealing Challenge

Challenge: See if you can become logged in as the "admin" user.

Note that to do so, you'll need to create your own account and create an XSS attack on your user profile.

For purposes of this challenge, anything you successfully "alert()" in the admin's browser will be passed along to you. (Admin browser is simulated using phantomjs)

Deploy to your own Heroku instance with this button below, or try out our live demo [HERE](https://ctf-xss-challenge.herokuapp.com/) (not guaranteed to be up).

[![Deploy](https://www.herokucdn.com/deploy/button.png)](https://heroku.com/deploy)

Note that useful information for testing and debugging will be logged to the Papertrail app in your heroku instance. Open papertrail to view those streaming logs.
