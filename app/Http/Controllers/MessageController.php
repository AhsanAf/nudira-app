<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    // Admin kirim pesan
    public function store(Request $request)
    {
        $data = $request->validate([
            'subject' => ['required','string','max:255'],
            'body'    => ['required','string'],
            'target'  => ['nullable','string','max:50'],
        ]);

        $subject = trim($data['subject']);
        $body    = trim($data['body']);
        $target  = $data['target'] ?? 'all';

        $id = DB::table('messages')->insertGetId([
            'from_user_id' => Auth::id(),
            'subject'      => $subject,
            'body'         => $body,
            'target'       => $target,
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        // Buatkan status "sudah dibaca" untuk PENGIRIM saja
        DB::table('message_reads')->updateOrInsert(
            ['message_id' => $id, 'user_id' => Auth::id()],
            ['read_at' => now(), 'created_at'=>now(), 'updated_at'=>now()]
        );

        return response()->json([
            'success'    => true,
            'id'         => $id,
            'subject'    => $subject,
            'body'       => $body,
            'from'       => Auth::user()?->name ?? 'admin',
            'created_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    // Tandai SATU pesan terbaca (hanya untuk user saat ini)
    public function markRead(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $id     = (int) $request->input('id');
        $userId = Auth::id();

        DB::table('message_reads')->updateOrInsert(
            ['message_id' => $id, 'user_id' => $userId],
            ['read_at' => now(), 'updated_at' => now(), 'created_at' => now()]
        );

        $unread = $this->countUnreadFor($userId);

        return response()->json(['success'=>true, 'unread'=>$unread]);
    }

    // Tandai SEMUA pesan terbaca (hanya untuk user saat ini)
    public function markAllRead(Request $request)
    {
        $userId = Auth::id();
        $now    = now();

        // Buatkan baris untuk pesan yang belum punya record user ini
        $missingIds = DB::table('messages')
            ->leftJoin('message_reads as mr', function($join) use ($userId){
                $join->on('mr.message_id', '=', 'messages.id')
                     ->where('mr.user_id', '=', $userId);
            })
            ->whereNull('mr.id')
            ->pluck('messages.id');

        if ($missingIds->isNotEmpty()) {
            $rows = $missingIds->map(fn($mid) => [
                'message_id' => $mid,
                'user_id'    => $userId,
                'read_at'    => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            DB::table('message_reads')->insert($rows);
        }

        // Update semua record milik user ini menjadi read
        DB::table('message_reads')->where('user_id', $userId)->update(['read_at'=>$now, 'updated_at'=>$now]);

        return response()->json(['success'=>true, 'unread'=>0]);
    }

    // Opsional: jumlah unread untuk user saat ini
    public function unreadCount()
    {
        return response()->json(['unread' => $this->countUnreadFor(Auth::id())]);
    }

    private function countUnreadFor(int $userId): int
    {
        return (int) DB::table('messages')
            ->leftJoin('message_reads as mr', function($join) use ($userId){
                $join->on('mr.message_id','=','messages.id')
                     ->where('mr.user_id','=',$userId);
            })
            ->whereNull('mr.read_at')
            ->count();
    }
}
