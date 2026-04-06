<div class="modal fade" tabindex="-1" id="modal_ticket_show">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header py-3 bg-dark">
                <div>
                    <h5 class="modal-title text-white mb-1">{{ $ticket->title }}</h5>
                    <div class="text-gray-300 fs-8">Cliente: {{ $ticket->tenant?->name ?? 'N/A' }}</div>
                </div>
                <div class="btn btn-icon bg-dark ms-2" data-bs-dismiss="modal" aria-label="Close">
                    <span class="svg-icon svg-icon-2x fw-bolder">X</span>
                </div>
            </div>
            <div class="modal-body">
                <div class="p-4 rounded bg-light mb-6">
                    <div class="fw-bold text-gray-800 mb-2">Solicitação inicial</div>
                    <div class="text-gray-700">{{ $ticket->description }}</div>
                </div>

                @if($ticket->attachments->isNotEmpty())
                    <div class="mb-6">
                        <div class="fw-bold text-gray-800 mb-3">Anexos</div>
                        <div class="d-flex flex-column gap-2">
                            @foreach ($ticket->attachments as $attachment)
                                <a href="{{ $attachment->url() }}" target="_blank" class="d-flex align-items-center justify-content-between border rounded px-4 py-3 text-gray-700 text-hover-primary">
                                    <span class="d-flex align-items-center gap-3">
                                        <i class="fa-solid fa-paperclip"></i>
                                        {{ $attachment->original_name }}
                                    </span>
                                    <span class="fs-8 text-gray-600">{{ number_format(($attachment->size_bytes ?? 0) / 1024 / 1024, 2, ',', '.') }} MB</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="mb-6">
                    <div class="fw-bold text-gray-800 mb-3">Respostas</div>
                    <div class="d-flex flex-column gap-4">
                        @forelse ($ticket->replies as $reply)
                            <div class="border rounded p-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold text-gray-800">
                                        {{ $reply->user?->name ?? $reply->tenant?->name ?? 'Cliente' }}
                                    </span>
                                    <span class="fs-8 text-gray-600">{{ $reply->created_at?->format('d/m/Y H:i') }}</span>
                                </div>
                                <div class="text-gray-700">{{ $reply->message }}</div>
                            </div>
                        @empty
                            <div class="text-gray-600">Nenhuma resposta enviada até o momento.</div>
                        @endforelse
                    </div>
                </div>

                <div class="row g-6 align-items-start">
                    <div class="col-md-4">
                        <div class="border rounded p-4 bg-light">
                            <div class="fw-bolder text-gray-800 mb-4">Informações do ticket</div>
                            <div class="mb-4">
                                <div class="fs-8 text-gray-600 text-uppercase">Solicitado em</div>
                                <div class="fw-bold text-gray-800">{{ $ticket->created_at?->format('d/m/Y H:i') ?? '-' }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="fs-8 text-gray-600 text-uppercase">Empresa</div>
                                <div class="fw-bold text-gray-800">{{ $ticket->tenant?->company ?? $ticket->tenant?->name ?? '-' }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="fs-8 text-gray-600 text-uppercase">Usuário solicitante</div>
                                <div class="fw-bold text-gray-800">
                                    {{ data_get($ticket->requester_user, 'name', '-') }}
                                    @if(data_get($ticket->requester_user, 'id'))
                                        <span class="text-gray-600 fs-8">#{{ data_get($ticket->requester_user, 'id') }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="fs-8 text-gray-600 text-uppercase">Quem abriu</div>
                                <div class="fw-bold text-gray-800">{{ $ticket->filedByUser?->name ?? '-' }}</div>
                            </div>
                            <div class="mb-4">
                                <div class="fs-8 text-gray-600 text-uppercase">Quando abriu</div>
                                <div class="fw-bold text-gray-800">
                                    @if($ticket->opened_at)
                                        {{ $ticket->opened_at?->format('d/m/Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                            <div class="mb-0">
                                <div class="fs-8 text-gray-600 text-uppercase">Quando finalizou</div>
                                <div class="fw-bold text-gray-800">
                                    @if($ticket->finished_at)
                                        {{ $ticket->finished_at?->format('d/m/Y H:i') }}
                                        @if($ticket->finishedByUser)
                                            <div class="fs-8 text-gray-600">por {{ $ticket->finishedByUser->name }}</div>
                                        @endif
                                    @else
                                        -
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-8">
                        <form action="{{ route('tickets.reply', $ticket->id) }}" method="POST" id="form-ticket-reply">
                            @csrf
                            <label class="form-label fw-bold text-gray-700 mb-2">Nova resposta</label>
                            <textarea name="message" class="form-control form-control-solid" rows="5" placeholder="Digite a resposta para o cliente" required></textarea>
                            <div class="d-flex justify-content-end align-items-center mt-4">
                                <button type="submit" class="btn btn-primary">Responder</button>
                            </div>
                        </form>

                        <div class="d-flex justify-content-start mt-4">
                            <form action="{{ route('tickets.finish', $ticket->id) }}" method="POST" id="form-ticket-finish">
                                @csrf
                                <button type="submit" class="btn btn-light-danger ticket-finish-submit" @if($ticket->progress === 'fechado') disabled @endif>
                                    Finalizar ticket
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
