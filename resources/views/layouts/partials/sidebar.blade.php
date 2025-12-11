<aside class="main-sidebar">
    <!-- Brand Logo -->
    <div class="logo" style="display:none;">
        <a href="#" class="brand-link"><img src="{!! url('assets/images/fursaa_newLogo.png') !!}" alt="Fursa Logo" class="img-logo" style="width:220px;"></a>
    </div>
    <h1 class="panel-title">{{strtoupper(auth()->user()->roles[0]->name ?? '')}} PANEL</h1>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav" role="menu">

                @auth

                <li class="nav-item">
                    <a href="{{ auth()->user()->can('dom-dashboard') ? route('dom-dashboard') : url('') }}" class="nav-link"> Dashboard </a>
                </li>

                @if(auth()->user()->can('flagged-items-dashboard'))
                <li class="nav-item">
                    <a href="{{ route('flagged-items-dashboard') }}" class="nav-link"> Inspection Dashboard </a>
                </li>
                @endif

                @if(auth()->user()->can('monthly-report-dom-checklists'))
                <li class="nav-item">
                    <a href="{{ route('monthly-report-dom-checklists') }}" class="nav-link"> Monthly Reports </a>
                </li>
                @endif

                @if(auth()->user()->can('users.index') || auth()->user()->can('roles.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> User Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('users.index'))
                            <li class="nav-item"><a href="{{ route('users.index') }}" class="nav-link"> Users </a></li>
                        @endif

                        @if(auth()->user()->can('roles.index'))
                            <li class="nav-item"><a href="{{ route('roles.index') }}" class="nav-link"> Roles </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('stores.index') || auth()->user()->can('corporate-office.index') || auth()->user()->can('departments.index') || auth()->user()->can('store-types.index') || auth()->user()->can('model-types.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Branch Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if( auth()->user()->can( 'store-categories.index' ) )
                            <li class="nav-item"><a href="{{ route( 'store-categories.index' ) }}" class="nav-link">Locations Categories</a></li>
                        @endif

                        @if(auth()->user()->can('store-types.index'))
                            <li class="nav-item"><a href="{{ route('store-types.index') }}" class="nav-link"> Locations Types </a></li>
                        @endif

                        @if(auth()->user()->can('model-types.index'))
                            <li class="nav-item"><a href="{{ route('model-types.index') }}" class="nav-link"> Locations Model Types </a></li>
                        @endif

                        @if(auth()->user()->can('stores.index'))
                            <li class="nav-item"><a href="{{ route('stores.index') }}" class="nav-link"> Locations </a></li>
                        @endif
        
                        @if(auth()->user()->can('corporate-office.index'))
                            <li class="nav-item"><a href="{{ route('corporate-office.index') }}" class="nav-link"> Corporate Offices </a></li>
                        @endif
        
                        @if(auth()->user()->can('departments.index'))
                            <li class="nav-item"><a href="{{ route('departments.index') }}" class="nav-link"> Departments </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('checklists.index') || auth()->user()->can('checklist-scheduling.index') || auth()->user()->can('scheduled-tasks.index') || auth()->user()->can('reassignments.index') || auth()->user()->can('reschedules'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Checklist Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('checklists.index'))
                            <li class="nav-item"><a href="{{ route('checklists.index') }}" class="nav-link"> Checklists Templates </a></li>
                        @endif
        
                        @if(auth()->user()->can('scheduled-tasks.index'))
                            <li class="nav-item"><a href="{{ route('scheduled-tasks.index') }}" class="nav-link"> Scheduled Tasks </a></li>
                        @endif

                        @if(auth()->user()->can('reschedules'))
                            <li class="nav-item"><a href="{{ route('reschedules') }}" class="nav-link"> Rescheduled Tasks </a></li>
                        @endif

                        @if(auth()->user()->can('reassignments.index'))
                            <li class="nav-item"><a href="{{ route('reassignments.index') }}" class="nav-link"> Re-Do </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if(auth()->user()->can('ticket-management.index') || auth()->user()->can('particulars.index') || auth()->user()->can('issues.index') || auth()->user()->can('ticket-escalations.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Ticket System <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('particulars.index'))
                            <li class="nav-item"><a href="{{ route('particulars.index') }}" class="nav-link"> Particulars </a></li>
                        @endif

                        @if(auth()->user()->can('issues.index'))
                            <li class="nav-item"><a href="{{ route('issues.index') }}" class="nav-link"> Issues </a></li>
                        @endif

                        @if(auth()->user()->can('ticket-management.index'))
                            <li class="nav-item"><a href="{{ route('ticket-management.index') }}" class="nav-link"> Tickets </a></li>
                        @endif

                        @if(auth()->user()->can('ticket-escalations.index'))
                            <li class="nav-item"><a href="{{ route('ticket-escalations.index') }}" class="nav-link"> Escalations </a></li>
                        @endif
                    </ul>
                </li>
                @endif               

                @if(auth()->user()->can('workflow-checklists.index') || auth()->user()->can('workflow-templates.index') || auth()->user()->can('workflow-assignments.index'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Workflow Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('workflow-checklists.index'))
                            <li class="nav-item"><a href="{{ route('workflow-checklists.index') }}" class="nav-link"> Checklists </a></li>
                        @endif
                        @if(auth()->user()->can('workflow-templates.index'))
                            <li class="nav-item"><a href="{{ route('workflow-templates.index') }}" class="nav-link"> Templates </a></li>
                        @endif
                        @if(auth()->user()->can('workflow-assignments.index'))
                            <li class="nav-item"><a href="{{ route('workflow-assignments.index') }}" class="nav-link"> Assignments </a></li>
                        @endif
                    </ul>
                </li>
                @endif


                @if(auth()->user()->can('topics.index') || auth()->user()->can('contents.index') || auth()->user()->can('content-analytics'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Learning Management <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('topics.index'))
                            <li class="nav-item"><a href="{{ route('topics.index') }}" class="nav-link"> Categories </a></li>
                        @endif

                        @if(auth()->user()->can('contents.index'))
                            <li class="nav-item"><a href="{{ route('contents.index') }}" class="nav-link"> Content </a></li>
                        @endif

                        @if(auth()->user()->can('content-analytics'))
                            <li class="nav-item"><a href="{{ route('content-analytics') }}" class="nav-link"> View Analytics </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                @if( auth()->user()->can('documents.index') || auth()->user()->can('document-upload.index') )
                <li class="nav-item">
                    <a href="#" class="nav-link">Document Management<i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if( auth()->user()->can( 'document-dashboard' ) )
                            <li class="nav-item"><a href="{{ route( 'document-dashboard' ) }}" class="nav-link">Dashboard</a></li>
                        @endif

                        @if( auth()->user()->can( 'documents.index' ) )
                            <li class="nav-item"><a href="{{ route( 'documents.index' ) }}" class="nav-link">Document Type</a></li>
                        @endif

                        @if( auth()->user()->can( 'document-upload.index' ) )
                            <li class="nav-item"><a href="{{ route( 'document-upload.index' ) }}" class="nav-link">Document List</a></li>
                        @endif
                    </ul>
                </li>
                @endif            

                @if(auth()->user()->can('notification-templates.index') || auth()->user()->can('imported-schedulings-history') || auth()->user()->can('settings.edit') || auth()->user()->can('notification-center'))
                <li class="nav-item">
                    <a href="#" class="nav-link"> Settings <i class="bi bi-chevron-down"></i></a>
                    <ul class="nav nav-dropdown">
                        @if(auth()->user()->can('notification-templates.index'))
                            <li class="nav-item"><a href="{{ route('notification-templates.index') }}" class="nav-link"> Notification Templates </a></li>
                        @endif

                        @if(auth()->user()->can('imported-schedulings-history'))
                            <li class="nav-item"><a href="{{ route('imported-schedulings-history') }}" class="nav-link"> XLSX Import History </a></li>
                        @endif

                        @if(auth()->user()->can('settings.edit'))
                            <li class="nav-item"><a href="{{ route('settings.edit') }}" class="nav-link"> Settings </a></li>
                        @endif

                        @if(auth()->user()->can('notification-center'))
                            <li class="nav-item"><a href="{{ route('notification-center') }}" class="nav-link"> Notifications </a></li>
                        @endif
                    </ul>
                </li>
                @endif

                <li class="nav-item">
                    <ul class="nav nav-dropdown">
                        <li class="nav-item"><a href="{{ route('logout') }}" class="nav-link">Logout</a></li>
                    </ul>
                </li>
                @endauth

            </ul>
        </nav>
        
        <div class="version"><img src="{!! url('assets/images/version.svg') !!}"> VERSION 1.0.0</div>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
