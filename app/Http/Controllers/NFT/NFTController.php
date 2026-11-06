<?php

namespace App\Http\Controllers\NFT;
use Validator;
use App\Http\Controllers\Controller;

use Illuminate\Http\Request;

use App\Models\Nft;

class NFTController extends Controller {

    public function store(Request $request){
        $validator = Validator::make($request->all(), [
            'token_id' => 'required',
            'name' => 'required',
            'description' => 'nullable',
            'image_url' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $data = $request->json()->all();
        try {
            $nft = Nft::create($data);
            return response()->json([
                'success' => 1,
                'message' => 'success',
                'data' => $nft
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage(),
            ]);
        }
    }

    public function getNFT($id){
        try {
            $nft = Nft::where('token_id', $id)->first();
            return response()->json(
                $nft
            );
        } catch (\Throwable $th) {
            return response()->json([
                'success' => 0,
                'message' => $th->getMessage(),
            ]);
        }
    }
}
