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
                    <option value="person" {{ old('accountType') == 'person' ? 'selected' : '' }}>{{ __('Person') }}</option>
                    <option value="company" {{ old('accountType') == 'company' ? 'selected' : '' }}>{{ __('Company') }}</option>
                </select>
                @error('accountType')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="cuenta">{{ __('Account Number') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('cuenta') is-invalid @enderror" 
                   name="cuenta" id="cuenta" maxlength="18" value="{{ old('cuenta') }}" required>
            @error('cuenta')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="empresa">{{ __('Company Name') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('empresa') is-invalid @enderror" 
                   name="empresa" id="empresa" maxlength="15" value="{{ old('empresa') }}" required>
            @error('empresa')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="nombre">{{ __('Full Name') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('nombre') is-invalid @enderror" 
                   name="nombre" id="nombre" maxlength="150" value="{{ old('nombre') }}">
            @error('nombre')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top person-field">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="apellidoPaterno">{{ __('Paternal Surname') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('apellidoPaterno') is-invalid @enderror" 
                   name="apellidoPaterno" id="apellidoPaterno" maxlength="60" value="{{ old('apellidoPaterno') }}">
            @error('apellidoPaterno')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top person-field">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="apellidoMaterno">{{ __('Maternal Surname') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('apellidoMaterno') is-invalid @enderror" 
                   name="apellidoMaterno" id="apellidoMaterno" maxlength="60" value="{{ old('apellidoMaterno') }}">
            @error('apellidoMaterno')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="rfcCurp">{{ __('RFC/CURP') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('rfcCurp') is-invalid @enderror" 
                   name="rfcCurp" id="rfcCurp" maxlength="18" value="{{ old('rfcCurp') }}" required>
            @error('rfcCurp')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="fechaNacimiento">{{ __('Date of Birth/Constitution') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('fechaNacimiento') is-invalid @enderror" 
                   name="fechaNacimiento" id="fechaNacimiento" placeholder="YYYYMMDD" 
                   pattern="\d{8}" title="Please enter date in YYYYMMDD format"
                   maxlength="8" value="{{ old('fechaNacimiento') }}" required>
            @error('fechaNacimiento')
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
                   name="cuentaNoSTP" id="cuentaNoSTP" maxlength="18" value="{{ old('cuentaNoSTP') }}" required>
            @error('cuentaNoSTP')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="mt-20 label-top person-field">
            <label class="gilroy-medium text-gray-100 mb-2 f-15" for="curp">{{ __('CURP') }}</label>
            <input type="text" class="form-control input-form-control apply-bg l-s2 @error('curp') is-invalid @enderror" 
                   name="curp" id="curp" value="{{ old('curp') }}" maxlength="18"
                   pattern="^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$" 
                   title="Please enter a valid CURP format">
            @error('curp')
                <span class="invalid-feedback">{{ $message }}</span>
            @enderror
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-lg btn-primary" id="stpRegistrationSubmitBtn">
                <span class="px-1" id="stpRegistrationSubmitBtnText">{{ __('Register') }}</span>
                <span id="rightAngleSvgIcon">{!! svgIcons('right_angle') !!}</span>
            </button>
        </div>
    </form>
</div>
@endsection

@push('js')
{{-- Include jQuery if not already included in your layout --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

{{-- Include jQuery Validation Plugin --}}
<script src="https://cdn.jsdelivr.net/npm/jquery-validation@1.19.5/dist/jquery.validate.min.js"></script>

{{-- Include Select2 --}}
<script src="{{ asset('public/dist/plugins/select2-4.1.0-rc.0/js/select2.min.js') }}"></script>

<script type="text/javascript">
    'use strict';
    $(document).ready(function() {
        // Initialize Select2
        $('.select2').select2({
            width: '100%'
        });

        // Toggle person/company fields
        $('#accountType').change(function() {
            if ($(this).val() === 'person') {
                $('.person-field').show();
                $('#fechaNacimiento').attr('placeholder', 'YYYYMMDD');
                $('#curp').prop('required', true);
                $('#nombre, #apellidoPaterno').prop('required', true);
            } else {
                $('.person-field').hide();
                $('#fechaNacimiento').attr('placeholder', 'YYYYMMDD');
                $('#nombre').prop('required', true);
                $('#curp, #apellidoPaterno').prop('required', false);
            }
        });

        // Initial state
        $('#accountType').trigger('change');

        // Custom validation method for CURP
        $.validator.addMethod("curpFormat", function(value, element) {
            if (value.length === 0 && $('#accountType').val() !== 'person') {
                return true;
            }
            return /^[A-Z]{4}\d{6}[HM][A-Z]{5}[0-9A-Z]\d$/.test(value);
        }, "Please enter a valid CURP format");

        // Custom validation method for date format
        $.validator.addMethod("dateFormat", function(value, element) {
            return /^\d{8}$/.test(value);
        }, "Please enter date in YYYYMMDD format");

        // Form validation
        $('#stpRegistrationForm').validate({
            ignore: [],
            rules: {
                cuenta: {
                    required: true,
                    maxlength: 18
                },
                empresa: {
                    required: true,
                    maxlength: 15
                },
                nombre: {
                    required: true,
                    maxlength: 150
                },
                apellidoPaterno: {
                    required: function() {
                        return $('#accountType').val() === 'person';
                    },
                    maxlength: 60
                },
                apellidoMaterno: {
                    maxlength: 60
                },
                rfcCurp: {
                    required: true,
                    maxlength: 18
                },
                fechaNacimiento: {
                    required: true,
                    maxlength: 8,
                    dateFormat: true
                },
                pais: {
                    required: true
                },
                cuentaNoSTP: {
                    required: true,
                    maxlength: 18
                },
                curp: {
                    required: function() {
                        return $('#accountType').val() === 'person';
                    },
                    curpFormat: true,
                    maxlength: 18
                }
            },
            errorElement: 'span',
            errorPlacement: function(error, element) {
                error.addClass('invalid-feedback');
                if (element.parent('.input-group').length) {
                    error.insertAfter(element.parent());
                } else if (element.hasClass('select2')) {
                    error.insertAfter(element.next('.select2-container'));
                } else {
                    error.insertAfter(element);
                }
            },
            highlight: function(element, errorClass, validClass) {
                $(element).addClass('is-invalid');
            },
            unhighlight: function(element, errorClass, validClass) {
                $(element).removeClass('is-invalid');
            },
            submitHandler: function(form) {
                $("#stpRegistrationSubmitBtn").attr("disabled", true);
                $(".spinner").removeClass("d-none");
                $("#stpRegistrationSubmitBtnText").text("{{ __('Processing...') }}");
                form.submit();
            }
        });
    });
</script>
@endpush