<table id="dataTables" class="table table-striped table-row-bordered gy-2 gs-7 align-middle datatables">
    <thead class="rounded" style="background: #1c283e">
        <tr class="fw-bold fs-6 text-white px-7">
            <th class="text-start" width="3%">Cliente</th>
            <th class="text-start">Mensagem</th>
            <th class="text-start px-0" width="30%">Código</th>
            <th class="text-center px-0">Código de Status</th>
            <th class="text-center px-0">Endereço Ip</th>
            <th class="text-center px-0">Criado Em</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($contents as $content)
            <tr>
                <td class="text-center pe-8">{{ $content->client_id }}</td>
                <td>
                    <a class="text-gray-800 text-hover-primary" href="{{ $content->url }}" target="_blank" data-bs-toggle="tooltip" title="{{ $content->message }}">{{ Str::limit($content->message, 50) }}</a>
                </td>
                <td class="text-start cursor-pointer text-hover-primary" data-bs-toggle="modal" data-bs-target="#modal-code" data-stacktrace="{{ $content->stack_trace }}" >{{ Str::limit($content->stack_trace, 50) }}</td>
                <td class="text-center ">{{ $content->status_code }}</td>
                <td class="text-center">{{ $content->ip_address }}</td>
                <td class="text-center">{{ $content->created_at->format('d/m/Y')}}</td>
            </tr>
        @endforeach
    </tbody>
</table>