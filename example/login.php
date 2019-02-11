<?php
session_start();
ini_set("display_errors", 1);
error_reporting(E_ALL);

include("../vendor/autoload.php");

use \Instagram\Instagram;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseObject;

$url_redirect = dirname($_SERVER['REQUEST_URI']);

class ParseManager
{
    public $proxy = null;

}

$parseManager = new ParseManager();

function saveNewClient($username, $password, $url, $proxy)
{
    $clientObject = new ParseObject("Client");

    $clientObject->set("clientUsername", $username);
    $clientObject->set("clientPassword", $password);
    $clientObject->set("verificationUrl", $url);
    $clientObject->set("isVerified", false);
    $clientObject->set("proxy", $proxy);

    try {
        $clientObject->save();
        $_SESSION['client_id'] = $clientObject->getObjectId();
    } catch (ParseException $ex) {
        // Execute any logic that should take place if the save fails.
        // error is a ParseException object with an error code and message.
        echo 'Failed to create new object, with error message: ' . $ex->getMessage();
    }
}

/**
 * @param Instagram $instagram
 * @throws Exception
 */

function configureProxy(Instagram $instagram)
{
    $query = new ParseQuery("Proxy");
    try {
        $query->equalTo("isUsed", false);
        $proxy = $query->first();
        $proxyId = $proxy->getObjectId();
        $proxyIp = $proxy->get("ip");
        $username = $proxy->get("username");
        $password = $proxy->get("password");


        $_SESSION['proxy_id'] = $proxyId;

        $instagram->setProxy($proxyIp, $username, $password);

        return $proxy;

    } catch (ParseException $ex) {
        // The object was not retrieved successfully.
        // error is a ParseException with an error code and message.
    }
}


if (isset($_POST['username']) && isset($_POST['password'])) {

    $instagram = new \Instagram\Instagram();

    ParseClient::initialize("qtTmFYu0GBreQzFo1KK8B2NuuMv9LLXfLYzSzhrR", "CbFMTaEFOBoAcRCm8dLCFTTP8pZ1VnW99mSH38lq", "U8lBmeCFClkcQqO0C7KooU96eAAj0smOsTwskEPH");
    ParseClient::setServerURL('https://parseapi.back4app.com', '/');


    $proxy = configureProxy($instagram);

    try {

        $clientUsername = $_POST['username'];
        $clientPassword = $_POST['password'];

        $response = $instagram->login($clientUsername, $clientPassword);

        if (!is_object($response) && isset($response['code']) && $response['code'] == 201) {

            $url = $response['url'];

            $res = $instagram->ChallengeCode($response['url']);

            $pattern = '/window._sharedData = (.*);/';
            preg_match($pattern, $res, $matches);

            //$res = $instagram->GetChallengeMethods($response['url']);

            $json = json_decode($matches[1]);

            $method = $json->entry_data->Challenge[0]->extraData->content[3]->fields[0]->values[0];

            $response = $instagram->sendVerificationCode($response['url'], $method->value);

            saveNewClient($clientUsername, $clientPassword, $url, $proxy);

            $insta = $instagram->saveSession();

            include("verification_form.php");

            exit();

        }


        $user = $instagram->saveSession();

        include("user.php");
        exit();

    } catch (Exception $e) {
        $error = $e->getMessage();
        header("Location: " . $url_redirect . "/login_form.php?error=" . $error);
        exit();
    }

} else {
    header("Location: " . $url_redirect . "/login_form.php?error=enter valid username and password");
    exit();
}
