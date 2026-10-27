@extends('layouts.app')

@section('title', 'Fila de Moderação — ' . $evento->titulo)

@section('content')
<div class="container py-4">
    <h1 class="h3 mb-4">Fila de moderação: {{ $evento->titulo }}</h1>

    @if (session('status'))
        <div class="alert alert-success" role="status">{{ session('status') }}</div>
    @endif

    @forelse ($moderacoes as $moderacao)
        <div class="card mb-3">
            <div class="card-body">
                <p class="mb-2"><strong>Pergunta:</strong> {{ $moderacao->pergunta->texto ?? 'Pergunta indisponível' }}</p>
                <p class="mb-2"><strong>Motivo:</strong> {{ $moderacao->motivo }}</p>
                <p class="mb-3"><strong>Status:</strong> {{ $moderacao->status }}</p>
                @if ($moderacao->status === 'analisando')
                    <div class="d-flex gap-2">
                        <form method="POST" action="{{ route('moderacoes.status.update', $moderacao) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="aprovada">
                            <button class="btn btn-success" type="submit">Aprovar pergunta</button>
                        </form>
                        <form method="POST" action="{{ route('moderacoes.status.update', $moderacao) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="rejeitada">
                            <button class="btn btn-danger" type="submit">Rejeitar pergunta</button>
                        </form>
                    </div>
                @endif
            </div>
        </div>
    @empty
        <p class="text-muted">Não há denúncias para este evento.</p>
    @endforelse

    {{ $moderacoes->links() }}
</div>
@endsection
