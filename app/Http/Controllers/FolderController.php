<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Illuminate\Support\Facades\Validator;

class FolderController extends Controller
{
    public function rename(Request $request) {
        // Validate data.
        $validator = Validator::make($request->all(), [
            'oldFolderName' => 'required|string|max:255',
            'newFolderName' => 'required|string|max:255',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        }

        // Update old folder name for every media item.
        $updatedRecords = DB::table('media')
            ->whereIn('user_id', [auth()->user()->id, auth()->user()->partner_id])
            ->where('folder', $request->oldFolderName)
            ->update(['folder' => $request->newFolderName]);

        return response()->json($updatedRecords);
    }
}
