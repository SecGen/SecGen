// Locally: phantomjs fake_admin_browser.js --url test.com
// Heroku: /app/vendor/phantomjs/bin/phantomjs fake_admin_browser.js --url test.com

var system = require('system');
var base_url = "http://localhost:8888/";
var password = "";
var profileid = 0;

if (system.args.length === 1) {
    console.log('Try to pass some args when invoking this script!');
} else {
    system.args.forEach(function (arg, i) {
        if (i == 2) {
            base_url = arg;
        }
        if (i == 4) {
            password = arg;
        }
        if (i == 6) {
            profileid = arg;
        }
    });
}

function open_target_profile(profileid) {
    var userprofilepage = require('webpage').create();
    userprofilepage.onAlert = function(alertmsg) {
        console.log(alertmsg);
    }
    userprofilepage.open(base_url+"index.php?id="+profileid, function (status) {
        setTimeout(function(){
            phantom.exit(0);
        }, 3000);
    });
}

var loginpage = require('webpage').create();
loginpage.open(base_url+'authenticate.php', 'post', 'username=admin&password='+password+'&save=OFF', function (status) {

    open_target_profile(profileid);
});
