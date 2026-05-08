<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class IdleController extends Controller
{
    /**
     * Proses unlock dengan password user
     */
    public function unlock(Request $request)
    {
        $user = Auth::user();
        // print_r("userpassword : " . $user->password);
        // print_r("requestpassword : " . $request->password);
        if ($user && Hash::check($request->password, $user->password)) {
            // Update personal_access_tokens
            DB::table('personal_access_tokens')
                ->where('tokenable_id', $user->id)
                ->update([
                    'expired_token' => 0,
                    'expired_token_at' => null,
                    'updated_at' => now(),
                ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Tandai token user sebagai expired (dipanggil saat idle timeout)
     */
    public function expireToken(Request $request)
    {
        $userId = Auth::id();

        DB::table('personal_access_tokens')
            ->where('tokenable_id', $userId)
            ->update([
                'expired_token' => 1,
                'expired_token_at' => now(),
            ]);

        return response()->json(['status' => 'expired']);
    }

    /**
     * Cek apakah token user sudah expired (dipanggil saat page load/refresh)
     */
    public function checkToken(Request $request)
    {
        $userId = Auth::id();

        $token = DB::table('personal_access_tokens')
            ->where('tokenable_id', $userId)
            ->first();

        return response()->json([
            'expired' => $token->expired_token ?? 0,
        ]);
    }
}
