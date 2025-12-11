<script src="https://cdn.jsdelivr.net/gh/linways/table-to-excel@v1.0.4/dist/tableToExcel.js"></script>
<script type="text/javascript">
    jQuery(".btn-export").click(function() {
        if (!$(".check_all:checked").length) {

            Swal.fire({
                title: "{{$errortext}}",
                icon: 'warning',
            });
            return false;
        }

        $(".check_all:not(:checked)").closest("tr").attr("data-exclude", true);

        $(".check_all:checked").closest("tr").attr("data-exclude", false);

        Swal.fire({
            title: '{{$confirmexport}}',
            showCancelButton: true,
            confirmButtonText: 'Download',
            showLoaderOnConfirm: true,
            preConfirm: (login) => {
                return fetch("{{route('users.permissions.export-data',$module)}}", {
                    method: "POST", 
                    headers: {
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({_token: "{{csrf_token()}}"}), 
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error(response.statusText)
                    }
                    return response.json()
                })
                .catch(error => {
                    Swal.showValidationMessage("Unable to perform request.")
                })
            },
            allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed) {
                    if (!result.value.success) {
                        Swal.fire({
                            title: result.value.message,
                            icon: 'warning',
                        });
                    } else {
                        TableToExcel.convert(document.querySelector("{{$tableid}}"), { // html code may contain multiple tables so here we are refering to 1st table tag
                            name: "{{$filename}}", // fileName you could use any name
                            sheet: {
                                name: '{{$sheetname}}' // sheetName
                            }
                        });
                    }
                }
            })
        

    });
</script>