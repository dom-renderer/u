<div class="table-responsive">
    <table class="table table-striped table-bordered table-hover">
        <thead>
        <th class="text-center">#</th>
        <th>Slug</th>
        <th>Default Value</th>
        <th>My Value</th>
        <th class="text-center">Lang</th>
        <th class="text-center">Edit</th>
        </thead>
        <tbody>
        @foreach($configurations_by_sections['editor'] as $ckey => $configuration)
            <tr>
                <td class="text-center">{!! $ckey + 1 !!}</td>
                <td>{!! $configuration->slug !!}</td>
                <td>{!! $configuration->default !!}</td>
                <td><a href="{!! route($setting->grab('admin_route').'.configuration.edit', [$configuration->id]) !!}" title="{{ "Edit".' '.$configuration->slug }}" data-bs-toggle="tooltip">{!! $configuration->value !!}</a></td>
                <td class="text-center">{!! $configuration->lang !!}</td>
                <td class="text-center">
                <a href="{{ route($setting->grab('admin_route').'.configuration.edit', [$configuration->id]) }}" class='btn btn-sm btn-warning' title="{{ "Edit".' '.$configuration->slug }}" data-bs-toggle="tooltip"><i class='bi bi-pen-fill'></i></a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>
