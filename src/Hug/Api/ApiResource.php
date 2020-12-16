<?php

namespace Hug\Api;

use  Hug\Admin\Admin;
use  Hug\User\User;

use Tonic\Resource, Tonic\Response, Tonic\ConditionException;
use \Firebase\JWT\JWT;

use Hug\HArray\HArray as HArray;

/**
 *
 */
class ApiResource extends Resource
{
    public $time_start = null;
    public $uid = null;
    public $role = null;

    /**
     * Process all request's data as json : encode / decode
     */
    function json()
    {
        $this->before(function ($request)
        {
            if($request->contentType == "application/json")
            {
                $request->data = json_decode($request->data);
            }
        });
        $this->after(function ($response)
        {
            $response->contentType = "application/json";
            $response->body = json_encode($response->body, JSON_INVALID_UTF8_IGNORE);
        });
    }

    /**
     * Check an API key against User & IP
     *
     * @param string $api_key
     * @return array $Response
     */
    function apiauth($api_key)
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => null];
        
        if($api_key!=='' && $api_key!=='null' && $api_key!==null && strlen($api_key)>20)
        {
            $infos = User::get_info_from_api_key($api_key);
            if($infos['status']==='success')
            {
                # Get client IP
                $ip = $_SERVER['REMOTE_ADDR'];
                //error_log('apiauth client IP : ' . $ip);

                # Test ip is valid ?
                
                # Get Authorized IPs 
                $UserApiAuthorizedIps = new UserApiAuthorizedIps($infos['data']['user_id']);

                # Is client IP authorized ?
                if($UserApiAuthorizedIps->is_authorized($ip))
                {
                    $Response['data'] = $infos['data'];
                    $Response['status'] = 'success';
                }
                else
                {
                    $Response['message'] = 'UNAUTHORIZED_IP';
                }
            }
            else
            {
                $Response['message'] = 'UNKWONW_API_KEY';
            }
        }
        else
        {
            $Response['message'] = 'MISSING_API_KEY';
        }
        return $Response;
    }

    /**
     * Check user is authorized based on token
     */
    function auth()
    {
        // $this->before(function ($request)
        // {
        //     //error_log('ZenResource auth : ' . $_SESSION['demo'] );
        //     if($this->is_demo_call_valid()===false)
        //     {
        //         throw new Tonic\DemoException;
        //     }

        // });

        /* Refresh token expiration date */
        // $this->after(function ($response)
        // {
        //     $response->setHeader("Refresh-Token", $new_token);
        // });

        # Hack to call from server : tests
        $all_heads = getallheaders();
        if(!isset($_SERVER['HTTP_X_ACCESS_TOKEN']) && isset($all_heads['HTTP_X_ACCESS_TOKEN']))
        {
            $_SERVER['HTTP_X_ACCESS_TOKEN'] = $all_heads['HTTP_X_ACCESS_TOKEN'];
        }
        
        if( isset($_SERVER['HTTP_X_ACCESS_TOKEN']) && 
            ! empty($_SERVER['HTTP_X_ACCESS_TOKEN']) && 
            ! is_null($_SERVER['HTTP_X_ACCESS_TOKEN']) && 
            $_SERVER['HTTP_X_ACCESS_TOKEN']!=='null')
        {
            try
            {
                $encoded = $_SERVER['HTTP_X_ACCESS_TOKEN'];
                error_log('encoded : ' . $encoded);

                JWT::$leeway = 60;
                $decoded = JWT::decode($encoded, SERVER_PRIVATE_KEY, ['HS256']);
                //$decoded = (array)$decoded;

                $decoded = HArray::object_to_array($decoded);
                //error_log('decoded : ' . print_r($decoded, true) );

                if( $decoded && 
                    isset($decoded['data']['uid']) && 
                    isset($decoded['data']['role']) && 
                    isset($decoded['data']['demo']))
                {
                    if($decoded['data']['demo']===true)
                    {
                        if($this->is_demo_call_valid()===false)
                        {
                            return new Tonic\Response(Tonic\Response::CONFLICT);
                        }
                    }

                    $this->uid = $decoded['data']['uid'];
                    $this->role = $decoded['data']['role'];
                    // error_log('auth uid - role : ' . $this->uid . ' - ' . $this->role);

                    return;
                }
            }
            catch (\Firebase\JWT\ExpiredException $e)
            {
                error_log('ApiResource ExpiredException : ' . $e->getMessage());
            }
            catch(UnexpectedValueException $e)
            {
                error_log('ApiResource auth UnexpectedValueException : ' . $e);
            }
            catch(Exception $e)
            {
                error_log('ApiResource auth Exception : ' . $e);
            }
        }
        else 
        {
            # Server to server call
            // error_log($_SERVER['REMOTE_ADDR'] . ' === ' . $_SERVER['SERVER_ADDR']);
            if($_SERVER['REMOTE_ADDR'] === $_SERVER['SERVER_ADDR'])
            {
                $this->uid = 'server';
                $this->role = 'server';
                return;
            }
        }

        return new \Tonic\Response(\Tonic\Response::UNAUTHORIZED);
    }

    /**
     * Add a new 'Exec-Time' header to response (time in seconds 0.00) 
     */
    function speed()
    {
        $this->before(function ($request)
        {
            $this->time_start = microtime(1);
        });

        $this->after(function ($response)
        {
            $time_end = microtime(1);
            $duration = round($time_end-$this->time_start, 2);
            $response->setHeader("Exec-Time", $duration);
        });
    }

    /**
     * Checks is demo API call is authorized or not
     */
    private function is_demo_call_valid()
    {
        $is_demo_call_valid = false;
            
        $visitor_ip = $_SERVER['REMOTE_ADDR'];
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        // error_log('visitor_ip : ' . $visitor_ip );
        // error_log('uri : ' . $uri );
        // error_log('method : ' . $method );

        # Check For Admin IPs 
        if(Admin::is_admin_ip($visitor_ip))
        {
            $is_demo_call_valid = true;
        }
        else
        {
            
            $exclude = false;
            foreach (DEMO_EXCLUDED_CALLS as $exclude_call => $methods)
            {
                if(HString::starts_with($uri, $exclude_call))
                {
                    if(in_array($method, $methods))
                    {
                        $exclude = true;
                        break;
                    }
                }
            }

            
            if($exclude === false)
            {
                $go = false;
                foreach (DEMO_AUTHORIZED_CALLS as $call => $methods)
                {
                    if(HString::starts_with($uri, $call)===true)
                    {
                        if(in_array($method, $methods))
                        {
                            //error_log('DEMO CALL IS VALID');
                            $is_demo_call_valid = true;
                            break;
                        }
                    }
                }
            }
        }
        // error_log('DEMO CALL FOR ' . $uri . ' ' . $method . ' IS ' . $is_demo_call_valid);

        return $is_demo_call_valid;
    }
    
}
