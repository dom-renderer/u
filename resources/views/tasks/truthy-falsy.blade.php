<table class="table table-bordered table-stripped gallery">
    <tbody>
        @forelse ($groupedData as $className => $fields)
        <tr>
            @php  $label = isset($fields[0]->label) ? $fields[0]->label : 'N/A'; @endphp
            <td>{!! $label !!}</td>
            
                @foreach ($fields as $field)
                @if(property_exists($field, 'isFile') &&  $field->isFile)
                    @if(is_array($field->value))
                    <td> 
                        @foreach ($field->value as $thisImg)
                            @php 
                                $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $thisImg);
                                $hasImages = true;
                            @endphp
                            <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                        @endforeach
                    </td>
                    @else
                    <td> 
                        @php 
                            $tImage = str_replace('assets/app/public/workflow-task-uploads/', '', $field->value);
                            $hasImages = true;
                        @endphp
                        <img data-index="{{ $globalCounter->value++ }}" class="thumbnail" src="{{ asset("storage/workflow-task-uploads/{$tImage}") }}" style="height: 100px;width:100px;object-fit:cover;">
                    </td>
                    @endif
                @else
                    @if(property_exists($field, 'value_label'))
                        @if($isPointChecklist)
                            @if(is_array($field->value_label))
                                <td> {!! implode(',', $field->value_label) !!} </td>
                            @else
                                <td> {!! $field->value_label !!} ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }}) </td>
                            @endif
                        @else
                            @if(is_array($field->value_label))
                                <td> {!! implode(',', $field->value_label) !!} </td>
                            @else
                                <td> {!! $field->value_label !!} </td>
                            @endif
                        @endif
                    @else
                        @if(is_array($field->value))
                            <td> {!! implode(',', $field->value) !!} </td>
                        @else
                            <td> {!! $field->value !!} </td>
                        @endif
                    @endif
                @endif
                @endforeach
        </tr>
        @empty
        <tr>
            <td>
                No Data Found
            </td>
        </tr>
        @endforelse
    </tbody>
</table>