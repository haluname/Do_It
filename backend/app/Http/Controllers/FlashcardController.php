<?php


namespace App\Http\Controllers;

use App\Models\Flashcard;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FlashcardController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'group_name' => 'required|string|max:255',
            'flashcards' => 'required|array',
            'flashcards.*.question' => 'required|string',
            'flashcards.*.answer' => 'required|string',
        ]);

        $user = Auth::user();
        $groupName = $request->group_name;

        foreach ($request->flashcards as $card) {
            $user->flashcards()->create([
                'group_name' => $groupName,
                'question' => $card['question'],
                'answer' => $card['answer']
            ]);
        }

        return response()->json(['message' => 'Flashcard salvate con successo!']);
    }

    public function index()
    {
        return Auth::user()->flashcards()->get();
    }


    public function destroy($id)
    {
        $flashcard = Flashcard::findOrFail($id);

        if ($flashcard->user_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorizzato'], 403);
        }

        $flashcard->delete();

        return response()->json(['message' => 'Flashcard eliminata con successo']);
    }

    public function deleteGroup($group_name)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Non autorizzato'], 401);
        }

        try {
            $deletedCount = Flashcard::where('user_id', $user->id)
                ->where('group_name', $group_name)
                ->delete();

            return response()->json([
                'message' => "Gruppo eliminato con successo",
                'deleted_count' => $deletedCount
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione del gruppo',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
