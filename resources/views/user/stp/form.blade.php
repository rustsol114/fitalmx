@extends('user.layouts.app')

@section('content')
<div class="bg-white pxy-62 shadow" id="stpAccountRegistration">
    <h1 class="mb-4 f-26 gilroy-Semibold text-uppercase text-center">{{ __('STP Crypto Account Registration') }}</h1>

    @include('user.common.alert')

    <form method="post" action="{{ route('user.deposit.stp_register') }}" id="stpRegistrationForm">
        @csrf
        <div class="mt-28 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="accountType">{{ __('Account Type') }}</label>
            <div class="avoid-blink">
                <select class="select2 @error('accountType') is-invalid @enderror" name="accountType" id="accountType" required>
                    <option value="person" {{ old('accountType') == 'person' ? 'selected' : '' }}>{{ __('Natural Person') }}</option>
                    <option value="company" {{ old('accountType') == 'company' ? 'selected' : '' }}>{{ __('Legal Entity') }}</option>
                </select>
                @error('accountType')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="empresa">{{ __('Company Name') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('empresa') is-invalid @enderror" 
                   name="empresa" id="empresa" value="{{ old('empresa') }}" required>
            @error('empresa')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-28 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="pais">{{ __('Country') }}</label>
            <div class="avoid-blink">
                <select class="select2 @error('pais') is-invalid @enderror" name="pais" id="pais" required>
                    @foreach ($countries as $key => $country)
                        <option value="{{ $key }}" {{ old('pais', '187') == $key ? 'selected' : '' }}>{{ $country }}</option>
                    @endforeach
                </select>
                @error('pais')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="cuentaNoSTP">{{ __('Non-STP Account') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('cuentaNoSTP') is-invalid @enderror" 
                   name="cuentaNoSTP" id="cuentaNoSTP" value="{{ old('cuentaNoSTP') }}" required>
            @error('cuentaNoSTP')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="institucionCuentaNoSTP">{{ __('Bank Institution') }}</label>
            <div class="avoid-blink">
            <select class="select2 @error('institucionCuentaNoSTP') is-invalid @enderror" name="institucionCuentaNoSTP" id="institucionCuentaNoSTP" required>
            @foreach ($supportedBanks as $key => $bank)
                        <option value="{{ $key }}" {{ old('institucionCuentaNoSTP', '40012') == $key ? 'selected' : '' }}>{{ $bank }}</option>
                    @endforeach
            </select>
            @error('institucionCuentaNoSTP')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            </div>
        </div>

        <div class="mt-20 param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="entidadFederativa">{{ __('Federal Entity') }}</label>
            <div class="avoid-blink">
            <select class="select2 @error('entidadFederativa') is-invalid @enderror" name="entidadFederativa" id="entidadFederativa" required>
            @foreach ($entidadFederativa as $key => $fed)
                        <option value="{{ $key }}" {{ old('entidadFederativa', '9') == $key ? 'selected' : '' }}>{{ $fed }}</option>
                    @endforeach
            </select>
            @error('entidadFederativa')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            </div>
        </div>

        <div class="mt-20  param-ref">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="actividadEconomica">{{ __('Economic Activity') }}</label>
            <div class="avoid-blink">
            <select class="select2 @error('actividadEconomica') is-invalid @enderror" name="actividadEconomica" id="actividadEconomica" required>
            @foreach ($actividadEconomica as $key => $ec)
                        <option value="{{ $key }}" {{ old('actividadEconomica', '31') == $key ? 'selected' : '' }}>{{ $ec }}</option>
                    @endforeach
            </select>
            </select>
            @error('actividadEconomica')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
            </div>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-lg btn-primary" id="stpRegistrationSubmitBtn">
                <span class="px-1" id="stpRegistrationSubmitBtnText">{{ __('Register') }}</span>
                <span class="spinner d-none">
                    <i class="fa fa-spinner fa-spin"></i>
                </span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>
<script src="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/js/select2.min.js') }}"></script>

<script type="text/javascript">
'use strict';
$(document).ready(function() {
    // Initialize Select2
    $('.select2').select2({
        width: '100%'
    });

    // Form validation and submission
    $('#stpRegistrationForm').validate({
        rules: {
            empresa: {
                required: true
            },
            pais: {
                required: true
            },
            cuentaNoSTP: {
                required: true
            },
            institucionCuentaNoSTP: {
                required: true
            },
            entidadFederativa: {
                required: true
            },
            actividadEconomica: {
                required: true
            }
        },
        submitHandler: function(form) {
            const accountType = $('#accountType').val();
            const apiEndpoint = accountType === 'person' 
                ? 'https://market-fital-api.fitalmx.com/api/stp/fisica-crypto'
                : 'https://market-fital-api.fitalmx.com/api/stp/moral-crypto';

            const formData = {
                empresa: $('#empresa').val(),
                pais: parseInt($('#pais').val()),
                paisNacimiento: accountType === 'person' ? parseInt($('#pais').val()) : undefined,
                cuentaNoSTP: $('#cuentaNoSTP').val(),
                institucionCuentaNoSTP: parseInt($('#institucionCuentaNoSTP').val()),
                entidadFederativa: parseInt($('#entidadFederativa').val()),
                actividadEconomica: parseInt($('#actividadEconomica').val())
            };

            $("#stpRegistrationSubmitBtn").attr("disabled", true);
            $(".spinner").removeClass("d-none");
            $("#stpRegistrationSubmitBtnText").text("{{ __('Processing...') }}");

            // Make API call
            $.ajax({
                url: apiEndpoint,
                type: 'PUT',
                contentType: 'application/json',
                headers: {
                    'Authorization': 'Bearer ' + yourAuthTokenHere // You'll need to provide the token
                },
                data: JSON.stringify(formData),
                success: function(response) {
                    // Handle success
                    window.location.href = '/success-page'; // Redirect to success page
                },
                error: function(xhr, status, error) {
                    // Handle error
                    $("#stpRegistrationSubmitBtn").attr("disabled", false);
                    $(".spinner").addClass("d-none");
                    $("#stpRegistrationSubmitBtnText").text("{{ __('Register') }}");
                    alert('Registration failed: ' + error);
                }
            });
        }
    });
});
</script>
@endpush