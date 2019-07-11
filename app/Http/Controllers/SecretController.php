<?php

namespace App\Http\Controllers;

use App\Secret;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SecretController extends Controller
{
    /**
     * Generates a random string.
     *
     * @param int $length of the string
     * @return String
     */
    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws Exception
     */
    public function store(Request $request)
    {
        try{
            $validateData = $request->validate([
                'secret' => 'required|string|max:255',
                'expireAfterViews' => 'required|integer|min:1',
                'expireAfter' => 'required|integer',
            ]);

            $secret = Secret::create([
                'hash' => $this->generateRandomString(15),
                'secret' => bcrypt($validateData['secret']),
                'expire_after_views' => $validateData['expireAfterViews'],
                'expire_after' => $validateData['expireAfter'],
            ]);

            return Response::json([
                'code' => 200,
                'message' => 'Successful operation',
                'secret' => [
                    'hash' => $secret->hash,
                    'secretText' => $secret->secret,
                    'createdAt' => $secret->created_at,
                    'expiresAt' => $secret->created_at->addMinutes($secret->expire_after),
                    'remainingViews' => $secret->expire_after_views
                ]
            ]);

        }catch (Exception $e){

            return Response::json([
                'code' => 405,
                'message' => 'Invalid inputs'
            ]);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $hash
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($hash)
    {
        $secret = Secret::where('hash',$hash)->first();

        if($secret != null){

            $expiresAt = $secret->created_at->addMinutes($secret->expire_after);
            $currentDate = Carbon::now();

            if($secret->expire_after_views > 0 && $expiresAt->gt($currentDate)){

                $secret->expire_after_views --;
                $secret->save();

                return Response::json([
                    'code' => 200,
                    'message' => 'Successful operation',
                    'secret' => [
                        'hash' => $secret->hash,
                        'secretText' => $secret->secret,
                        'createdAt' => $secret->created_at,
                        'expiresAt' => $expiresAt,
                        'remainingViews' => $secret->expire_after_views
                    ]
                ]);
            }else{
                return Response::json([
                    'code' => 410,
                    'message' => 'Expired'
                ]);
            }

        }else{
            return Response::json([
                'code' => 404,
                'message' => 'Secret not found'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
