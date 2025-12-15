{{--
    Vista del Widget de Helpdesk
    
    Generada automáticamente por: php artisan helpdeskwidget:install
    
    Personaliza esta vista según las necesidades de tu proyecto.
    Compatible con AdminLTE v3.
--}}
@extends('layouts.app')

@section('page_title', 'Centro de Soporte')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-headset mr-2"></i>
                            Centro de Soporte
                        </h3>
                    </div>
                    <div class="card-body">
                        <div id="helpdesk-widget-wrapper" style="width: 100%;">
                            <x-custom-helpdesk-widget width="100%" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        #helpdesk-widget-wrapper iframe {
            width: 100% !important;
            border: none !important;
            display: block;
            min-height: 600px;
            transition: height 0.3s ease;
        }
    </style>
@endsection
