@extends('layouts.app')

@section('content')

@include('partials._hero')

@include('partials._input_form')

{{-- ── RESULT CARD ── --}}
<div class="card" id="resultCard" style="display:none">

  @include('partials._tabs')

  {{-- ── Feature panels (shown/hidden by JS tab switching) ── --}}
  @include('partials._panel_today')
  @include('partials._panel_chart')
  @include('partials._panel_panchanga')
  @include('partials._panel_masa')
  @include('partials._panel_muhrat')
  @include('partials._panel_tarabal_murti')
  @include('partials._panel_planets')
  @include('partials._panel_festival')

</div>{{-- /resultCard --}}

@endsection

@section('scripts')
@include('partials._js_main')
@include('partials._js_festival')
@endsection
