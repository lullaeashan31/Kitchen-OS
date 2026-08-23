<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Services\EmployeeLockerService;
use App\Services\QrCodeRenderer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;

/**
 * Admin-side: generate/show the standing "your signed documents" link and
 * QR for an employee, plus a prewritten WhatsApp message the owner copies
 * and sends manually (§3.4 — no WhatsApp Business API in this phase).
 */
class EmployeeLockerController extends Controller
{
    public function show(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        $token = EmployeeLockerService::getOrCreate($employee, $request->user()->id);
        $url = URL::to('/d/'.$token->token);

        return view('employees.locker', [
            'employee' => $employee,
            'url' => $url,
            'qrSvg' => QrCodeRenderer::svg($url),
            'waLink' => $this->whatsAppLink($employee, $url),
        ]);
    }

    public function regenerate(Request $request, Employee $employee)
    {
        abort_unless($request->user()->can('document.manage'), 403);

        EmployeeLockerService::regenerate($employee, $request->user()->id);

        return redirect()->route('employees.locker.show', $employee)
            ->with('status', 'Link regenerated — the old link no longer works.');
    }

    private function whatsAppLink(Employee $employee, string $url): string
    {
        $message = "Hi {$employee->name}, here are your signed onboarding documents from Traverse Inc. You can view them anytime at this link: {$url}";
        $phone = preg_replace('/\D+/', '', $employee->phone);
        // Assume Indian numbers when no country code is present (10 digits).
        if (strlen($phone) === 10) {
            $phone = '91'.$phone;
        }

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($message);
    }
}
