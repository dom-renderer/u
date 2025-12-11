<nav>
    <ul class="nav nav-pills">
        <li role="presentation" class="nav-item">
            <a class="nav-link {!! $tools->fullUrlIs(route(\App\Models\TicketSetting::grab('main_route') . '.index')) ? "active" : "" !!}"
                href="{{ route(\App\Models\TicketSetting::grab('main_route') . '.index') }}">Active Tickets
                <span class="badge badge-pill {!! $tools->fullUrlIs(route(\App\Models\TicketSetting::grab('main_route') . '.index')) ? "" : "bg-primary" !!}">
                     <?php
                        // if ($u->isAdmin()) {
                        //     echo \App\Models\Ticket::active()->count();
                        // } elseif ($u->isAgent()) {
                            if( (isset(auth()->user()->roles[0]->id) && in_array(auth()->user()->roles[0]->id, [\App\Helpers\Helper::$roles['admin'], 13])) ){
                                echo \App\Models\Ticket::active()->count();
                            } else {
                                echo \App\Models\Ticket::active()->agentUserTickets($u->id)->count();
                            }
                        // } else {
                        //     echo \App\Models\Ticket::userTickets($u->id)->active()->count();
                        // }
                    ?>
                </span>
            </a>
        </li>
        <li role="presentation" class="nav-item">
            <a class="nav-link {!! $tools->fullUrlIs(route(\App\Models\TicketSetting::grab('main_route') . '-complete')) ? "active" : "" !!}"
                 href="{{ route(\App\Models\TicketSetting::grab('main_route') . '-complete') }}">Completed Tickets
                <span class="badge badge-pill {!! $tools->fullUrlIs(route(\App\Models\TicketSetting::grab('main_route') . '-complete')) ? "" : "bg-primary" !!}">
                    <?php
                        // if ($u->isAdmin()) {
                        //     echo \App\Models\Ticket::complete()->count();
                        // } elseif ($u->isAgent()) {
                            if( (isset(auth()->user()->roles[0]->id) && in_array(auth()->user()->roles[0]->id, [\App\Helpers\Helper::$roles['admin'], 13])) ){
                                echo \App\Models\Ticket::complete()->count();
                            } else {
                                echo \App\Models\Ticket::complete()->agentUserTickets($u->id)->count();
                            }
                        // } else {
                        //     echo \App\Models\Ticket::userTickets($u->id)->complete()->count();
                      // }
                    ?>
                </span>
            </a>
        </li>

        {{-- @if(isset(auth()->user()->roles[0]->id) && auth()->user()->roles[0]->id== Helper::$roles['admin'] && $u->isAdmin())
            <!-- <li role="presentation" class="nav-item">
                <a class="nav-link {!! $tools->fullUrlIs(action('\App\Http\Controllers\DashboardController@index')) || Request::is($setting->grab('admin_route').'/indicator*') ? "active" : "" !!}"
                    href="{{ action('\App\Http\Controllers\DashboardController@index') }}">Dashboard</a>
            </li> -->

            <li role="presentation" class="nav-item dropdown">

                <a class="nav-link dropdown-toggle {!!
                    $tools->fullUrlIs(action('\App\Http\Controllers\StatusesController@index').'*') ? "active" : "" !!}"
                    data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                    Settings
                </a>
                <div class="dropdown-menu">
                    <a  class="dropdown-item {!! $tools->fullUrlIs(action('\App\Http\Controllers\StatusesController@index').'*') ? "active" : "" !!}"
                        href="{{ action('\App\Http\Controllers\StatusesController@index') }}">Statuses</a>
                    <a  class="dropdown-item {!! $tools->fullUrlIs(action('\App\Http\Controllers\PrioritiesController@index').'*') ? "active" : "" !!}"
                        href="{{ action('\App\Http\Controllers\PrioritiesController@index') }}">Priorities</a>

                </div>
            </li>
        @endif --}}

    </ul>
</nav>
