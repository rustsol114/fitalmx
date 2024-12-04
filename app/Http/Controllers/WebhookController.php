<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class WebhookController extends Controller
{

    // Payment status constants
    const STATUS_SUCCESS = 'LQ';
    const STATUS_CANCELLED = 'CN';
    const STATUS_REFUNDED = 'D';

    const SUCCES_MESSAGE = ['mensaje' => 'recibido'];


    public function handleStpStatus(Request $request)
    {
        // Log the incoming webhook
        Log::info('STP Webhook received:', $request->all());

        try {
            // Validate the incoming request
            $validatedData = $request->validate([
                'cuenta' => 'required|string',
                'empresa' => 'required|string',
                'estado' => 'required|string',
                'observaciones' => 'required|string',
                'validaCep' => 'required|string|in:0,1',
            ]);

            // Find the user by account number
            $user = User::where('account_number', $validatedData['cuenta'])
                ->where('empresa', $validatedData['empresa'])
                ->first();

            if (!$user) {
                Log::warning('User not found for STP notification:', $validatedData);
                return response()->json(self::SUCCES_MESSAGE, 200);
            }

            // Update user based on the notification
            $user->cep_validated = $validatedData['validaCep'] === '1';

            // If estado is 'A', we can assume the account is validated by admin
            if ($validatedData['estado'] === 'A') {
                $user->validated_by_admin = 1;
            }

            $user->save();

            // Log the successful update
            Log::info('User validation updated:', [
                'user_id' => $user->id,
                'cuenta' => $validatedData['cuenta'],
                'cep_validated' => $user->cep_validated,
                'validated_by_admin' => $user->validated_by_admin
            ]);

            // Return the exact response format required by STP
            return response()->json(['mensaje' => 'recibido'], 200);
        } catch (\Exception $e) {
            Log::error('STP Webhook processing error:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            // Still return success to STP even if we have internal errors
            return response()->json(['mensaje' => 'recibido'], 200);
        }
    }

   /**
     * Handle incoming STP payment notification
     */
    public function handleNotification(Request $request)
    {
        try {
            $validated = $this->validatePayload($request);
            
            // $notification = PaymentNotification::create([
            //     'stp_id' => $validated['id'],
            //     'company' => $validated['empresa'],
            //     'tracking_key' => $validated['claveRastreo'],
            //     'status' => $validated['estado'],
            //     'return_reason' => $validated['causaDevolucion'],
            //     'liquidation_timestamp' => $this->convertTimestamp($validated['tsLiquidacion']),
            // ]);

            return response()->json(self::SUCCES_MESSAGE, 200);

        } catch (\Exception $e) {
            Log::error('STP Payment Notification Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to process payment notification'
            ], 500);
        }
    }

    public function handlePayment(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'id' => 'required|numeric|max:9999999999',
                'fechaOperacion' => 'required|numeric|digits:8',
                'institucionOrdenante' => 'required|numeric|max:99999',
                'institucionBeneficiaria' => 'required|numeric|max:99999',
                'claveRastreo' => 'required|string|max:30',
                'monto' => 'required|numeric',
                'nombreOrdenante' => 'nullable|string|max:120',
                'tipoCuentaOrdenante' => 'nullable|numeric|max:99',
                'cuentaOrdenante' => 'nullable|string|max:20',
                'rfcCurpOrdenante' => 'nullable|string|max:18',
                'nombreBeneficiario' => 'required|string|max:40',
                'tipoCuentaBeneficiario' => 'required|numeric|max:99',
                'cuentaBeneficiario' => 'required|string|max:20',
                'rfcCurpBeneficiario' => 'required|string|max:18',
                'conceptoPago' => 'required|string|max:40',
                'referenciaNumerica' => 'required|numeric|max:9999999',
                'empresa' => 'required|string|max:15',
            ]);

            if ($validator->fails()) {
                Log::error('STP Payment validation failed', ['errors' => $validator->errors()]);
                return response()->json([
                    'error' => 'Invalid payment data',
                    'details' => $validator->errors()
                ], 400);
            }

            if ($request->input('institucionOrdenante') == '90903' && 
                $request->input('nombreOrdenante') == 'CODI VALIDA') {
                return $this->validateCodiAccount($request);
            }

            $payment = new Payment();
            $payment->stp_id = $request->input('id');
            $payment->operation_date = date('Y-m-d', strtotime($request->input('fechaOperacion')));
            $payment->ordering_institution = $request->input('institucionOrdenante');
            $payment->beneficiary_institution = $request->input('institucionBeneficiaria');
            $payment->tracking_key = $request->input('claveRastreo');
            $payment->amount = $request->input('monto');
            $payment->ordering_name = $request->input('nombreOrdenante');
            $payment->beneficiary_name = $request->input('nombreBeneficiario');
            $payment->beneficiary_account = $request->input('cuentaBeneficiario');
            $payment->concept = $request->input('conceptoPago');
            $payment->reference = $request->input('referenciaNumerica');
            $payment->company = $request->input('empresa');
            $payment->save();

          
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'payment_id' => $payment->id
            ], 200);

        } catch (\Exception $e) {
            Log::error('STP Payment processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Payment processing failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function validateCodiAccount(Request $request)
    {
        $isValid = true;

        return response()->json([
            'success' => $isValid,
            'account' => $request->input('cuentaBeneficiario'),
            'message' => $isValid ? 'Account validated successfully' : 'Invalid account'
        ], $isValid ? 200 : 400);
    }

    /**
     * Validate incoming payload
     */
    private function validatePayload(Request $request)
    {
        return $request->validate([
            'id' => 'required|numeric|digits_between:1,12',
            'empresa' => 'required|string|max:15',
            'claveRastreo' => 'required|string|max:30',
            'estado' => 'required|string|max:20|in:LQ,CN,D',
            'causaDevolucion' => 'required|string|max:100',
            'tsLiquidacion' => 'required|string|max:14'
        ]);
    }

    /**
     * Convert millisecond timestamp to DateTime
     */
    private function convertTimestamp(string $timestamp): string
    {
        return date('Y-m-d H:i:s', (int)($timestamp / 1000));
    }
}
