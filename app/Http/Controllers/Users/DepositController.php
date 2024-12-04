<?php

namespace App\Http\Controllers\Users;

use App\Exceptions\Api\V2\AmountLimitException;
use Exception;
use App\Services\DepositMoneyService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Helpers\Common;
use App\Http\Requests\Api\V2\DepositMoney\ValidateDepositRequest;
use App\Models\{
    Transaction,
    Currency,
    Bank,
    User
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Repositories\StpApiRepository;
use App\Repositories\StpApiV3Repository;
use Illuminate\Support\Facades\Validator;

class DepositController extends Controller
{
    protected $helper;
    protected $service;
    protected $user;
    protected $stp;
    protected $stpV3;

    public function __construct(DepositMoneyService $service)
    {
        $this->helper  = new Common();
        $this->service  = $service;
        $this->user = new User();
        //$this->stp = new StpApiRepository();
        $this->stpV3 = new StpApiV3Repository();
    }

    public function create()
    {
        setActionSession();

        if (route('user.deposit.confirm') !== url()->previous()) {
            if (!empty(session('paymentData'))) {
                session()->forget('paymentData');
            }
        }

        $response  = $this->service->getSelfCurrencies();

        $curencies = [];

        foreach ($response['currencies'] as $c) {
            if ($c['type'] == 'fiat') {
                $curencies[] = $c;
            }
        }

        $defaultBankCode = config('services.stpmex.institucionContraparte');
        $bankList = config('services.stpmex.supportedBanks');
        $defaultBankName = $bankList[$defaultBankCode];

        $data = [
            'menu' => 'deposit',
            'icon' => 'university',
            'content_title' => 'Deposit',
            'defaultWallet' => $response['default'],
            'activeCurrencyList' =>  $curencies,
            'defaultBankName' => $defaultBankName,
            'bankList' => $bankList
        ];

        if (!$this->user->account_number && !$this->user->rfc_validated && !$this->user->cep_validated) {
            return redirect()->route('user.deposit.stp-form');
        } else {
            return view('user.deposit.create', $data);
        }
    }

    public function stpForm(Request $request)
    {
        $countries = config('services.stpmex.countries_list');

        $actividadEconomica = array_flip(config('services.stpmex.actividadEconomica'));

        $supportedBanks = config('services.stpmex.supportedBanks');

        $entidadFederativa = config('services.stpmex.supportedBanks');

        return view('user.stp.form', compact('countries', 'actividadEconomica', 'supportedBanks', 'entidadFederativa'));
    }

    public function testBalance()
    {
        $data = [
            "empresa" => "FITAL_MX",
            "cuentaOrdenante" => "646180566300000007",
        ];

        $privatekey_path = env('STP_PRIVATEKEY_PATH');
        $passphrase = env('STP_PASSPHRASE');

    
        $original = "||" . $data['empresa'] ."|". $data['cuentaOrdenante'] . "|||";

        $sign = $this->stp->oldSign($original, $privatekey_path, $passphrase);

        $data['firma'] = $sign;

        $response = $this->stp->getAccountBalance($data);

        dd($response);

    }

    public function stpRegister(Request $request)
    {
        // $validator = Validator::make($request->all(), [
        //     'accountType' => 'required|in:person,company',
        //     'cuenta' => 'required|string|max:18',
        //     'empresa' => 'required|string|max:15',
        //     //'nombre' => 'required_if:accountType,person|nullable|string|max:150',
        //     //'apellidoPaterno' => 'required_if:accountType,person|nullable|string|max:60',
        //     //'apellidoMaterno' => 'nullable|string|max:60',
        //     //'rfcCurp' => 'required|string|max:18',

        //     // 'fechaNacimiento' => [
        //     //     'required',
        //     //     'max:8',
        //     //     'regex:/^(19|20)\d{2}(0[1-9]|1[0-2])(0[1-9]|[12][0-9]|3[01])$/'
        //     // ],
        //     // 'pais' => 'required|string|size:3',

        //     'cuentaNoSTP' => 'required|string|max:18',
        //     //'curp' => 'required_if:accountType,person|nullable|string|max:18',
        // ]);

        // if ($validator->fails()) {
        //     return redirect()->back()
        //         ->withErrors($validator)
        //         ->withInput();
        // }

        $isIndividual = $request->accountType == 'person';
        $data = $request->all();

        // $privatekeyPath = env('STP_PRIVATEKEY_PATH');
        // $passphrase = env('STP_PASSPHRASE');

        // $original = "||" . $data['empresa'] . "|" . $data['cuenta'] . "|" .  $data['rfcCurp'] . "|" .  $data['cuentaNoSTP'] . "||";
        
        //$sign = $this->stp->oldSign($original, $privatekeyPath, $passphrase);

        // $data['paisNacimiento'] = $data['pais'];
        // $data['fechaConstitucion'] = $data['fechaNacimiento'];
        
        // $data['firma'] = $sign;

        if ($isIndividual) {
            $response = $this->stpV3->registerNaturalPerson($data);
        } else {
            $response = $response = $this->stpV3->registerLegalEntity($data);
        }

        //$response = $this->stpV3->registerCryptoAccount($data, $isIndividual);

        // $this->helper->one_time_message('success', __('Addon Installed successfully'));
        // return redirect()->route('user.deposit.stp-form')->withInput();

        if ($response['success'] == false) {
            $this->helper->one_time_message('error', __($response['error']['message']));
            return redirect()->route('user.deposit.stp-form')->withInput();
        } else {
            $this->helper->one_time_message('success', __('Register successfully'));
            return redirect()->route('user.deposit.stp-form')->withInput();
        }

    }

    public function depositConfirm(ValidateDepositRequest $request)
    {
        try {
            $transInfo  = $this->service->validateDepositable(
                $request->currency_id,
                $request->amount,
                $request->payment_method
            );

            $transInfo['mx_bank_name'] = $request->mx_bank_name;
            $transInfo['payment_description'] = $request->payment_description;

            session(['mx_bank_name' => $request->mx_bank_name]);
            session(['payment_description' => $request->payment_description]);

            setPaymentData($transInfo);
            return view('user.deposit.confirm', $transInfo);
        } catch (AmountLimitException $e) {
            $this->helper->one_time_message('error', __($e->getMessage()));
            return redirect('deposit');
        }
    }

    /**
     * Method depositGateway
     *
     * @param Request $request
     *
     * Set payment data
     *
     * Generate Payment url, redirect to payment page
     *
     */
    public function depositGateway(Request $request)
    {
        actionSessionCheck();

        $data = getPaymentData();

        // These are the mandatory field for dynamic gateway payment.
        $paymentData = [
            'currency_id' => $data['currencyId'],
            'total' => $data['totalAmount'],
            'transaction_type' => Deposit,
            'payment_type' => 'deposit',
            'method' => $data['payment_method'],
            'redirectUrl' => route('deposit.complete'),
            'cancel_url' => url('deposit'),
            'gateway' => $data['paymentMethodAlias'],
            'user_id' => Auth::id(),
            'uuid' => unique_code()
        ];

        if ($data['payment_method'] == Bank) {
            $paymentData['banks'] = getBankList($data['currencyId'], 'deposit');
        }

        $data = array_merge($data, $paymentData);

        setPaymentData($data);

        return redirect(gatewayPaymentUrl($data));
    }

    /**
     * Method depositComplete
     *
     * @param Request $request [parameter from gateway response]
     *
     * After complete the payment via gateway will return here
     *
     * Process the transaction
     *
     */
    public function depositComplete(Request $request)
    {
        try {

            $data = getPaymentParam(request()->params);

            isGatewayValidMethod($data['payment_method']);

            $details = [];

            if ($data['payment_method'] == Bank) {

                if (isset($request->bank, $request->attachment)) {
                    $details = [
                        'bank' => $request->bank,
                        'attachment' => $request->attachment
                    ];
                }
            }

            $depositResponse = $this->service->processPaymentConfirmation(
                $data['currency_id'],
                $data['payment_method'],
                $data['totalAmount'],
                $data['amount'],
                $data['user_id'],
                $data['uuid'],
                $details
            );

            $data['transaction_id'] = $depositResponse['transaction']->id;

            clearActionSession();

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return  $data['transaction_id'];
            }

            setPaymentData($data);

            return redirect()->route('user.deposit.success');
        } catch (Exception $e) {

            if (isset(request()->execute) && (request()->execute == 'api')) {
                return [
                    'status' => '401',
                    'message' => $e->getMessage()
                ];
            }

            $this->helper->one_time_message('error', __($e->getMessage()));
            return redirect('deposit');
        }
    }

    /**
     * Method depositSuccess
     *
     * Show deposit success page
     *
     * @return view
     */
    public function depositSuccess()
    {
        try {
            $data = getPaymentData();
            return view('user.deposit.success', $data);
        } catch (Exception $e) {
            $this->helper->one_time_message('error', $e->getMessage());
            return redirect('deposit');
        }
    }

    public function getPaymentMethods(Request $request)
    {
        $data = [];
        $currencyType = Currency::where('id', $request->currency_id)->value('type');
        $data['paymentMethods'] = $this->service->getPaymentMethods($request->currency_id, $currencyType, $request->transaction_type_id, 'web');
        $data['preference'] = ($currencyType == 'fiat') ? preference('decimal_format_amount', 2) : preference('decimal_format_amount_crypto', 8);
        return response()->json(['success' => $data]);
    }


    public function getDepositFeesLimit(Request $request)
    {
        try {
            $data = $this->service->validateDepositable($request->currency_id, $request->amount, $request->payment_method_id);
            $data['status'] = '200';
        } catch (Exception $e) {
            $data = [
                'message' => __($e->getMessage()),
                'status' => '401'
            ];
        }
        return response()->json(['success' => $data]);
    }


    public function getBankDetailOnChange(Request $request)
    {
        $bank = Bank::with('file:id,filename')->where(['id' => $request->bank])->first(['bank_name', 'account_name', 'account_number', 'file_id']);
        if ($bank) {
            $data['status'] = true;
            $data['bank']   = $bank;

            if (!empty($bank->file_id)) {
                $data['bank_logo'] = $bank->file?->filename;
            }
        } else {
            $data['status'] = false;
            $data['bank']   = __('Bank Not FOund');
        }
        return $data;
    }

    public function depositPrintPdf($trans_id)
    {
        return redirect()->back();

        $data['transactionDetails'] = Transaction::with(['user', 'payment_method:id,name', 'currency:id,symbol,code'])
            ->where(['id' => $trans_id])
            ->first(['user_id', 'currency_id', 'payment_method_id', 'uuid', 'transaction_type_id', 'charge_percentage', 'charge_fixed', 'subtotal', 'total', 'status', 'created_at']);

        generatePDF('user.deposit.deposit-pdf', 'deposit_', $data);
    }


    public function coinPaymentSummary()
    {
        if (Session::has('transactionDetails') && Session::has('transactionInfo')) {
            $transactionDetails = Session::get('transactionDetails');
            $transactionInfo = Session::get('transactionInfo');
            $gateway = 'Coinpayments';

            return view('gateways.coinpayment_summery', compact('transactionDetails', 'transactionInfo', 'gateway'));
        }

        // Redirect to the deposit page if any of the sessions is not available
        $this->helper->one_time_message('error', __('Coinpayment Session has been ended'));
        return redirect()->route('home');
    }

    public function getStpUserInfo(Request $request)
    {
        $email = $request->stp_email;
        $pass = $request->stp_pass;
        $amount = $request->amount * 1.04;

        //$stpToken = $this->stp->generateAccessToken($email, $pass);

        //session(['stpToken' => $stpToken]);

        //$response = $this->stp->getUserInformation($stpToken);

        $success = false;

        if (isset($response['data']['parent']['user']['totalAmount'])) {
            $balance = $response['data']['parent']['user']['totalAmount'];

            if (((int)($balance * 100) >= (int)($amount * 100))) {
                $message = 'Success';
                $success = true;
            } else {
                $formatted_balance = number_format($balance, 2);

                // Create the message with the formatted balance
                $message = 'Saldo insuficiente ' . $formatted_balance;
            }
        } else {
            $message = 'Unauthorized';
        }

        $return = [
            'success' => $success,
            'message' => $message,
        ];

        return response()->json($return);
    }
}
