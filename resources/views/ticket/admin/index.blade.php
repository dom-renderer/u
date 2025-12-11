@extends('ticket.layouts.master')

@section('title', "Ticket System Dashboard"." - ".Helper::setting()->name)

<style>
    @media (max-width:576px) {
        .nav-link {
            padding: 6px 9px !important;
        }
    }
</style>

@section('ticketit_extra_content')
    @if($tickets_count)
        <div class="card-deck mb-3">
            <div class="card bg-light">
                <div class="card-body row d-flex align-items-center">
                    <div class="col-3" style="font-size: 5em;">
                        <i class="fas fa-th"></i>
                    </div>
                    <div class="col-9 text-right">
                        <h1>{{ $tickets_count }}</h1>
                        <div>Total tickets</div>
                    </div>
                </div>
            </div>

            <div class="card bg-danger">
                <div class="card-body row d-flex align-items-center">
                    <div class="col-3" style="font-size: 5em;">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <div class="col-9 text-right">
                        <h1>{{ $open_tickets_count }}</h1>
                        <div>Open tickets</div>
                    </div>
                </div>
            </div>

            <div class="card bg-success">
                <div class="card-body row d-flex align-items-center">
                    <div class="col-3" style="font-size: 5em;">
                        <i class="fas fa-thumbs-up"></i>
                    </div>
                    <div class="col-9 text-right">
                        <h1>{{ $closed_tickets_count }}</h1>
                        <span>Closed tickets</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-lg-8  mt-3 mb-4">
                <div class="card ">
                    <div class="card-header d-flex justify-content-between align-items-baseline flex-wrap">
                        <div><i class="fas fa-chart-bar fa-fw"></i> Performance Indicator</div>
                        <div class="btn-group">
                            <button type="button" class="btn btn-secondary btn-sm dropdown-toggle" data-toggle="dropdown">
                                Periods
                                <span class="caret"></span>
                            </button>
                            <div class="dropdown-menu" role="menu">
                                <a class="dropdown-item" href="{{ action('\App\Http\Controllers\DashboardController@index', 2) }}">
                                    3 months
                                </a>
                                <a class="dropdown-item" href="{{ action('\App\Http\Controllers\DashboardController@index', 5) }}">
                                    6 months
                                </a>
                                <a class="dropdown-item" href="{{ action('\App\Http\Controllers\DashboardController@index', 11) }}">
                                    12 months
                                </a>
                            </div>
                        </div>
                    </div>
                    <div class="panel-body">
                        <div id="curve_chart" style="width: 100%; height: 350px"></div>
                    </div>
                </div>
                <div class="card-deck my-3">
                    <div class="card ">
                        <div class="card-header">
                        Tickets share per category
                        </div>
                        <div class="panel-body">
                            <div id="catpiechart" style="width: auto; height: 350;"></div>
                        </div>
                    </div>
                    <div class="card ">
                        <div class="card-header">
                        Tickets share per agent
                        </div>
                        <div class="panel-body">
                            <div id="agentspiechart" style="width: auto; height: 350;"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4 mt-3 mb-5">
                <nav>
                    <ul class="nav nav-pills nav-justified">
                        <li class="nav-item">
                            <a class="nav-link {{$active_tab == "cat" ? "active" : ""}}" data-toggle="pill" href="#information-panel-categories">
                                <i class="fas fa-folder"></i>
                                <small>Categories</small>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$active_tab == "agents" ? "active"  : ""}}" data-toggle="pill" href="#information-panel-agents">
                                <i class="fas fa-user-secret"></i>
                                <small>Agents</small>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{$active_tab == "users" ? "active" : ""}}" data-toggle="pill" href="#information-panel-users">
                                <i class="fas fa-users"></i>
                                <small>Users</small>
                            </a>
                        </li>
                    </ul>
                </nav>
                <br>
                <div class="tab-content">
                    <div id="information-panel-categories" class="list-group tab-pane fade {{$active_tab == "cat" ? "show active" : ""}}">
                        <a href="#" class="list-group-item list-group-item-action disabled">
                            <span>Category
                                <span class="badge badge-pill badge-secondary">Total</span>
                            </span>
                            <small class="pull-right text-muted">
                                <em>
                                Open / Closed
                                </em>
                            </small>
                        </a>
                        @foreach($categories as $category)
                            <a href="#" class="list-group-item list-group-item-action">
                        <span style="color: {{ $category->color }}">
                            {{ $category->name }} <span class="badge badge-pill badge-secondary">{{ $category->tickets()->count() }}</span>
                        </span>
                        <span class="pull-right text-muted small">
                            <em>
                                {{ $category->tickets()->whereNull('completed_at')->count() }} /
                                 {{ $category->tickets()->whereNotNull('completed_at')->count() }}
                            </em>
                        </span>
                            </a>
                        @endforeach
                        {!! $categories->render("pagination::bootstrap-4") !!}
                    </div>
                    <div id="information-panel-agents" class="list-group tab-pane fade {{$active_tab == "agents" ? "show active" : ""}}">
                        <a href="#" class="list-group-item list-group-item-action disabled">
                            <span>Agent
                                <span class="badge badge-pill badge-secondary">Total</span>
                            </span>
                            <span class="pull-right text-muted small">
                                <em>
                                Open / Closed
                                </em>
                            </span>
                        </a>
                        @foreach($agents as $agent)
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>
                                    {{ $agent->name }}
                                    <span class="badge badge-pill badge-secondary">
                                        {{ $agent->agentTickets(false)->count()  +
                                         $agent->agentTickets(true)->count() }}
                                    </span>
                                </span>
                                <span class="pull-right text-muted small">
                                    <em>
                                        {{ $agent->agentTickets(false)->count() }} /
                                         {{ $agent->agentTickets(true)->count() }}
                                    </em>
                                </span>
                            </a>
                        @endforeach
                        {!! $agents->render("pagination::bootstrap-4") !!}
                    </div>
                    <div id="information-panel-users" class="list-group tab-pane fade {{$active_tab == "users" ? "show active" : ""}}">
                        <a href="#" class="list-group-item list-group-item-action disabled">
                            <span>User
                                <span class="badge badge-pill badge-secondary">Total</span>
                            </span>
                            <span class="pull-right text-muted small">
                                <em>
                                Open / Closed
                                </em>
                            </span>
                        </a>
                        @foreach($users as $user)
                            <a href="#" class="list-group-item list-group-item-action">
                                <span>
                                    {{ $user->name }}
                                    <span class="badge badge-pill badge-secondary">
                                        {{ $user->userTickets(false)->count()  +
                                         $user->userTickets(true)->count() }}
                                    </span>
                                </span>
                                <span class="pull-right text-muted small">
                                    <em>
                                        {{ $user->userTickets(false)->count() }} /
                                        {{ $user->userTickets(true)->count() }}
                                    </em>
                                </span>
                            </a>
                        @endforeach
                        {!! $users->render("pagination::bootstrap-4") !!}
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="card text-center">
        No tickets yet
        </div>
    @endif
@stop
@section('script')
    @if($tickets_count)
    {{--@include('ticketit::shared.footer')--}}
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script type="text/javascript">
        // Load the Visualization API and the corechart package.
        google.charts.load('current', {'packages':['corechart']});
        // Set a callback to run when the Google Visualization API is loaded.
        google.charts.setOnLoadCallback(drawChart);
        // performance line chart
        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ["Month", "{!! implode('", "', $monthly_performance['categories']) !!}"],
                @foreach($monthly_performance['interval'] as $month => $records)
                    ["{{ $month }}", {!! implode(',', $records) !!}],
                @endforeach
            ]);
            var options = {
                title: 'How many days in average to resolve a ticket?',
                curveType: 'function',
                legend: {position: 'right'},
                vAxis: {
                    viewWindowMode:'explicit',
                    format: '#',
                    viewWindow:{
                        min:0
                    }
                }
            };
            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
            chart.draw(data, options);
            // Categories Pie Chart
            var cat_data = google.visualization.arrayToDataTable([
              ['Category', 'Tickets'],
              @foreach($categories_share as $cat_name => $cat_tickets)
                    ['{!! addslashes($cat_name) !!}', {{ $cat_tickets }}],
              @endforeach
            ]);
            var cat_options = {
              title: 'Tickets distribution per category',
              legend: {position: 'bottom'}
            };
            var cat_chart = new google.visualization.PieChart(document.getElementById('catpiechart'));
            cat_chart.draw(cat_data, cat_options);
            // Agents Pie Chart
            var agent_data = google.visualization.arrayToDataTable([
              ['Agent', 'Tickets'],
              @foreach($agents_share as $agent_name => $agent_tickets)
                    ['{!! addslashes($agent_name) !!}', {{ $agent_tickets }}],
              @endforeach
            ]);
            var agent_options = {
              title: 'Tickets distribution per Agent',
              legend: {position: 'bottom'}
            };
            var agent_chart = new google.visualization.PieChart(document.getElementById('agentspiechart'));
            agent_chart.draw(agent_data, agent_options);
        }
    </script>
    @endif
@append
