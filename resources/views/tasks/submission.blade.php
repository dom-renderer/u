<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title> {{ isset($task->parent->checklist->name) ? $task->parent->checklist->name : '' }} </title>
    <link rel='stylesheet' href="{{ url('assets/css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/css/formio-bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{  asset('assets/css/formio.full.min.css') }}">
    <style>
        div#formio > div.alert-danger {
            display: none;
        }
    
        .formio-form nav[id^="wizard-"] {
            display: none;
        }
    
        .formio-wizard-nav-container {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div id="formio"></div>
</body>
</html>

<script src="{{  asset('assets/js/jquery.min.js') }}"></script>
<script src="{{  asset('assets/js/formio.full.min.js') }}"></script>
<script type="text/javascript">
$(document).ready(function () {

    function submitForm(data, url) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = url;

        for (const key in data) {
            if (data.hasOwnProperty(key)) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = JSON.stringify(data[key]);
                form.appendChild(input);
            }
        }

        document.body.appendChild(form);
        form.submit();
    }


    const formJson = @json(isset($task->form) ? $task->form : '');

    Formio.createForm(document.getElementById('formio'), formJson)
    .then((form) => {

        form.on('submit', (submission) => {

            const data = {
                data: submission
            };

            submitForm(data, "{{ route('checklists-submission', $id) }}");
        });

    })
    .catch((err) => {
        
    });
    
});
</script>