<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientOrder;
use App\Models\ClientPriceList;
use App\Models\ProductVariant;
use App\Models\User;
use App\Mail\ClientCredentialsMail;
use App\Mail\ClientOrderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use App\Rules\SecureUpload;

class ClientZoneController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('ClientZone/Index', [
            'clients' => User::query()->where('role', 'client')->latest()->get(['id', 'name', 'email', 'company', 'phone', 'tax_id', 'is_active', 'approval_status', 'account_source', 'deactivation_source', 'created_at']),
            'variants' => ProductVariant::query()->with('product.category')->where('is_active', true)->orderBy('code')->paginate(25, ['id', 'product_id', 'code', 'tube', 'thread', 'quality', 'list_price', 'discount_percent', 'final_price'], 'variants_page')->withQueryString(),
            'orders' => ClientOrder::query()->with(['user:id,name,email,company', 'items'])->latest()->take(60)->get(),
            'priceLists' => ClientPriceList::query()->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function updateClient(Request $request, User $user)
    {
        abort_unless($user->role === 'client', 404);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user->id)],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'password' => ['nullable', 'string', 'min:8', 'max:100'],
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'send_credentials' => ['nullable', 'boolean'],
        ]);
        $password = $data['password'] ?? null;
        unset($data['send_credentials']);
        if (blank($password)) unset($data['password']);
        $data['is_active'] = $data['approval_status'] === 'approved';
        $data['deactivation_source'] = $data['is_active'] ? null : 'admin';
        $securityAttributes = collect($data)->only(['approval_status', 'is_active', 'deactivation_source'])->all();
        $user->fill(collect($data)->except(array_keys($securityAttributes))->all())->forceFill($securityAttributes)->save();

        if ($request->boolean('send_credentials') || ($data['approval_status'] === 'approved' && $user->wasChanged('approval_status'))) {
            try { Mail::to($user->email)->send(new ClientCredentialsMail($user->fresh(), $password)); } catch (\Throwable $exception) { report($exception); }
        }

        return back()->with('success', $data['approval_status'] === 'approved' ? 'Cliente aprobado y credenciales actualizadas.' : 'Datos y estado del cliente actualizados.');
    }

    public function updateOrder(Request $request, ClientOrder $clientOrder)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['received', 'processing', 'quoted', 'completed', 'cancelled'])],
            'delivery_date' => ['nullable', 'date'],
        ]);
        $clientOrder->update($data);
        if ($clientOrder->wasChanged(['status', 'delivery_date'])) {
            try { Mail::to($clientOrder->user->email)->send(new ClientOrderMail($clientOrder->fresh(['user', 'items']), 'client')); } catch (\Throwable $exception) { report($exception); }
        }
        return back()->with('success', 'Pedido actualizado. El estado y la fecha ya se reflejan en el comprobante del cliente.');
    }

    public function storePriceList(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'file' => ['required', 'file', new SecureUpload(), 'mimes:pdf,xls,xlsx,csv', 'max:51200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $path = $request->file('file')->store('client-price-lists', 'local');
        ClientPriceList::create([
            'name' => $data['name'],
            'file_path' => $path,
            'file_size' => $request->file('file')->getSize(),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active', true),
        ]);
        return back()->with('success', 'Lista de precios publicada para clientes aprobados.');
    }

    public function updatePriceList(Request $request, ClientPriceList $clientPriceList)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:160'],
            'file' => ['nullable', 'file', new SecureUpload(), 'mimes:pdf,xls,xlsx,csv', 'max:51200'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        if ($request->hasFile('file')) {
            $this->deletePriceListFile($clientPriceList->file_path);
            $data['file_path'] = $request->file('file')->store('client-price-lists', 'local');
            $data['file_size'] = $request->file('file')->getSize();
        }
        $data['is_active'] = $request->boolean('is_active');
        $clientPriceList->update($data);
        return back()->with('success', 'Lista de precios actualizada.');
    }

    public function destroyPriceList(ClientPriceList $clientPriceList)
    {
        $this->deletePriceListFile($clientPriceList->file_path);
        $clientPriceList->delete();
        return back()->with('success', 'Lista de precios eliminada.');
    }

    private function deletePriceListFile(?string $path): void
    {
        if (! $path) return;
        Storage::disk('local')->delete($path);
        Storage::disk('public')->delete($path); // Compatibilidad con archivos heredados.
    }
}
