<?php
header("Content-Type: application/json; charset=UTF-8");

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpException;

require __DIR__ . './../model/model.php';

class RcsApi
{
    //get single phone by phoneID
    public static function getPhoneByPhoneID(Request $request, Response $response, $args)
    {
        try {
            $id = isset($args['phoneID']) ? $args['phoneID'] : '';
            $validateMac = util::cleanMacAddress($id);
            $rcsGetPhoneByID = RcsModel::getPhoneByPhoneID($validateMac);
            $response->getBody()->write(json_encode($rcsGetPhoneByID));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage = $httpException->getMessage();
            $statusCode   = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
    //get all phones
    public static function getAllPhones(Request $request, Response $response, $args)
    {
        try {
            $allPhones = RcsModel::getAllPhones();
            $response->getBody()->write(json_encode($allPhones));
            return $response;
        } catch (Exception $httpException) {
            // Handle the http exception here
            $throwMessage = $httpException->getMessage();
            $statusCode   = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
    //get phones by server id
    public static function getPhonesByServerID(Request $request, Response $response, $args)
    {
        try {
            $serverId = $args['serverId'];
            $rcsGetPhoneByServerID = RcsModel::getPhonesByServerID($serverId);
            $response->getBody()->write(json_encode($rcsGetPhoneByServerID));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage = $httpException->getMessage();
            $statusCode   = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
    //get account summary
    public static function getAccountSummary(Request $request, Response $response, $args)
    {
        try {
            $vid = $args['vendorId'];
            $getAccountSummary = RcsModel::getAccountSummary($vid);
            $response->getBody()->write(json_encode($getAccountSummary));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage   = $httpException->getMessage();
            $statusCode     = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }

    //delete phones by phone id
    public static function deletePhone(Request $request, Response $response, $args)
    {
        try {
            //$id = $args['phoneID'];
            $requestData = $request->getBody();
            $data = json_decode($requestData, true);

            //$validateMac = util::cleanMacAddress($id);
            $deletePhone = RcsModel::deletePhone($data);
            $response->getBody()->write(json_encode($deletePhone));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage  = $httpException->getMessage();
            $statusCode    = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
    //adding phone
    public static function addPhone(Request $request, Response $response, $args)
    {
        try {
            $requestData = $request->getBody();
            $data = json_decode($requestData, true);
            $addPhone = RcsModel::addPhone($data);
            $response->getBody()->write(json_encode($addPhone));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage  = $httpException->getMessage();
            $statusCode    = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
    //update phone details
    public static function updatePhone(Request $request, Response $response, $args)
    {
        try {
            //$pid = $args['phoneID'];
            //$validateMac = util::cleanMacAddress($pid);
            $requestData = $request->getBody();
            $data = json_decode($requestData, true);
            $updatePhone = RcsModel::updatePhone($data);
            $response->getBody()->write(json_encode($updatePhone));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage  = $httpException->getMessage();
            $statusCode    = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }


    public static function getToken(Request $request, Response $response, $args)
    {
        try {
            if (isset($args['phoneID'])) {
                $pid = $args['phoneID'];
                $validateMac = util::cleanMacAddress($pid);
            } else {
                $validateMac = '';
            }
            $getToken = RcsModel::getToken($validateMac);
            //return $getToken;
            $response->getBody()->write(json_encode($getToken));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage = $httpException->getMessage();
            $statusCode   = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }

    public static function login(Request $request, Response $response, $args)
    {
        try {
            $requestData = $request->getBody();
            $data = json_decode($requestData, true);
            $login = RcsModel::login($data);
            $response->getBody()->write(json_encode($login));
            return $response;
        } catch (Exception $httpException) {
            $throwMessage = $httpException->getMessage();
            $statusCode   = $httpException->getCode();
            $errorMessage["error"]        = $throwMessage;
            $errorMessage["status code"]  = $statusCode;
            $response->getBody()->write(json_encode($errorMessage));
            return $response->withStatus($statusCode);
        }
    }
}
