<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 2019-03-21
 * Time: 14:35
 */


ini_set("display_errors", 1);
error_reporting(E_ALL);

include("../../vendor/autoload.php");

use \Instagram\Instagram;
use \Instagram\ParseManager;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseObject;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$data = json_decode(file_get_contents("php://input"));


if (
    !empty($data->username) &&
    !empty($data->password)
) {

    $parseManager = new ParseManager();

    $instagram = new Instagram();

    ParseClient::initialize("qtTmFYu0GBreQzFo1KK8B2NuuMv9LLXfLYzSzhrR", "CbFMTaEFOBoAcRCm8dLCFTTP8pZ1VnW99mSH38lq", "U8lBmeCFClkcQqO0C7KooU96eAAj0smOsTwskEPH");
    ParseClient::setServerURL('https://parseapi.back4app.com', '/');


    $proxy = configureProxy($instagram);

    try {

        $clientUsername = $data->username;
        $clientPassword = $data->password;

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

            $session = $instagram->saveSession();

            $clientId = saveNewClient($clientUsername, $clientPassword, $url, $proxy, $session);

            http_response_code(200);

            echo json_encode(array("message" => "Verification code sent!", "client_id" => $clientId, "verification_url" => $url));

        } else {
            //TODO THIS IS WHEN USER CAN LOG IN AND THE PROXY DOES NOT REQUIRE 2 FACTOR AUTH

            http_response_code(200);

            echo json_encode(array("message" => "No need to validate user", "data" => $data));
        }


//        $user = $instagram->saveSession();
//
//        include("user.php");
//        exit();
//
    } catch (Exception $e) {
        $error = $e->getMessage();
        http_response_code(400);
        echo json_encode(array("message" => $error));
    }

} else {

    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Username and password are missing"));

}


function saveNewClient($username, $password, $url, $proxy, $session)
{
    $clientObject = new ParseObject("Client");

    $clientObject->set("clientUsername", $username);
    $clientObject->set("clientPassword", $password);
    $clientObject->set("verificationUrl", $url);
    $clientObject->set("isVerified", false);
    $clientObject->set("proxy", $proxy);
    $clientObject->set("session", $session);

    try {
        $clientObject->save();
        $_SESSION['client_id'] = $clientObject->getObjectId();
        return $clientObject->getObjectId();
    } catch (ParseException $ex) {
        // Execute any logic that should take place if the save fails.
        // error is a ParseException object with an error code and message.

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => 'Failed to create new object, with error message: ' . $ex->getMessage()));
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


?>