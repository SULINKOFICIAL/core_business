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