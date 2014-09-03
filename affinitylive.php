<?php

	require("config.php"); // for clientID, clientSecret, clientURL

?>


<?php
function authorize($clientURL, $clientID, $clientSecret)
{
        $url = $clientURL."/oauth2/v0/token";
        $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $url,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array("response_type" => 'code',
                "grant_type" => 'client_credentials',
                "client_id" => $clientID,
                "client_secret" => $clientSecret )
        ));

        $resp = curl_exec($curl);
        curl_close($curl);
        $response = json_decode($resp);

        if(@$response->error=='invalid_client')
        {
        $response = 'false';
        }
        return $response;
}
        function createContact($clientURL, $token, $firstname, $lastname, $email, $phone, $selection, $companyID)
        {
                $contacturl = $clientURL."/api/v0/contacts";
                $username = randomWord();
                $password = randomWord();

                $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $contacturl,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array("company_id" => $companyID,
                "_bearer_token" => $token,
                "firstname" => $firstname,
                "surname" => $lastname,
                "email" => $email,
                "phone" => $phone,
                "comments" => $selection,
                "username" => $username,
                "password" => $password )
                ));

                $resp = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($resp);

                if(@$response->meta->status != 'ok')
                {
                        $response = 'false';
                }
                return $response;

        }
        function getContact($clientURL, $token, $contactID)
        {
                $contacturl = $clientURL."/api/v0/contacts/".$contactID."?_bearer_token=".$token."&_fields=default_affiliation";

                $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $contacturl
                ));

                $resp = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($resp);

                if(@$response->meta->status != 'ok')
                {
                        $response = 'false';
                }
                return $response;

        }

        function createProspect($clientURL, $token, $affiliation_id, $companyname)
        {
                $companyurl = $clientURL."/api/v0/prospects/";

                $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $companyurl,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array(
                "_bearer_token" => $token,
                "affiliation_id" => $affiliation_id,
                "title" => $companyname)
                ));

                $resp = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($resp);

                return $response;

        }

        function getProspects($clientURL, $token)
        {
                $companyurl = $clientURL."/api/v0/prospects/?_bearer_token=".$token;

                $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $companyurl)
                );

                $resp = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($resp);

                return $response;

        }

        function createCompany($clientURL, $token, $companyname)
        {
                $companyurl = $clientURL."/api/v0/companies";

                $curl = curl_init();
                curl_setopt_array($curl, array( CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $companyurl,
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array(
                "_bearer_token" => $token,
                "standing" => "interested",
                "name" => $companyname)
                ));

                $resp = curl_exec($curl);
                curl_close($curl);
                $response = json_decode($resp);

                return $response;

        }

        function randomWord() {
                $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789";
                $pass = array();
                $alphaLength = strlen($alphabet) - 1;
                for ($i = 0; $i < 8; $i++) {
                        $n = rand(0, $alphaLength);
                        $pass[] = $alphabet[$n];
                }
                return implode($pass);
        }


?>
<?php
if($_POST)
{
    $result = authorize($clientURL, $clientID, $clientSecret);
    $firstname = $_POST['contact_firstname'];
    $lastname = $_POST['contact_surname'];
    $email = $_POST['email'];
    $phone = $_POST['phone_number'];
    $companyname = $_POST['company_name'];

    if(!empty($_POST['selection'])){
            $selection = implode("; ", $_POST['selection']);
    }

    $companyresult = createCompany($clientURL, $result->access_token, $companyname);
    $companyID = $companyresult->response->id;


    $contactresult = createContact($clientURL, $result->access_token, $firstname, $lastname, $email, $phone, $selection, $companyID);

    // var_dump($contactresult);

    if( $contactresult->meta->status == "ok"){
        $fullcontactresult = getContact($clientURL, $result->access_token, $contactresult->response->id);

        //var_dump($fullcontactresult);

        if( $fullcontactresult->meta->status == "ok" &&
            $fullcontactresult->response->default_affiliation != null ){
         
            // temporary while we figure out prospects
            $message = "Thank you for contacting us! We will be in touch with you shortly.";
            
            // get
            //$prospectresult = getProspects($clientURL, $result->access_token);

            // post
            //$prospectresult = createProspect($clientURL, $result->access_token, $fullcontactresult->response->default_affiliation, $companyname);
            // var_dump($prospectresult);


            // if( $prospectresult->meta->status == "ok" ){
            //     $message = "Thank you for contacting us! We will be in touch with you shortly.";
            // }
        }
    }

    if( $message == null ) {
        $message = "We were unable to send this form. Please contact us via phone or email.";
    }

    // if( !defined($prospectresult) ) {
    //     $message = "We were unable to send this form. Please contact us via phone or email.";
    // }

    echo $message;
}