<?php
/**
 * Created by PhpStorm.
 * User: victor
 * Date: 2019-02-11
 * Time: 09:48
 */

namespace Instagram;

use Parse\ParseClient;
use Parse\ParseException;
use Parse\ParseQuery;

class ParseManager
{

    private $isConfigured = false;

    public function initParseManager()
    {
        if ($this->isConfigured) {
            return;
        }

        ParseClient::initialize("qtTmFYu0GBreQzFo1KK8B2NuuMv9LLXfLYzSzhrR", "CbFMTaEFOBoAcRCm8dLCFTTP8pZ1VnW99mSH38lq", "U8lBmeCFClkcQqO0C7KooU96eAAj0smOsTwskEPH");
        ParseClient::setServerURL('https://parseapi.back4app.com', '/');
        $this->isConfigured = true;
    }


    public function getAvailableProxy()
    {
        $query = new ParseQuery("Proxy");
        try {
            $query->equalTo("isUsed", false);
            $first = $query->first();

            return $first;

        } catch (ParseException $ex) {
            // The object was not retrieved successfully.
            // error is a ParseException with an error code and message.
        }

    }

    public function getProxyById($id)
    {
        $query = new ParseQuery("Proxy", $id);
        try {
            //$query->equalTo("isUsed", false);
            $first = $query->first();

            return $first;

        } catch (ParseException $ex) {
            // The object was not retrieved successfully.
            // error is a ParseException with an error code and message.
        }

    }
}