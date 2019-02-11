<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include("../vendor/autoload.php");

use \Instagram\Instagram;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseObject;

$url_redirect = dirname($_SERVER['REQUEST_URI']);


function saveNewClient($instaInfo)
{
    $clientId = $_SESSION['client_id'];
    $clientObject = new ParseObject("Client", $clientId);

    $clientObject->set("instaInfo", $instaInfo);
    $clientObject->set("isVerified", true);
    try {
        $clientObject->save();
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
    $proxyId = $_SESSION['proxy_id'];
    $query = new ParseQuery("Proxy", $proxyId);
    try {
        //$query->equalTo("isUsed", false);
        $first = $query->first();

        $proxyIp = $first->get("ip");
        $username = $first->get("username");
        $password = $first->get("password");

        $instagram->setProxy($proxyIp, $username, $password);

        return $first;

    } catch (ParseException $ex) {
        // The object was not retrieved successfully.
        // error is a ParseException with an error code and message.
    }
}

try {

    $instagram = new \Instagram\Instagram();
    ParseClient::initialize("qtTmFYu0GBreQzFo1KK8B2NuuMv9LLXfLYzSzhrR", "CbFMTaEFOBoAcRCm8dLCFTTP8pZ1VnW99mSH38lq", "U8lBmeCFClkcQqO0C7KooU96eAAj0smOsTwskEPH");
    ParseClient::setServerURL('https://parseapi.back4app.com', '/');

    $proxy = configureProxy($instagram);

    $method = json_decode($_POST['method_obj']);
    $session = $_POST['insta'];

    $instagram->initFromSavedSession($session);
    $response = $instagram->ConfirmVerificationCode($_POST['url'], $_POST['code']);

    if ($response->status == "ok") {

        $user = json_decode($instagram->saveSession());
        $userId = $user->cookies->ds_user_id;
        $user->loggedInUser['pk'] = $userId;

        saveNewClient($user);
        //Mark the proxy that is already used
        $proxy->set("isUsed", true);
        $proxy->save();


        //Everything is great!
        include("user.php");
        exit();

    } else {
        //Verification Code is wrong
        $insta = $session;
        $url = $request->url;
        $response = json_decode($request->url_obj);
        $method = json_decode($request->method_obj);
        $pass = $request->pass;
        $error = "Check the verification code.";

        header("Location: " . $url_redirect . "/login_form.php?error=" . $error);
        exit();

    }

} catch (\Exception $e) {
    $error = json_encode($savedSession);
    header("Location: " . $url_redirect . "/login_form.php?error=" . $error);
    exit();
}

?>
