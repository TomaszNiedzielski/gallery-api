<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;

class FolderController extends Controller
{
    public function rename(Request $request) {
        $this->validate($request, [
            'oldFolderName' => 'required|max:255',
            'newFolderName' => 'required|max:255'
        ]);

        $updatedRecords = DB::table('media')
            ->whereIn('user_id', [auth()->user()->id, auth()->user()->partner_id])
            ->where('folder', $request->oldFolderName)
            ->update(['folder' => $request->newFolderName]);

        return response()->json($updatedRecords);
    }
}
