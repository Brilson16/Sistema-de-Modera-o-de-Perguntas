<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreModeracaoRequest;
use App\Models\Evento;
use App\Models\Moderacao;
use App\Models\Pergunta;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ModeracaoController extends Controller
{
    public function store(StoreModeracaoRequest $request, Pergunta $pergunta): RedirectResponse
    {
        Moderacao::create([
            'pergunta_id' => $pergunta->id,
            'motivo' => $request->validated('motivo'),
        ]);

        return back()->with('status', 'Denúncia enviada para análise.');
    }

    public function index(Evento $evento): View
    {
        $this->authorize('moderate', $evento);

        $moderacoes = Moderacao::with('pergunta')
            ->whereHas('pergunta', fn ($query) => $query->where('evento_id', $evento->id))
            ->latest()
            ->paginate(15);

        return view('moderacoes.index', compact('evento', 'moderacoes'));
    }

    public function updateStatus(Request $request, Moderacao $moderacao): RedirectResponse
    {
        $moderacao->load('pergunta.evento');
        $this->authorize('moderate', $moderacao->pergunta->evento);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:aprovada,rejeitada,approved,rejected'],
        ]);

        $approved = in_array($validated['status'], ['aprovada', 'approved'], true);
        $moderacao->update(['status' => $approved ? 'aprovada' : 'rejeitada']);
        $moderacao->pergunta->update([
            'is_public' => $approved,
            'status' => $approved ? 'aprovada' : 'rejeitada',
        ]);

        return back()->with('status', 'Decisão de moderação registrada.');
    }
}
