<div class="card mb-4">
    <div class="card-body">
        <table class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
            <thead class="rounded" style="background: #1c283e">
                <tr class="fw-bold fs-6 text-white px-7">
                    <th class="px-4 w-150px text-start">ID</th>
                    <th class="">Nome no Cartão</th>
                    <th class="px-4 text-center">Número no Cartão</th>
                    <th class="px-4 text-center">Expiração</th>
                    <th class="px-4">Data</th>
                    <th class="px-4">Status</th>
                    <th class="px-4 text-center">Status</th>
                </tr>
            </thead>
            <tbody class="text-start">
                @foreach ($client->cards as $card)
                <tr>
                    <td class="w-100px text-start px-2">
                        <p class="text-gray-700 fw-bolder mb-0">
                            #{{ $card->id }}
                        </p>
                    </td>
                    <td class="">
                        {{ $card->name }}
                    </td>
                    <td class="text-center">
                        <span class="text-gray-600">
                            {{ $card->number }}
                        </span>
                    </td>
                    <td class="text-center">
                        <p class="text-gray-600 m-0">
                            {{ str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) }}/{{ $card->expiration_year }}
                        </p>
                    </td>
                    <td>
                        <span class="text-gray-600 fs-8">
                            {{ $card->created_at->format('d/m/Y') }} às {{ $card->created_at->format('H:i:s') }}
                        </span>
                    </td>
                    <td>
                        @if ($card->tokenization_id)
                        <a href="{{ route('rede.verify.token', $card->tokenization_id) }}">
                            <i class="fa-solid fa-arrows-left-right-to-line" data-bs-toggle="tooltip" data-bs-html="true" title="<b>tokenizationId:</b><br>{{ $card->tokenization_id }}"></i>
                        </a>
                        @endif
                        @if ($card->tokenization_id)
                        <a href="{{ route('rede.cryptogram', $card->tokenization_id) }}">
                            <i class="fa-solid fa-solid fa-robot" data-bs-toggle="tooltip" data-bs-html="true" title="Criptografar"></i>
                        </a>
                        @endif
                    </td>
                    <td class="text-center">
                        @if ($card->status)
                        <span class="badge badge-light-success">
                            Ativo
                        </span>
                        @else
                        <span class="badge badge-light-danger">
                            Cancelada
                        </span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>