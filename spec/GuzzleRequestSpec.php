<?php
namespace Vzaar;

use kahlan\plugin\Stub;
use kahlan\plugin\Monkey;

describe('GuzzleRequest', function () {
    describe('->send()', function () {
        beforeEach(function () {
            $client = Stub::create([]);

            Stub::on($client)->method('createRequest', function() {
                $request = Stub::create([]);

                Stub::on($request)->method('setHeader', function() {
                    return true;
                });

                Stub::on($request)->method('getBody', function() {
                    $request_body = Stub::create([]);

                    Stub::on($request_body)->method('addFile', function() {
                        return true;
                    });

                    return $request_body;
                });

                return $request;
            });

            Stub::on($client)->method('send', function() {
                $response = Stub::create([]);

                Stub::on($response)->method('getBody', function() {
                    $response_body = Stub::create([]);

                    Stub::on($response_body)->method('getContents', function() {
                        return ['some_response_body_contents'];
                    });

                    return $response_body;
                });

                return $response;
            });

            $this->guzzle_request = new GuzzleRequest('http://someurl.com', $client);
        });

        it('returns successful response content', function () {
            $data = null;

            expect($this->guzzle_request->send($data, 'some/file/path/'))->toEqual(
                ['some_response_body_contents']
            );
        });
    });
});