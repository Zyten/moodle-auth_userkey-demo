<?php
require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/');
$dotenv->load();
$dotenv->required([
        'MOODLE_WEBSERVICE_TOKEN',
        'MOODLE_DOMAIN_NAME',
        'EMAIL',
        'USERNAME',
        'FNAME',
        'LNAME',
        'COURSEID'
])->notEmpty();


/**
 * @param   string $useremail Email address of user to create token for.
 * @param   string $firstname First name of user (used to update/create user).
 * @param   string $lastname Last name of user (used to update/create user).
 * @param   string $username Username of user (used to update/create user).
 * @param   string $ipaddress IP address of end user that login request will come from (probably $_SERVER['REMOTE_ADDR']).
 * @param int      $courseid Course id to send logged in users to, defaults to site home.
 * @param int      $modname Name of course module to send users to, defaults to none.
 * @param int      $activityid cmid to send logged in users to, defaults to site home.
 * @return bool|string
 */
function getloginurl($useremail, $username, $firstname, $lastname, $courseid = null, $modname = null, $activityid = null) {
    require_once('curl.php');

    $token        = $_ENV['MOODLE_WEBSERVICE_TOKEN'];
    $domainname   = $_ENV['MOODLE_DOMAIN_NAME'];
    $functionname = 'auth_userkey_request_login_url';

    $param = [
        'user' => [
            'username'  => $username, 
        ]
    ];

    $serverurl = $domainname . '/webservice/rest/server.php' . '?wstoken=' . $token . '&wsfunction=' . $functionname . '&moodlewsrestformat=json';
    $curl = new curl; // The required library curl can be obtained from https://github.com/moodlehq/sample-ws-clients 


    try {
        $resp     = $curl->post($serverurl, $param);
        $resp     = json_decode($resp);
        if ($resp && !empty($resp->loginurl)) {
            $loginurl = $resp->loginurl;        
        }
    } catch (Exception $ex) {
        return $ex;;
    }

    if (!isset($loginurl)) {
        return "Failed";
    }

    $path = '';
    if (isset($courseid)) {
        $path = '&wantsurl=' . urlencode("$domainname/course/view.php?id=$courseid");
    }
    if (isset($modname) && isset($activityid)) {
        $path = '&wantsurl=' . urlencode("$domainname/mod/$modname/view.php?id=$activityid");
    }

    return $loginurl . $path;
}

echo getloginurl($_ENV['EMAIL'], $_ENV['USERNAME'], $_ENV['FNAME'], $_ENV['LNAME'], $_ENV['COURSEID']);
