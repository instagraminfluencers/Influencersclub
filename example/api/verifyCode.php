<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 2019-03-21
 * Time: 15:19
 */
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


include("../../vendor/autoload.php");

use \Instagram\Instagram;
use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseQuery;
use Parse\ParseObject;


$data = json_decode(file_get_contents("php://input"));


if (
    !empty($data->client_id) &&
    !empty($data->verification_code) &&
    !empty($data->verification_url)
) {


    try {

        $instagram = new Instagram();

        ParseClient::initialize("qtTmFYu0GBreQzFo1KK8B2NuuMv9LLXfLYzSzhrR", "CbFMTaEFOBoAcRCm8dLCFTTP8pZ1VnW99mSH38lq", "U8lBmeCFClkcQqO0C7KooU96eAAj0smOsTwskEPH");
        ParseClient::setServerURL('https://parseapi.back4app.com', '/');

        $clientId = $data->client_id;
        $currentClient = getClient($data->client_id);


        $url = $data->verification_url;


        $verificationCode = $data->verification_code;
        $proxyId = $currentClient->get("proxy")->getObjectId();
        $session = $currentClient->get("session");

        $proxy = configureProxy($instagram, $proxyId);

        $instagram->initFromSavedSession($session);

        $response = $instagram->ConfirmVerificationCode($url, $verificationCode);


        if ($response->status == "ok") {

            $user = json_decode($instagram->saveSession());
            $userId = $user->cookies->ds_user_id;
            $user->loggedInUser['pk'] = $userId;

            saveNewClient($user, $clientId);

            //Mark the proxy that is already used
            $proxy->set("isUsed", true);
            $proxy->save();

            // set response code - 400 bad request
            http_response_code(200);

            // tell the user
            echo json_encode(array("message" => "User Verified!", "user" => $user));

        } else {
            //Verification Code is wrong
            $insta = $session;
            $url = $request->url;
            $response = json_decode($request->url_obj);
            $method = json_decode($request->method_obj);
            $pass = $request->pass;

            // set response code - 400 bad request
            http_response_code(400);

            // tell the user
            echo json_encode(array("message" => "Check the verification code."));

        }


    } catch (\Exception $e) {

        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "Something went wrong, error: $e"));
    }


} else {

    // set response code - 400 bad request
    http_response_code(400);

    // tell the user
    echo json_encode(array("message" => "Client code and verification code are missing"));

}


function saveNewClient($instaInfo, $clientId)
{
    $clientObject = new ParseObject("Client", $clientId);

    $clientObject->set("instaInfo", $instaInfo);
    $clientObject->set("isVerified", true);
    try {
        $clientObject->save();
    } catch (ParseException $ex) {
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
function configureProxy(Instagram $instagram, $proxyId)
{
    $query = new ParseQuery("Proxy", $proxyId);
    try {
        $query->equalTo("isUsed", false);
        $first = $query->first();

        $proxyIp = $first->get("ip");
        $username = $first->get("username");
        $password = $first->get("password");

        $instagram->setProxy($proxyIp, $username, $password);

        return $first;

    } catch (ParseException $ex) {
        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "configureProxy"));
    }
}

function getClient($clientId)
{

    $query = new ParseQuery("Client", $clientId);

    try {

        $query->equalTo("objectId", $clientId);
        $first = $query->first();

        $session = $first->get("session");
        $proxy = $first->get("proxy")->getObjectId();

//        http_response_code(200);
//
//        // tell the user
//        echo json_encode(array("message" => "Client ID Validated", "session" => $first->get("session"), "proxy" => $proxy, "client" => $first));

        return $first;

    } catch (ParseException $ex) {
        // The object was not retrieved successfully.
        // error is a ParseException with an error code and message.
        // set response code - 400 bad request
        http_response_code(400);

        // tell the user
        echo json_encode(array("message" => "ClientID is invalid"));
    }
}


?>

