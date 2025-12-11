<?php

namespace App\ViewComposers;

use App\Http\Controllers\ToolsController;
use App\Helpers\EditorLocale;
use App\Models\Agent;
use App\Models\TicketSetting;

class TicketItComposer
{
    public static function settings(&$u)
    {
        view()->composer('ticketit::*', function ($view) use (&$u) {
            if (auth()->check()) {
                if ($u === null) {
                    $u = Agent::find(auth()->user()->id);
                }
                $view->with('u', $u);
            }
            $setting = new TicketSetting();
            $view->with('setting', $setting);
        });
    }

    public static function general()
    {
        // Passing to views the master view value from the setting file
        view()->composer('ticketit::*', function ($view) {
            $tools = new ToolsController();
            $master = TicketSetting::grab('master_template');
            $email = TicketSetting::grab('email.template');
            $view->with(compact('master', 'email', 'tools'));
        });
    }

    public static function codeMirror()
    {
        // Passing to views the master view value from the setting file
        view()->composer('ticketit::*', function ($view) {
            $editor_enabled = TicketSetting::grab('editor_enabled');
            $codemirror_enabled = TicketSetting::grab('editor_html_highlighter');
            $codemirror_theme = TicketSetting::grab('codemirror_theme');
            $view->with(compact('editor_enabled', 'codemirror_enabled', 'codemirror_theme'));
        });
    }

    public static function summerNotes()
    {
        view()->composer('ticketit::tickets.partials.summernote', function ($view) {

            $editor_locale = EditorLocale::getEditorLocale();
            $editor_options = file_get_contents(base_path(TicketSetting::grab('summernote_options_json_file')));

            $view->with(compact('editor_locale', 'editor_options'));
        });
    }

    public static function sharedAssets()
    {
        //inlude font awesome css or not
        view()->composer('ticketit::shared.assets', function ($view) {
            $include_font_awesome = TicketSetting::grab('include_font_awesome');
            $view->with(compact('include_font_awesome'));
        });
    }
}
