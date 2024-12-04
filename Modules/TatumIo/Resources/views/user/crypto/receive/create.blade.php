@extends('user.layouts.app')

@section('content')
<div class="text-center">
    <p class="mb-0 gilroy-Semibold f-26 text-dark theme-tran r-f-20 text-uppercase">
        {{ __('Receive :x', ['x' => strtoupper($walletCurrencyCode)]) }}
    </p>
</div>
<div class="modal-dialog merchant-space" id="crypto-receive-create">
    <div class="modal-content">
        <div class="modal-body modal-body-pxy">
            @include('user.common.alert')
            <form method="POST" action="" id="transfer_form">
                <input type="hidden" value="{{ csrf_token() }}" name="_token" id="token">
                <div class="mt-28 param-ref">
                    <label class="gilroy-medium text-gray-100 mb-2 f-15" for="priority">{{ __('Receiving Address') }}</label>
                    <div class="avoid-blink">
                        <select class="select2 priority" data-minimum-results-for-search="Infinity" id="senderAddress" name="senderAddress">
                            @foreach ($addresses as $key => $d)
                            @php
                            $abbreviatedAddress = substr($d['address'], 0, 33) . '...';
                            @endphp
                            <option value="{{ $d['address'] }}">{{ $abbreviatedAddress }} - {{ __('Balance') }}: {{ $d['balance'] }} </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="express-merchant-qr-section ">
                    <div class="row">
                        <div class="col-md-8 offset-md-2">
                            <p class="mb-0 f-14 leading-22 text-dark gilroy-medium text-center mt-5p">{{ __('Receiving Address Qr Code') }}</p>
                        </div>
                    </div>
                    <div class="d-flex justify-content-center mt-20">
                        <div class="border" id="wallet-address"></div>
                    </div>
                    <div class="d-flex justify-content-center mt-56">
                        <p class="mb-0 f-14 leading-22 text-dark gilroy-medium text-center mt-5p">{!! __('<b>Only receive :x to this address</b>, receiving any other coin will result in permanent loss.', ['x' => strtoupper($walletCurrencyCode)]) !!}</p>
                    </div>
                    <div class="form-group mt-20">
                        <label class="gilroy-medium text-gray-100 mb-2 f-15">{{ __('Receiving Address') }}</label>
                        <div class="input-group mb-3 mt-2">
                            <input type="text" class="form-control text-dark bg-copy-dark" id="wallet-address-input" value="{{ decrypt($address) }}" readonly>
                            <div class="input-group-append wallet-address-copy-btn">
                                <span class="input-group-text btn-primary copy-button">{{ __('Copy') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('js')

<script src="{{ asset('public/dist/plugins/jquery-qrcode/jquery.qrcode.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/plugins/jquery-qrcode/qrcode.min.js') }}" type="text/javascript"></script>
<script src="{{ asset('public/dist/libraries/sweetalert/sweetalert-unpkg.min.js')}}" type="text/javascript"></script>

<script>
    var copied = "{{ __('Copied!') }}";
    var addressCopyText = "{{ __('Address Copied!') }}";
    var addressText = "{{ decrypt($address) }}";

    $(document).ready(function() {
        // Copy selected value on change
        $('#senderAddress').on('change', function() {
            var selectedValue = $(this).val();

            $('#wallet-address').empty();
            
            jQuery('#wallet-address').qrcode({
                text: selectedValue
            });

            $('#wallet-address-input').val(selectedValue);
        });
    });
</script>
<script src="{{ asset('Modules/TatumIo/Resources/assets/user/js/crypto_send_receive.min.js') }}" type="text/javascript"></script>

@endpush