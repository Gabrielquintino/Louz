<?php
namespace App\Models;

use Exception;

class ZapiModel {    

    public function generateQrCode( string $pInstancia, string $pToken ) : string {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.z-api.io/instances/' . $pInstancia . '/token/' . $pToken . '/qr-code/image',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'GET',
          CURLOPT_CAINFO => 'C:/certificados/certificado.crt',
          CURLOPT_SSL_VERIFYPEER => false
        ));
        
        $response = curl_exec($curl);

        if (!empty($response)) {
            $objResponse = json_decode($response);
            curl_close($curl);
            return $objResponse->value;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }

    public function getInstanceData(string $pInstancia, string $pToken) : object {

        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.z-api.io/instances/'. $pInstancia .'/token/' . $pToken . '/device',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_CAINFO => 'C:/certificados/certificado.crt',
        CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);

        $objResponse = json_decode($response);

        curl_close($curl);
        
        return $objResponse;
        
    }

    public function disconnectInstance(string $pInstancia, string $pToken) : bool {
        $curl = curl_init();

        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.z-api.io/instances/' .$pInstancia. '/token/' . $pToken . '/disconnect',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_CAINFO => 'C:/certificados/certificado.crt',
        CURLOPT_SSL_VERIFYPEER => false
        ));

        $response = curl_exec($curl);

        curl_close($curl);


        try {
            $objResponse = json_decode($response);
            return $objResponse->value;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function sendMessage(string $pInstancia, string $pToken, string $pClientToken, string $pPhone, string $pMessage) : bool {
        $curl = curl_init();

        curl_setopt_array($curl, array(
          CURLOPT_URL => 'https://api.z-api.io/instances/'.$pInstancia.'/token/'.$pToken.'/send-text',
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => '',
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 0,
          CURLOPT_FOLLOWLOCATION => true,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => 'POST',
          CURLOPT_POSTFIELDS =>'{
          "phone": "'.$pPhone.'",
          "message": "'.$pMessage.'"
        }',
          CURLOPT_HTTPHEADER => array(
            'Client-Token: '.$pClientToken.'',
            'Content-Type: application/json'
          ),
        ));
        
        $response = curl_exec($curl);

        $objResponse = json_decode($response);

        if (!empty($response)) {
            $objResponse = json_decode($response);
            var_dump($objResponse);
            curl_close($curl);
            return $objResponse->value;
        } else {
            throw new Exception("Error Processing Request", 1);
        }
    }
}