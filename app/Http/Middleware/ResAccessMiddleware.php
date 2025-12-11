<?php

namespace App\Http\Middleware;

use Closure;
use App\Helpers\LaravelVersion;
use App\Models\Agent;
use App\Models\TicketSetting;

class ResAccessMiddleware
{
    /**
     * Run the request filter.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Agent::isTicketAdmin()) {
            return $next($request);
        }

        // All Agents have access in none restricted mode
        if (TicketSetting::grab('agent_restrict') == 'no') {
            if (Agent::isAgent()) {
                return $next($request);
            }
        }

        // if this is a ticket show page
        if ($request->route()->getName() == TicketSetting::grab('main_route').'.show') {
            if (LaravelVersion::lt('5.2.0')) {
                $ticket_id = $request->route(TicketSetting::grab('main_route'));
            } else {
                $ticket_id = $request->route('ticket');
            }
        }

        // if this is a new comment on a ticket
        if ($request->route()->getName() == TicketSetting::grab('main_route').'-comment.store') {
            $ticket_id = $request->get('ticket_id');
        }

        // Assigned Agent has access in the restricted mode enabled
        if (Agent::isAgent() && Agent::isAssignedAgent($ticket_id)) {
            return $next($request);
        }

        // Ticket Owner has access
        if (Agent::isTicketOwner($ticket_id)) {
            return $next($request);
        }

        return redirect()->route(TicketSetting::grab('main_route') . '.index')
            ->with('warning', 'You are not permitted to do this action.');
    }
}
