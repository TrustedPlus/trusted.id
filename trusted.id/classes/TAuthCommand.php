<?php

namespace Trusted\Id;
use Bitrix\Main\Config\Option;

require_once __DIR__ . '/../config.php';
require_once TR_ID_MODULE_PATH . '/classes/Utils.php';

session_start();

class TAuthCommand {

    static protected function getToken(&$curl, &$response) {
        $res = null;
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                $res = json_decode($response, true);
            } else {
                $message = 'Wrong HTTP response status ' . $info['http_code'];
                if ($response) {
                    $error = json_decode($response, true);
                    if ($error) {
                        $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                    }
                }
                Utils::debug('OAuth request error', $message);
                throw new OAuth2Exception($message, 0, null);
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            Utils::debug('CURL error', $error);
            throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
        }
        return $res;
    }

    static function getAccessTokenByCode($code) {
        Utils::debug('Run: getAccessTokenByCode');
        $AG = new AuthorizationGrant();
        $AG->setCode($code);
        $params = $AG->jsonSerialize();
        //$params['prompt'] = 'login';
        $url = TR_ID_COMMAND_URI_TOKEN;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $AG->getClientId() . ':' . $AG->getClientSecret());

        curl_setopt($curl, CURLOPT_URL, $url);
        Utils::debug('CURL url:', $url);
        curl_setopt($curl, CURLOPT_POST, true);
        $post_fields = urldecode(http_build_query($params));
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_fields);
        Utils::debug('CURL post fields:', $post_fields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_SSLVERSION, TR_ID_SSL_VERSION);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $res = TAuthCommand::getToken($curl, $response);
        return $res;
    }

    static function getAccessTokenByRefreshToken($refresh_token) {
        $params = array(
            'grant_type' => 'refresh_token',
            'refresh_token' => $refresh_token, //Refresh token from the approval step.
            'format' => 'json' //Expected return format. This parameter is optional. The default is json. Values are: [urlencoded, json, xml]
        );
        $url = TR_ID_COMMAND_URI_TOKEN;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, TR_ID_OPT_CLIENT_ID . ':' . TR_ID_OPT_CLIENT_SECRET);

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, urldecode(http_build_query($params)));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSLVERSION, TR_ID_SSL_VERSION);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($curl);
        $res = TAuthCommand::getToken($curl, $response);

        return $res;
    }

    static function getUserProfileByToken($accessToken) {
        $response = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, TR_ID_COMMAND_URI_USERPROFILE);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSLVERSION, TR_ID_SSL_VERSION);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $res = json_decode($result, true);
                } else {
                    $message = 'Wrong HTTP response status ' . $info['http_code'];
                    if ($result) {
                        $error = json_decode($result, true);
                        if ($error) {
                            $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                        }
                    }
                    Utils::debug('OAuth request error', $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            } else {
                $error = curl_error($curl);
                curl_close($curl);
                Utils::debug('CURL error', $error);
                throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
            }
            curl_close($curl);
            // TODO: wrong $result
            $response = json_decode($result, true);
        }
        $res = null;
        if ($response['success']) {
            $res = $response['user'];
        }
        return $res;
    }

    static function checkTokenExpiration($accessToken) {
        Utils::debug('access token', $accessToken);
        $res = false;
        $version = TR_ID_OPT_SERVICE_VERSION;
        $requestType = strcmp($version, "1.3") == 0 ? 'GET' : 'POST';
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, TR_ID_OPT_CLIENT_ID . ':' . TR_ID_OPT_CLIENT_SECRET);
            curl_setopt($curl, CURLOPT_URL, TR_ID_COMMAND_URI_CHECK_TOKEN . '?token=' . $accessToken);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $requestType);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSLVERSION, TR_ID_SSL_VERSION);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                curl_close($curl);
                if ($info['http_code'] == 200) {
                    $res = true;
                } else if ($info['http_code'] == 400) {
                    $res = false;
                } else {
                    $message = 'Wrong HTTP response status ' . $info['http_code'];
                    if ($result) {
                        $error = json_decode($result, true);
                        if ($error) {
                            $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                        }
                    }
                    Utils::debug('OAuth request error', $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            } else {
                curl_close($curl);
                $error = curl_error($curl);
                Utils::debug('CURL error', $error);
                throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
            }
        }
        return $res;
    }

    static function getAppParameters($accessToken, $controller) {
        $res = false;
        switch ($controller) {
            case 'login':
                $url = TR_ID_COMMAND_REST_LOGIN;
                break;
            case 'social':
                $url = TR_ID_COMMAND_REST_SOCIAL;
                break;
            case 'certificate':
                $url = TR_ID_COMMAND_REST_CERTIFICATE;
                break;
            default:
                return $res;
        }
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, 'clientId=' . TR_ID_OPT_CLIENT_ID);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $res = json_decode($response, true);
                } else {
                    $message = 'Wrong HTTP response status ' . $info['http_code'];
                    if ($response) {
                        $error = json_decode($response, true);
                        if ($error) {
                            $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                        }
                    }
                    Utils::debug('OAuth request error', $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            } else {
                $error = curl_error($curl);
                curl_close($curl);
                Utils::debug('CURL error', $error);
                throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
            }
        }
        return $res;
    }

    static function getAppList($accessToken) {
        $res = false;
        if ($accessToken) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $accessToken));
            curl_setopt($curl, CURLOPT_URL, TR_ID_COMMAND_REST_APP_LIST);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($curl);
            if (!curl_errno($curl)) {
                $info = curl_getinfo($curl);
                if ($info['http_code'] == 200) {
                    $res = json_decode($response, true);
                    $res = $res['list'];
                } else {
                    $message = 'Wrong HTTP response status ' . $info['http_code'];
                    if ($response) {
                        $error = json_decode($response, true);
                        if ($error) {
                            $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                        }
                    }
                    Utils::debug('OAuth request error', $message);
                    throw new OAuth2Exception($message, 0, null);
                }
            } else {
                $error = curl_error($curl);
                curl_close($curl);
                Utils::debug('CURL error', $error);
                throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
            }
        }
        return $res;
    }

    static function findTnUserDataById($searchId) {
        $res = false;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, TR_ID_COMMAND_AUTHORIZE_PROFILE);
        curl_setopt($curl, CURLOPT_USERPWD, TR_ID_OPT_CLIENT_ID . ":" . TR_ID_OPT_CLIENT_SECRET);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array('userId' => $searchId));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                $responseList = json_decode($response, true);
                if ($responseList['code'] == 1605) {
                    $res = array(
                        'id' => $searchId,
                        'entityId' => $searchId,
                        'familyName' => null,
                        'givenName' => null,
                        'email' => null,
                        'username' => null
                    );
                } else {
                    $res = $responseList['data'];
                }
            } else {
                $message = 'Wrong HTTP response status ' . $info['http_code'];
                if ($response) {
                    $error = json_decode($response, true);
                    if ($error) {
                        $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                    }
                }
                Utils::debug('OAuth request error', $message);
                throw new OAuth2Exception($message, 0, null);
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            Utils::debug('CURL error', $error);
            throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
        }
        return $res;
    }

    // SearchField can be: email, phone number, photo
    static function findTnUserData($searchField, $searchTerm) {
        $res = false;

        $postFields = array(
            'type' => $searchField,
            'identity' => $searchTerm,
        );

        if ($searchField === "photo") {
            $E_VISION_KEY = Option::get(TR_ID_MODULE_ID, 'E_VISION_KEY', '');
            if ($E_VISION_KEY == "" ) {
                return false;
            }
            $postFields = array_merge($postFields, array('minAccuracy' => 50, "key" => $E_VISION_KEY));
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, TR_ID_COMMAND_AUTHORIZE_IDENTITY);
        curl_setopt($curl, CURLOPT_USERPWD, TR_ID_OPT_CLIENT_ID . ":" . TR_ID_OPT_CLIENT_SECRET);
        curl_setopt($curl, CURLOPT_TIMEOUT, 30);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postFields);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        $response = curl_exec($curl);
        if (!curl_errno($curl)) {
            $info = curl_getinfo($curl);
            if ($info['http_code'] == 200) {
                $responseList = json_decode($response, true);
                $responseList = $searchField === "photo" ? $responseList['data']['0']['userId'] : $responseList['data'];
                $res = TAuthCommand::findTnUserDataById($responseList);
            } else {
                $message = 'Wrong HTTP response status ' . $info['http_code'];
                if ($response) {
                    $error = json_decode($response, true);
                    if ($error) {
                        $message .= PHP_EOL . $error['error'] . ' - ' . $error['error_description'];
                    }
                }
                Utils::debug('OAuth request error', $message);
                throw new OAuth2Exception($message, 0, null);
            }
        } else {
            $error = curl_error($curl);
            curl_close($curl);
            Utils::debug('CURL error', $error);
            throw new OAuth2Exception(TR_ID_ERROR_MSG_CURL, TR_ID_ERROR_CODE_CURL, null);
        }
        return $res;
    }
}
