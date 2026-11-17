<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClientOrder;
use App\Models\ClientPriceList;
use App\Models\IntegrationClient;
use App\Models\ProductVariant;
use App\Models\User;
use App\Mail\ClientCredentialsMail;
use App\Mail\ClientOrderMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use App\Rules\SecureUpload;
use App\Services\IntegrationOutbox;

class ClientZoneController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('ClientZone/Index', [
            'clients' => User::query()->where('role', 'client')->latest()->get(['id', 'name', 'email', 'company', 'phone', 'tax_id', 'discount_percent', 'can_view_price_lists', 'default_transport_name', 'default_transport_address', 'default_transport_phone', 'default_delivery_destination', 'is_active', 'approval_status', 'account_source', 'deactivation_source', 'created_at']),
            'integrationClients' => IntegrationClient::query()->whereNull('user_id')->orderBy('business_name')->get(['id', 'external_code', 'business_name', 'email_candidates', 'tax_id', 'transport_name', 'transport_address', 'transport_phone', 'delivery_destination', 'last_received_at']),
            'variants' => ProductVariant::query()->with('product.category')->where('is_active', true)->orderBy('code')->paginate(25, ['id', 'product_id', 'code', 'tube', 'thread', 'quality', 'list_price'], 'variants_page')->withQueryString(),
            'orders' => ClientOrder::query()->with(['user:id,name,email,company', 'items'])->latest()->take(60)->get(),
            'priceLists' => ClientPriceList::query()->orderBy('sort_order')->orderByDesc('id')->get(),
        ]);
    }

    public function storeClient(Request $request, IntegrationOutbox $outbox)
    {
        $data = $this->validatedClient($request, true);
        $integrationClient = filled($data['integration_client_id'] ?? null)
            ? IntegrationClient::query()->whereNull('user_id')->findOrFail($data['integration_client_id'])
            : null;

        unset($data['integration_client_id'], $data['send_credentials']);
        $password = $data['password'];
        $data['role'] = 'client';
        $data['is_active'] = $data['approval_status'] === 'approved';
        $data['deactivation_source'] = $data['is_active'] ? null : 'admin';
        $data['account_source'] = $integrationClient ? 'api' : 'admin';
        $security = collect($data)->only(['is_active', 'approval_status', 'deactivation_source', 'account_source'])->all();

        if ($integrationClient) {
            $data = array_replace([
                'company' => $integrationClient->business_name,
                'tax_id' => $integrationClient->tax_id,
                'default_transport_name' => $integrationClient->transport_name,
                'default_transport_address' => $integrationClient->transport_address,
                'default_transport_phone' => $integrationClient->transport_phone,
                'default_delivery_destination' => $integrationClient->delivery_destination,
            ], array_filter($data, fn ($value) => $value !== null && $value !== ''));
        }

        $user = User::create($data);
        $user->forceFill([
            'role' => 'client',
            'is_active' => $security['is_active'],
            'approval_status' => $security['approval_status'],
            'deactivation_source' => $security['deactivation_source'],
            'account_source' => $security['account_source'],
        ])->save();
        if ($integrationClient) $integrationClient->update(['user_id' => $user->id]);
        $outbox->client($user->fresh(), 'client.created');

        if ($request->boolean('send_credentials')) {
            try { Mail::to($user->email)->send(new ClientCredentialsMail($user, $password)); } catch (\Throwable $exception) { report($exception); }
        }

        return back()->with('success', 'Cliente creado'.($integrationClient ? ' y vinculado con los datos de la API.' : '.'));
    }

    public function updateClient(Request $request, User $user, IntegrationOutbox $outbox)
    {
        abort_unless($user->role === 'client', 404);
        $data = $this->validatedClient($request, false, $user);
        $password = $data['password'] ?? null;
        unset($data['send_credentials']);
        if (blank($password)) unset($data['password']);
        $data['is_active'] = $data['approval_status'] === 'approved';
        $data['deactivation_source'] = $data['is_active'] ? null : 'admin';
        $securityAttributes = collect($data)->only(['approval_status', 'is_active', 'deactivation_source'])->all();
        $user->fill(collect($data)->except(array_keys($securityAttributes))->all())->forceFill($securityAttributes)->save();
        $outbox->client($user, 'client.updated');

        if ($request->boolean('send_credentials') || ($data['approval_status'] === 'approved' && $user->wasChanged('approval_status'))) {
            try { Mail::to($user->email)->send(new ClientCredentialsMail($user->fresh(), $password)); } catch (\Throwable $exception) { report($exception); }
        }

        return back()->with('success', $data['approval_status'] === 'approved' ? 'Cliente aprobado y credenciales actualizadas.' : 'Datos y estado del cliente actualizados.');
    }

    private function validatedClient(Request $request, bool $creating, ?User $user = null): array
    {
        return $request->validate([
            'integration_client_id' => [$creating ? 'nullable' : 'prohibited', 'integer', Rule::exists('integration_clients', 'id')],
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190', Rule::unique('users', 'email')->ignore($user?->id)],
            'company' => ['nullable', 'string', 'max:150'],
            'phone' => ['nullable', 'string', 'max:50'],
            'tax_id' => ['nullable', 'string', 'max:40'],
            'discount_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'can_view_price_lists' => ['nullable', 'boolean'],
            'default_transport_name' => ['nullable', 'string', 'max:120'],
            'default_transport_address' => ['nullable', 'string', 'max:255'],
            'default_transport_phone' => ['nullable', 'string', 'max:60'],
            'default_delivery_destination' => ['nullable', 'string', 'max:255'],
            'password' => [$creating ? 'required' : 'nullable', 'string', Password::min(8), 'max:100'],
            'approval_status' => ['required', Rule::in(['pending', 'approved', 'rejected'])],
            'send_credentials' => ['nullable', 'boolean'],
        ]);
    }

    public function updateOrder(Request $request, ClientOrder $clientOrder, IntegrationOutbox $outbox)
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(['received', 'processing', 'quoted', 'completed', 'cancelled'])],
            'delivery_date' => ['nullable', 'date'],
        ]);
        $clientOrder->update($data);
        if ($clientOrder->wasChanged(['status', 'delivery_date'])) {
            $outbox->order($clientOrder, 'order.updated');
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
