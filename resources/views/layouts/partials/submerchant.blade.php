@auth
    @hasanyrole('Admin|Merchant')
    @role('Admin')
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var branchData = @json($branches);
            var submerchantData = @json($submerchants);
            document.querySelector("select[name='role']").addEventListener('change', function(e) {
                if(e.target[e.target.options.selectedIndex].text.trim().toLowerCase() === 'sub merchant') {
                    $("select[name='is_vip']").val(0).trigger('change');
                    $('#vip_user_div').hide();
                    $("#branch_id").prop('required',true);
                    $("#parent_id").prop('required',true);
                    $('#parent_merchant_div').show();
                    $('#branch_div').show();
                } else if(e.target[e.target.options.selectedIndex].text.trim().toLowerCase() === 'user') {
                    $('#vip_user_div').show();
                    $(".js-example-basic-multiple").select2().val(null).trigger('change');
                    $(".js-example-basic-single").select2().val(null).trigger('change');
                    $("#branch_id").prop('required',false);
                    $("#parent_id").prop('required',false);
                    $('#parent_merchant_div').hide();
                    $('#branch_div').hide();
                } else {
                    $("select[name='is_vip']").val(0).trigger('change');
                    $(".js-example-basic-multiple").select2().val(null).trigger('change');
                    $(".js-example-basic-single").select2().val(null).trigger('change');
                    $('#vip_user_div').hide();
                    $("#branch_id").prop('required',false);
                    $("#parent_id").prop('required',false);
                    $('#parent_merchant_div').hide();
                    $('#branch_div').hide();
                }
            })
            $(".js-example-basic-multiple").select2({
                placeholder: "Select Branch",
                data: branchData.reduce(function(filtered, option){
                    if(Object.keys(submerchantData).length > 0) {
                        if(option, parseInt(option.user_id) === parseInt(submerchantData.parent_id)) {
                            filtered.push({id: option.id, text: option.name, selected: $.inArray(option.id, submerchantData.branch_id.split(",").map(x => parseInt(x))) > -1 })
                        }
                    }
                    return filtered;
                }, [])
            });
            
            $(".js-example-basic-single").select2({
                placeholder: "Select Merchant",
                allowClear: true
            }).on('change', function(e) {
                if(e.target.options.selectedIndex > 0) {
                    $("#branch_id").html('').select2({placeholder: "Select Branch", data: branchData.reduce(function(filtered, option){
                            if(option, parseInt(option.user_id) === parseInt(e.target.value)) {
                                filtered.push({id: option.id, text: option.name})
                            }
                            return filtered;
                        }, [])
                    });
                } else {
                    $('#branch_id').html('').select2({placeholder: "Select Branch",data: [{id: '', text: ''}]});
                }
            });
        });
    </script>
    @endrole
    @role('Merchant')
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            var branchData = @json($branches);
            var submerchantData = @json($submerchants);
            document.querySelector("select[name='role']").addEventListener('change', function(e) {
                if(e.target[e.target.options.selectedIndex].text.trim().toLowerCase() === 'sub merchant') {
                    $("#branch_id").prop('required',true);
                    $("#parent_id").prop('required',true);
                    $('#parent_merchant_div').show();
                    $('#branch_div').show();
                } else {
                    $("#branch_id").prop('required',false);
                    $("#parent_id").prop('required',false);
                    $('#parent_merchant_div').hide();
                    $('#branch_div').hide();
                }
            })
            $(".js-example-basic-multiple").select2({
                placeholder: "Select Branch",
                data: branchData.reduce(function(filtered, option){
                    if(Object.keys(submerchantData).length > 0) {
                        if(option, parseInt(option.user_id) === parseInt(submerchantData.parent_id)) {
                            filtered.push({id: option.id, text: option.name, selected: $.inArray(option.id, submerchantData.branch_id.split(",").map(x => parseInt(x))) > -1 })
                        }
                    }
                    return filtered;
                }, [])
            });
            
            $(".js-example-basic-single").select2({
                placeholder: "Select Merchant",
                allowClear: true
            }).on('change', function(e) {
                if(e.target.options.selectedIndex > 0) {
                    $("#branch_id").html('').select2({placeholder: "Select Branch", data: branchData.reduce(function(filtered, option){
                            if(option, parseInt(option.user_id) === parseInt(e.target.value)) {
                                filtered.push({id: option.id, text: option.name})
                            }
                            return filtered;
                        }, [])
                    });
                } else {
                    $('#branch_id').html('').select2({placeholder: "Select Branch",data: [{id: '', text: ''}]});
                }
            });
        });
    </script>
    @endrole
    @endhasanyrole
    @endauth