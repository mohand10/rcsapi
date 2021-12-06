<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response;
//require __DIR__ . './../utils/util.php';

class JWTMiddleware
{

    public function __invoke(Request $request, RequestHandler $handler)
    {
        $response = $handler->handle($request);
        $existingContent = (string) $response->getBody();
        $response = new Response();

        $getHeaders = apache_request_headers();
        if (isset($getHeaders['Authorization'])) {
            $auth = $getHeaders['Authorization'];
            $noBearer = explode(' ', $auth);
            $token = $noBearer[1];

            $tokenParts = explode('.', $token);
            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];

            $key = "secret_key";

            $base64UrlHeader = util::base64url_encode($header);
            $base64UrlPayload = util::base64url_encode($payload);
            $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $key, true);
            $base64UrlSignature = util::base64url_encode($signature);
            $signatureValid = ($base64UrlSignature === $signatureProvided);
            if ($signatureValid) {
                $display['message'] = "signature is valid";
                $display['content'] = json_decode($existingContent, true);
            } else {
                $display['message']     = "signature is not valid";
                $display['status code'] = 401;
            }
        } else {
            //$display[] = array("message"=>"jwt error");
            $display['message']     = "JWT error";
            $display['status code'] = 401;
        }
        $response->getBody()->write(json_encode($display));

        return $response;
    }
}
