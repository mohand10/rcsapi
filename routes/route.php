<?php
require __DIR__ . './../controller/RcsApi.php';
class RcsRouter
{
    public static  function route($app)
    {
        $app->group('/api', function ($app) {
            $app->get('/getPhone[/{phoneID}]', 'RcsApi::getPhoneByPhoneID');
            $app->get('/allPhones', 'RcsApi::getAllPhones');
            $app->get('/phones/sid/{serverId}', 'RcsApi::getPhonesByServerID');
            $app->get('/accountSummary/{vendorId}', 'RcsApi::getAccountSummary');

            $app->post('/addPhone', 'RcsApi::addPhone');

            $app->delete('/deletePhone[/{phoneID}]', 'RcsApi::deletePhone');

            $app->put('/updatePhone', 'RcsApi::updatePhone');
        })->add(new JwtMiddleware);
        $app->post('/login', 'RcsApi::login');
    }
}
