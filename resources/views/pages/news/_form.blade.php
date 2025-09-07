<div class="row">
    <div class="col-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Título</label>
        <input type="text" class="form-control form-control-solid" placeholder="Título" name="title" value="{{ $news->title ?? old('title') }}" required>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Categoria</label>
        <select name="category" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" required>
            <option value=""></option>
            <option value="novidades"           {{ $news->category == 'novidades' ? 'selected' : '' }}>Novidades do Produto</option>
            <option value="atualizacoes"        {{ $news->category == 'atualizacoes' ? 'selected' : '' }}>Atualizações e Releases</option>
            <option value="seguranca"           {{ $news->category == 'seguranca' ? 'selected' : '' }}>Segurança</option>
            <option value="eventos"             {{ $news->category == 'eventos' ? 'selected' : '' }}>Eventos & Webinars</option>
            <option value="clientes"            {{ $news->category == 'clientes' ? 'selected' : '' }}>Histórias de Clientes</option>
            <option value="parcerias"           {{ $news->category == 'parcerias' ? 'selected' : '' }}>Parcerias & Integrações</option>
            <option value="melhores-praticas"   {{ $news->category == 'melhores-praticas' ? 'selected' : '' }}>Dicas e Melhores Práticas</option>
            <option value="suporte"             {{ $news->category == 'suporte' ? 'selected' : '' }}>Suporte & Ajuda</option>
            <option value="time"                {{ $news->category == 'time' ? 'selected' : '' }}>Equipe & Carreiras</option>
            <option value="comunicados"         {{ $news->category == 'comunicados' ? 'selected' : '' }}>Comunicados Oficiais</option>
            <option value="mercado"             {{ $news->category == 'mercado' ? 'selected' : '' }}>Tendências de Mercado</option>
            <option value="casos-uso"           {{ $news->category == 'casos-uso' ? 'selected' : '' }}>Casos de Uso</option>
            <option value="cultura"             {{ $news->category == 'cultura' ? 'selected' : '' }}>Cultura & Valores</option>
        </select>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Prioridade</label>
        <select name="priority" class="form-select form-select-solid" data-control="select2" data-placeholder="Selecione" required>
            <option value=""></option>
            <option value="high"    {{ $news->priority == 'high' ? 'selected' : '' }}>Alta</option>
            <option value="medium"  {{ $news->priority == 'medium' ? 'selected' : '' }}>Média</option>
            <option value="low"     {{ $news->priority == 'low' ? 'selected' : '' }}>Baixa</option>
        </select>
    </div>
    <div class="col-4 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Exibir durante</label>
        <input type="text" class="form-control form-control-solid input-date-range" placeholder="Exibir durante" name="date" value="{{ $news->start_date ? $news->start_date . ' até ' . $news->end_date : old('date') }}" required>
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

@section('custom-footer')
    @parent
    <script>
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