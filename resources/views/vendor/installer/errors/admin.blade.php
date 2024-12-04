@extends('vendor.installer.layout')

@section('content')
  <div class="card">
      <div class="card-content black-text">
              <div class="center-align">
                  <p class="card-title">{{ __('OOPS') }}</p>
                  <hr>
              </div>
              <div class="center-align">
                  {{ __('Please verify your purchase code and username.') }}
              </div>
      </div>
      <div class="card-action right-align">

      </div>
  </div>
@endsection
