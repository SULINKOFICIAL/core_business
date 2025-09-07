<div class="row">
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Título</label>
        <input type="text" class="form-control form-control-solid" placeholder="Título" name="title" value="{{ $news->title ?? old('title') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Tags</label>
        <input class="form-control form-control-solid" name="tags" value="{{ $news->tags ?? old('tags') }}" id="tags-custom"/>
    </div>
    <div class="row">
        <div class="col mb-4">
            <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Categoria</label>
            <select name="category_id" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" required>
                <option value=""></option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" {{ isset($news) && $news->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
        </div>
        @if (!isset($news))
        <div class="col-4 mb-4">
            <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Prioridade</label>
            <select name="priority" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" required>
                <option value=""></option>
                <option value="low"     {{ isset($news) && $news->priority == 'low' ? 'selected' : '' }}>Baixa</option>
                <option value="medium"  {{ isset($news) && $news->priority == 'medium' || !isset($news) ? 'selected' : '' }}>Média</option>
                <option value="high"    {{ isset($news) && $news->priority == 'high' ? 'selected' : '' }}>Alta (CUIDADO)</option>
            </select>
        </div>
        @endif
        <div class="col mb-4">
            <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Exibir durante</label>
            <input type="text" class="form-control form-control-solid input-date-range" placeholder="Exibir durante" name="date" value="{{ isset($news) && $news->start_date ? $news->start_date . ' até ' . $news->end_date : old('date') }}" required>
        </div>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Resumo</label>
        <input type="text" class="form-control form-control-solid" placeholder="Resumo" name="resume" value="{{ $news->resume ?? old('resume') }}" required>
    </div>
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Corpo</label>
        <div id="kt_docs_quill_basic" name="kt_docs_quill_basic" style="height: auto;">
            @if(isset($news) && $news->body)
                {!! $news->body !!}
            @else
                <h1>Título da Notícia Exemplo</h1>
                <p>Escreva aqui o <strong>resumo inicial</strong> da sua notícia, destaque os pontos principais ou coloque uma frase de impacto.</p>
                
                <blockquote>Exemplo: “Novo recurso permite automatizar tarefas e ganhar produtividade!”</blockquote>
                
                <h3>Subtítulo ou Seção Importante</h3>
            <p>
                Utilize este espaço para desenvolver o conteúdo principal da notícia. 
                <br>
                <em>Dica:</em> Você pode <strong>negritar</strong> palavras, <u>sublinhar</u> ou <i>destacar trechos importantes</i>.
            </p>
            
            <ul>
                <li>Conte fatos relevantes;</li>
                <li>Liste novidades ou etapas;</li>
                <li>Adicione tópicos importantes para o leitor;</li>
                <li>Seja claro e objetivo;</li>
            </ul>
            
            <p>Para inserir links: <a href="https://seusite.com/noticia">Clique aqui e veja um exemplo</a></p>
            
            <p style="text-align: right;"><strong>Assinatura ou créditos, se desejar</strong></p>
            @endif
        </div>
        <input type="hidden" name="body" id="body">
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Texto do CTA</label>
        <input type="text" class="form-control form-control-solid" placeholder="Texto do CTA" name="cta_text" value="{{ $news->cta_text ?? old('cta_text') }}">
    </div>
    <div class="col-6 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">URL do CTA</label>
        <input type="text" class="form-control form-control-solid" placeholder="URL do CTA" name="cta_url" value="{{ $news->cta_url ?? old('cta_url') }}">
    </div>
</div>
<div class="col-12">
    <div class="alert alert-danger d-flex align-items-center p-5 shadow">
        <i class="ki-duotone ki-shield-tick fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
        <div class="d-flex flex-column">
            <h4 class="mb-1 text-danger">Atenção com as prioridades!</h4>
            <span>Escolha a prioridade da notícia para que ela seja exibida nas atualizações e no modal de notícias.</span>
            <ul class="mb-0">
                <li>
                    <b>Proridade Baixa:</b> Exibe apenas nas atualizações.
                </li>
                <li>
                    <b>Proridade Média:</b> Exibe nas atualizações e abre modal.
                </li>
                <li>
                    <b>Proridade Alta:</b> Exibe nas atualizações e abre modal e <b>envia e-mail para os usuários do cliente</b>.
                </li>
            </ul>
        </div>
    </div>
</div>

@isset($news)
    <div class="col-12">
        <span class="text-gray-700">
            <b>Criado em:</b> {{ $news->created_at->format('d/m/Y') }}
        </span>
    </div>
@endisset

@section('custom-footer')
    @parent
    <script>
        var input1 = document.querySelector("#tags-custom");
        new Tagify(input1);


        // Inicializa o Quill normalmente
        var quill = new Quill('#kt_docs_quill_basic', {
            modules: {
                toolbar: [
                    [{ header: [1, 2, false] }],
                    ['bold', 'italic', 'underline'],
                    ['image', 'link', 'blockquote', 'code-block'],
                    [{ 'align': [] }],
                ]
            },
            placeholder: 'Type your text here...',
            theme: 'snow'
        });

        // Atualiza o hidden com o conteúdo atual ao iniciar
        $('#body').val(quill.root.innerHTML);

        // Sempre que houver mudança no editor, atualiza o input hidden
        quill.on('text-change', function() {
            $('#body').val(quill.root.innerHTML);
        });

        // Opcional: antes de enviar o form, força atualização final (boa prática)
        $('form').on('submit', function() {
            $('#body').val(quill.root.innerHTML);
        });

        $(".input-date-range").flatpickr({
            altInput: true,
            altFormat: "F j, Y",
            dateFormat: "Y-m-d",
            mode: "range",
            minDate: "today",
            locale: "pt",
        });
    </script>
@endsection