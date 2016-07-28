<?php
namespace Vzaar;

use kahlan\plugin\Stub;
use kahlan\plugin\Monkey;

describe('VzaarGuzzle', function () {
    describe('->whoAmI()', function () {
        beforeEach(function () {
            $config = [
                'secret' => 'some_secret',
                'token' => 'some_token'
            ];

            $req = Stub::create([]);
            $this->vzaar_guzzle = new VzaarGuzzle($config);

            Stub::on($this->vzaar_guzzle)->method('::setAuth')->andReturn($req);
        });

        it('returns null when vzaar-api->test->login is not set', function () {
            Stub::on(GuzzleRequest::class)->method('send', function() {
                return json_encode([]);
            });

            expect($this->vzaar_guzzle->whoAmI())->toBeNull();
        });

        it('returns some value when vzaar-api->test->login is set', function () {
            Stub::on(GuzzleRequest::class)->method('send', function() {
                return json_encode(['vzaar_api' => ['test' => ['login' => 'some_login']]]);
            });

            expect($this->vzaar_guzzle->whoAmI())->toEqual('some_login');
        });
    });

    // describe('->getAccountDetails()', function () {
    //     beforeEach(function () {
    //         $config = [
    //             'secret' => 'some_secret',
    //             'token' => 'some_token'
    //         ];

    //         // $req = Stub::create([]);
    //         $this->vzaar_guzzle = new VzaarGuzzle($config);

    //         // Stub::on($this->vzaar_guzzle)->method('::setAuth')->andReturn($req);
    //     });

    //     // it('returns null when vzaar-api->test->login is not set', function () {
    //     //     Stub::on(GuzzleRequest::class)->method('send', function() {
    //     //         return json_encode([]);
    //     //     });

    //     //     expect($this->vzaar_guzzle->whoAmI())->toBeNull();
    //     // });

    //     it('returns some value when vzaar-api->test->login is set', function () {
    //         // $this->vzaar_guzzle->url = 'http://someurl.com';

    //         $account = [];

    //         // Stub::on(GuzzleRequest::class)->method('send', function() {
    //         //     return json_encode(['vzaar_api' => ['test' => ['login' => 'some_login']]]);
    //         // });

    //         expect($this->vzaar_guzzle->getAccountDetails($account))->toBeNull();
    //     });
    // });

    describe('->uploadVideo()', function () {
        beforeEach(function () {
            $config = [
                'secret' => 'some_secret',
                'token' => 'some_token'
            ];

            $req = Stub::create([]);
            $this->vzaar_guzzle = new VzaarGuzzle($config);

            $signature = [
                'vzaar-api' => [
                    'accesskeyid' => 'accesskeyid',
                    'signature' => 'signature',
                    'acl' => 'some-acl',
                    'bucket' => 'some-bucket',
                    'policy' => 'some-policy',
                    'success_action_status' => 201,
                    'key' => 'some-key',
                    'upload_hostname' => 'http://upload.somehost.com'
                ]
            ];

            Stub::on($this->vzaar_guzzle)->method('::getUploadSignature')->andReturn($signature);
            Stub::on($this->vzaar_guzzle)->method('::setAuth')->andReturn($req);
        });

        it('returns value from response key', function () {
            Stub::on(GuzzleRequest::class)->method('send', function() {
                return "<PostResponse><Key>1/22/333/4444</Key></PostResponse>";
            });

            expect($this->vzaar_guzzle->uploadVideo('/some_video.mp4'))->toEqual('333');
        });
    });
});