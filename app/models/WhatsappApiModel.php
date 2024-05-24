<?php
namespace App\Models;

use Exception;

class WhatsappApiModel {    

    public function generateQrCode( string $pInstancia = null, string $pToken ) : string {
        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://localhost:3000/'.$pToken.'/qrcode',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);

        if (!empty($response)) {

            try {
                $objResponse = json_decode($response);
                curl_close($curl);

                if (property_exists($objResponse, 'data')) {
                    return $objResponse->data;
                } else {
                    http_response_code(500);
                }
            } catch (Exception $err) {
                http_response_code(500);

                throw $err;
            }

        } else {
            http_response_code(500);
        }
    }

    public function getInstanceData(string $pInstancia, string $pToken) : object {

        $curl = curl_init();
        
        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://localhost:3000/'.$pToken.'/getClientInformation',            
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);

        if (!empty($response)) {
            try {
                $objResponse = json_decode($response);
                curl_close($curl);
                return $objResponse;
            } catch (\Throwable $th) {
                throw new Exception("Error Processing Request", 1);
            }

        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }

    public function disconnectInstance(string $pInstancia, string $pToken) : bool {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'http://localhost:3000/'.$pToken.'/disconnectInstance',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        
        $response = curl_exec($curl);
        
        curl_close($curl);

        try {
            $objResponse = json_decode($response);
            return $objResponse;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function sendTextMessage(string $pToken, string $pPhone, string $pMessage) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://localhost:3000/'.$pToken.'/sendTextMessage',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "phone": "'.$pPhone.'",
            "message": "'.$pMessage.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Client-Token: Fd6b1737dd30b461e8c92934b6ea28247S',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (!empty($response)) {
            $objResponse = json_decode($response);
            curl_close($curl);
            return $objResponse;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }

    public function sendFile(string $pToken, string $pPhone, string $pUrl) {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => 'http://localhost:3000/'.$pToken.'/sendFile',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
            "phone": "'.$pPhone.'",
            "url": "'.$pUrl.'"
            }',
            CURLOPT_HTTPHEADER => array(
                'Client-Token: Fd6b1737dd30b461e8c92934b6ea28247S',
                'Content-Type: application/json'
            ),
        ));

        $response = curl_exec($curl);

        if (!empty($response)) {
            $objResponse = json_decode($response);
            curl_close($curl);
            return $objResponse;
        } else {
            throw new Exception("Error Processing Request", 1);
        }

    }

    public function getChatById(string $pInstancia, string $pChatId) {
        
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'http://localhost:3000/'.$pInstancia.'/getChatById',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_POSTFIELDS =>'{
        "chatId": "'.$pChatId.'@c.us"
        }',
        CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
        ));

        $response = curl_exec($curl);

        if (!empty($response)) {

            try {
                $objResponse = json_decode($response);
                curl_close($curl);
                return $objResponse;
            } catch (Exception $err) {
                throw $err;
            }

        }

        curl_close($curl);
        echo $response;

    }
}