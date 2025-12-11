@php
    $sectionData = [];
    $groupedData = [];
    $isPointChecklistArr = [];
    $headers = ['INSPECTION ITEM'];
    $headersSectionWise = ['SECTION'];
    $headersMain = ['TASK'];
    $totalSummary = [];

    if(!empty($tasks)) {
        $tempTasks = \App\Models\ChecklistTask::whereIn('id', $tasks)->get()->keyBy('id');

        foreach ($tempTasks as $taskId => $thisRow) {

            $versionedForm = \App\Helpers\Helper::getVersionForm($thisRow->version_id);
            $isPointChecklistArr[$taskId] = \App\Helpers\Helper::isPointChecklist($versionedForm);
            $headers[$taskId] = date('d F Y H:i', strtotime($thisRow->date));
            $headersMain[$taskId] = $thisRow->code;
            $headersSectionWise[$taskId] = date('d-m-Y', strtotime($thisRow->date));

            foreach ($thisRow->data as $item) {
                $groupedData[$item->className][$taskId][] = $item;
            }

            $iterationCount = 0;
            foreach (collect($thisRow->data)->groupBy('page')->values()->toArray() as $pKey => $totalSectionsRow) {
                if (!($iterationCount == 0 || $iterationCount == 1)) {
                    $thisVarients = \App\Helpers\Helper::categorizePoints($totalSectionsRow ?? []);
                    $thisTotal = count(\App\Helpers\Helper::selectPointsQuestions($totalSectionsRow));
                    $thisToBeCounted = $thisTotal - count($thisVarients['na']);

                    $thisFailed = abs(count(array_column($thisVarients['negative'], 'value')));
                    $thisAchieved = $thisToBeCounted - abs($thisFailed);

                    if ($thisFailed <= 0) {
                        $thisAchieved = array_sum(array_column($thisVarients['positive'], 'value'));
                    }
                    
                    if ($thisToBeCounted > 0) {
                        $thisPer = number_format(($thisAchieved / $thisToBeCounted) * 100, 2);
                    } else {
                        $thisPer = 0;
                    }

                    $titleOfSection = '';

                    if (is_array($versionedForm) && isset($versionedForm[$pKey])) {
                        $titleOfSection = collect($versionedForm[$pKey])->where('type', 'header')->get(0)->label ?? '';
                    }

                    if ($titleOfSection) {
                        $sectionData[$titleOfSection][] = $thisPer;
                    } else {
                        $sectionData[$titleOfSection][] = 'N/A';
                    }
                }

                $iterationCount++;
            }

            /** Calculation **/

            $varients = \App\Helpers\Helper::categorizePoints($thisRow->data ?? []);

            $total = count(\App\Helpers\Helper::selectPointsQuestions($thisRow->data));
            $toBeCounted = $total - count($varients['na']);

            $failed = abs(count(array_column($varients['negative'], 'value')));
            $achieved = $toBeCounted - abs($failed);

            if ($failed <= 0) {
                $achieved = array_sum(array_column($varients['positive'], 'value'));
            }

            if ($toBeCounted > 0) {
                $percentage = ($achieved / $toBeCounted) * 100;
            } else {
                $percentage = 0;
            }
            
            $totalSummary[$taskId] = [
                'total' => $total,
                'passed' => $achieved,
                'negative' => count($varients['negative']),
                'na' => count($varients['na']),
                'percentage' => number_format($percentage, 2),
                'final_result' => $percentage > 80 ? "Pass" : "Fail"
            ];

            /** Calculation **/

        }
    }

    $globalCounter = new \stdClass();
    $globalCounter->value = 0;
@endphp


@if(array_filter($isPointChecklistArr) && count($sectionData) > 0)
<table class="table table-bordered">
    <thead>
        <tr>
            @foreach ($headersSectionWise as $tId => $header)
                <th>
                    {!! $header !!}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @forelse ($sectionData as $sectionDataRowKey => $sectionDataRowDt)
            <tr>
                <td>
                    {!! $sectionDataRowKey !!}
                </td>
                @foreach ($sectionDataRowDt as $sectionDataRow)
                    <td style="@if(is_numeric($sectionDataRow) && $sectionDataRow > 80) background:#c8e6c9; @else background:#ffccbc; @endif">
                        {{ $sectionDataRow }}%
                    </td>
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
@endif



{{-- HTML --}}
<table class="table table-bordered table-stripped gallery">
    <thead>
        <tr>
            @foreach ($headersMain as $tId => $header)
                <th>
                    {{ $header }}
                </th>
            @endforeach
        </tr>
        <tr>
            @foreach ($headers as $header)
                <th>
                    {{ $header }}
                </th>
            @endforeach
        </tr>
    </thead>
    <tbody>
        @foreach ($groupedData as $className => $fR)
        <tr>

            <td>
                @php
                    $label = reset($fR);
                    $label = Helper::getQuestionField($label);
                @endphp
                {!! $label !!}
            </td>

            {{-- Result LOOP --}}
            @foreach ($fR as $tK => $fields)
                
                <td>
                    <table class="table table-bordered table-stripped gallery">
                        <tbody>
                            <tr>
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
                                            @if(isset($isPointChecklistArr[$tK]) && $isPointChecklistArr[$tK])
                                                @if(is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} ({{ is_array($field->value) ? implode(',', $field->value) : $field->value }}) </td>
                                                @endif
                                            @else
                                                @if(is_array($field->value_label))
                                                    <td> {!! implode(',', $field->value_label) !!} </td>
                                                @else
                                                    <td> {!! $field->value_label !!} {{ is_array($field->value) ? implode(',', $field->value) : $field->value }} </td>
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
                        </tbody>
                    </table>
                </td>

            @endforeach            
            {{-- Result LOOP --}}

        </tr>
        @endforeach
    </tbody>
</table>
{{-- HTML --}}



@if(array_filter($isPointChecklistArr))
<table class="table table-striped table-bordered">
    <tbody>
        <tr>
            <td>Total Questions</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['total'] }}
             </td>
            @empty
            @endforelse
        </tr>
        <tr>
            <td>Passed</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['passed'] }}
             </td>
            @empty
            @endforelse
        </tr>
        <tr>
            <td>Failed</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['negative'] }}
             </td>
            @empty
            @endforelse
        </tr>
        <tr>
            <td>N/A</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['na'] }}
             </td>
            @empty
            @endforelse
        </tr>
        <tr>
            <td>Percentage</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['percentage'] }}
             </td>
            @empty
            @endforelse
        </tr>
        <tr>
            <td>Final Result</td>
            @forelse ($totalSummary as $taskId => $item)
             <td>
                {{ $item['final_result'] }}
             </td>
            @empty
            @endforelse
        </tr>
    </tbody>
</table>
@endif