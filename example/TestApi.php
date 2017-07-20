<?php

use Hug\Api\ApiResource;

/**
 * @uri /test
 */
class TestApi extends ApiResource
{
	/**
     *
     * @method GET
     * @json
     * @speed
     * @provides application/json
     * @return Tonic\Response
     */
    public function get()
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => ['coucou']];

        return new Tonic\Response(
            Tonic\Response::OK, 
            $Response['data']
        );
        // return new Tonic\Response(
        //     Tonic\Response::NOTACCEPTABLE, 
        //     $Response['message']
        // );
    }

    /**
     * @method POST
     * @json
     * @accepts application/json
     * @provides application/json
     * @return Tonic\Response
     */
    public function post()
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => []];

        return new Tonic\Response(Tonic\Response::OK, $Response['data']);
        // return new Tonic\Response(Tonic\Response::NOTACCEPTABLE, $Response['message']);
    }

    /**
     * @method PUT
     * @json
     * @accepts application/json
     * @provides application/json
     * @return Tonic\Response
     */
    function put()
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => []];
        
        return new Tonic\Response(Tonic\Response::OK, $Response['data']);
        // return new Tonic\Response(Tonic\Response::SERVICEUNAVAILABLE, $Response['message']);
    }
    /**
     * @method DELETE
     * @json
     * @accepts application/json
     * @provides application/json
     * @return Tonic\Response
     */
    function delete()
    {
        $Response = ['status' => 'error', 'message' => '', 'data' => []];
        
        return new Tonic\Response(Tonic\Response::OK, $Response['data']);
        // return new Tonic\Response(Tonic\Response::SERVICEUNAVAILABLE, $Response['message']);
    }
}