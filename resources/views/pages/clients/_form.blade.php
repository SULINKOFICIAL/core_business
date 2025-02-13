<div class="row">
    <div class="col-12 col-md-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2 required">Nome da empresa</label>
        <input type="text" class="form-control form-control-solid" placeholder="Nome" name="name" value="{{ $content->name ?? old('name') }}" maxlength="255" required>
    </div>
    <div class="col-12 col-md-12 mb-4">
        <label class="form-label fs-6 fw-bold text-gray-700 mb-2">Logo</label>
        <input type="file" name="fileLogo" class="form-control form-control-solid">
    </div>
</div>


@section('custom-footer')
<script>
    $(document).ready(function(){
        $('.not-allow-www').on('input', function(){
            let value = $(this).val();
            // Remove "www." do início, se existir
            if (value.startsWith("www.")) {
                $(this).val(value.replace(/^www\./, ''));
            }
        });
    });
</script>
@endsection
