<?php

namespace App\Http\Controllers;

use App\Http\Requests\EventoFormRequest;
use App\Models\Evento;
use App\Models\Pergunta;
use App\Http\Requests\StorePerguntaRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;

class EventoController extends Controller
{
    public function index()
    {
        $eventos = Evento::all();
        return view('eventos.index', compact('eventos'));
    }

    public function show($id)
    {
        $evento = Evento::findOrFail($id);

        $perguntas = Pergunta::query()
            ->where('evento_id', $evento->id)
            ->withCount('votos')
            ->when(Auth::check(), function ($query) {
                $query->withExists([
                    'votos as usuario_votou' => fn ($votos) => $votos->where('users.id', Auth::id()),
                ]);
            })
            ->orderByDesc('votos_count')
            ->latest()
            ->paginate(10);

        return view('eventos.show', compact('evento', 'perguntas'));
    }

    public function votar(Pergunta $pergunta): RedirectResponse
    {
        $resultado = $pergunta->votos()->toggle(auth()->id());
        $mensagem = count($resultado['attached']) > 0
            ? 'Seu voto foi registrado!'
            : 'Seu voto foi removido.';

        return back()->with('status', $mensagem);
    }

 
    public function storePergunta(StorePerguntaRequest $request, $id)
    {
        $evento = Evento::findOrFail($id);

        Pergunta::create([
            'evento_id' => $evento->id,
            'user_id' => Auth::user()->id,
            'texto'     => $request->input('texto'),
            'status'    => 'pendente',
        ]);

        return redirect()->route('eventos.show', $evento->id)
            ->with('sucesso', 'Sua pergunta foi enviada com sucesso!');
    }

    public function create(){
        return view('eventos.create');
    }

    public function store(EventoFormRequest $request){
        $evento = $request->user()->eventos()->create($request->validated());
        return redirect()->route('eventos.show', $evento->id);
    }
}
