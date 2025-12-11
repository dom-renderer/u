@if($editor_enabled)

@if($codemirror_enabled)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/{{\App\Helpers\Cdn::CodeMirror}}/codemirror.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/codemirror/{{\App\Helpers\Cdn::CodeMirror}}/mode/xml/xml.min.js"></script>
@endif

<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/{{\App\Helpers\Cdn::Summernote}}/summernote-bs4.min.js"></script>
@if($editor_locale)
    <script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/{{\App\Helpers\Cdn::Summernote}}/lang/summernote-{{$editor_locale}}.min.js"></script>
@endif
<script>


    $(function() {

        var options = $.extend(true, {lang: '{{$editor_locale}}' {!! $codemirror_enabled ? ", codemirror: {theme: '{$codemirror_theme}', mode: 'text/html', htmlMode: true, lineWrapping: true}" : ''  !!} } , {!! $editor_options !!});

        $("textarea.summernote-editor").summernote(options);

        $("label[for=content]").click(function () {
            $("#content").focus();
        });

        $(document).on("change", "input[name='agent_or_admin']", function () {
            $th = $(this);
            $.ajax({
                url: `{{ route(\App\Models\TicketSetting::grab('main_route').'.get_admin_agent') }}`,
                data: {
                    get_agent_admin: $th.val()
                },
                type: "POST",
                dataType: 'JSON',
                success: function(resp) {
                    if(resp.status) {
                        obj = resp.data;
                        if($th.val() == "agent") {
                            html = `<option value="" selected="selected">Select Agent</option>`;
                        } else {
                            html = `<option value="" selected="selected">Select Admin</option>`;
                        }
                        for (arr in obj) {
                            html += `<option value="`+arr+`">`+obj[arr]+`</option>`;
                        }
                        $('#agent_id').html(html);
                    }
                }
            });
        });
    });
    $('.ticketForm').click(function() {
        var content = $('.summernote-editor').summernote('isEmpty');

        var subject = $('#subject').val();

        if (content == true) {
            $('#contentErr').html('Please enter Content');

        }else{
            $('#contentErr').html(' ');
        }
        if (!subject) {
            $('#subjectErr').html('Please enter Subject');
        }else{
            $('#subjectErr').html('');
        }
        if (content == false && subject != '') {
            $(':input[type="submit"]').prop('disabled', true);
                    $('#tocket_form').submit();
        }else{
            return false;
        }


    });
    $(document).on('change','[name="priority_id"]',function(e){
        let color = $('[name="priority_id"] option:selected').data('color');
        $(this).css('background-color',color);
    });
</script>
@endif
