<?php

namespace App\Service;

use Google\Exception;
use Google_Client;
use Google_Service_Gmail;

class GoogleClient
{
    /**
     * Returns an authorized API client.
     *
     * @return Google_Client the authorized client object
     * @throws Exception
     */
    public static function getClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName('Gmail API PHP Quickstart');
        $client->setScopes([
            Google_Service_Gmail::GMAIL_READONLY,
            Google_Service_Gmail::GMAIL_SEND,
            Google_Service_Gmail::GMAIL_MODIFY
        ]);
        $credentialsPath = 'creds/credentials.json';
        if (file_exists($credentialsPath)) {
            $client->setAuthConfig($credentialsPath);
            $client->setAccessType('offline');
            $client->setPrompt('select_account consent');
        } else {
            $filename = 'tests/Feature/sample.txt';
            $fp = fopen($filename, 'r');
            $txt = "";
            while (!feof($fp)) {
                $txt .= str_replace("\n", "\r\n", fgets($fp));
            }
            dump(MailAnalysis::regex(1675852326, $txt));
            fclose($fp);
            exit();
        }

        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first
        // time.
        $tokenPath = 'creds/token.json';
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // If there is no previous token, or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
}
