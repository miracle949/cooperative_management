<?php

namespace App\Http\Controllers;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $paymentMethods = PaymentMethod::orderBy('id')->get();

        return response()->json(['success' => true, 'data' => $paymentMethods]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'method_name'    => 'required|string|max:255',
            'has_qr_code'    => 'required|boolean',
            'qr_code_image'  => 'required_if:has_qr_code,1|nullable|image|mimes:png,jpg,jpeg|max:2048',
            'account_name'   => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'reference_length' => 'nullable|integer|min:4|max:50',
            'instructions'   => 'nullable|string|max:1000',
        ]);

        $data = [
            'method_name'    => $request->method_name,
            'has_qr_code'    => $request->boolean('has_qr_code'),
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'reference_length' => $request->filled('reference_length') ? (int) $request->reference_length : null,
            'instructions'   => $request->instructions,
            'is_active'      => true,
        ];

        if ($request->boolean('has_qr_code') && $request->hasFile('qr_code_image')) {
            $data['qr_code_image_path'] = $request->file('qr_code_image')->store('payment_qr_codes', 'public');
        }

        $method = PaymentMethod::create($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment method created successfully.',
            'data'    => $method,
        ]);
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $request->validate([
            'method_name'    => 'required|string|max:255',
            'has_qr_code'    => 'required|boolean',
            // Only require a new image if enabling QR and none is stored yet
            'qr_code_image'  => [
                \Illuminate\Validation\Rule::requiredIf(
                    $request->boolean('has_qr_code') && ! $method->qr_code_image_path
                ),
                'nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048',
            ],
            'account_name'   => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'reference_length' => 'nullable|integer|min:4|max:50',
            'instructions'   => 'nullable|string|max:1000',
        ]);

        $data = [
            'method_name'    => $request->method_name,
            'has_qr_code'    => $request->boolean('has_qr_code'),
            'account_name'   => $request->account_name,
            'account_number' => $request->account_number,
            'reference_length' => $request->filled('reference_length') ? (int) $request->reference_length : null,
            'instructions'   => $request->instructions,
        ];

        if ($request->boolean('has_qr_code') && $request->hasFile('qr_code_image')) {
            if ($method->qr_code_image_path) {
                Storage::disk('public')->delete($method->qr_code_image_path);
            }
            $data['qr_code_image_path'] = $request->file('qr_code_image')->store('payment_qr_codes', 'public');
        } elseif (! $request->boolean('has_qr_code')) {
            if ($method->qr_code_image_path) {
                Storage::disk('public')->delete($method->qr_code_image_path);
            }
            $data['qr_code_image_path'] = null;
        }

        $method->update($data);

        return response()->json([
            'success' => true,
            'message' => 'Payment method updated successfully.',
            'data'    => $method,
        ]);
    }

    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->qr_code_image_path) {
            Storage::disk('public')->delete($method->qr_code_image_path);
        }

        $method->delete();

        return response()->json([
            'success' => true,
            'message' => 'Payment method deleted successfully.',
        ]);
    }

    public function toggleActive($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->is_active = ! $method->is_active;
        $method->save();

        return response()->json([
            'success' => true,
            'message' => 'Payment method status updated.',
            'is_active' => $method->is_active,
        ]);
    }

    public function getQrCode($id)
    {
        $method = PaymentMethod::findOrFail($id);

        if ($method->has_qr_code && $method->qr_code_image_path) {
            return response()->json([
                'success' => true,
                'has_qr' => true,
                'qr_url' => Storage::disk('public')->url($method->qr_code_image_path),
            ]);
        }

        return response()->json([
            'success' => true,
            'has_qr' => false,
        ]);
    }
}
