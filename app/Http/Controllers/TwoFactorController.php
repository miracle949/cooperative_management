<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Users_tbl;
use App\Models\two_factor_secret_tbl;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use OTPHP\TOTP;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorController extends Controller
{
    /**
     * Clock-skew window given to the TOTP library, in seconds. Must stay
     * strictly below the 30s period (OTPHP requirement); half a period is
     * a small, conservative allowance.
     */
    private const TOTP_LEEWAY = 15;

    private const RECOVERY_CODE_COUNT = 10;

    private function issuer(): string
    {
        $name = config('app.name');

        return is_string($name) && $name !== '' ? $name : 'KPMPCATS';
    }

    private function eligibleUserOrAbort(): Users_tbl
    {
        $user = auth()->user();

        abort_unless(
            $user instanceof Users_tbl
                && method_exists($user, 'requiresTwoFactor')
                && $user->requiresTwoFactor(),
            403
        );

        return $user;
    }

    /**
     * Loads the 2FA configuration with a fresh query. Using the relation
     * builder (and never the memoized attribute) avoids stale results on the
     * long-lived authenticated-user instance when the record is created or
     * removed during the same session.
     */
    private function secretRecordFor(Users_tbl $user): ?two_factor_secret_tbl
    {
        return $user->twoFactorSecret()->first();
    }

    private function totpFromSecret(string $secret): TOTP
    {
        return TOTP::createFromSecret($secret);
    }

    /**
     * Constant-time check for a well-formed (6-digit) TOTP code against the
     * stored secret, within the clock-skew window.
     */
    private function isValidTotp(string $secret, string $code): bool
    {
        $normalized = preg_replace('/\s+/', '', $code);

        if (! is_string($normalized) || ! preg_match('/^[0-9]{6}$/', $normalized)) {
            return false;
        }

        try {
            return $this->totpFromSecret($secret)->verify($normalized, null, self::TOTP_LEEWAY);
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * If the submitted value matches one of the stored recovery-code hashes,
     * that code is consumed (removed) so it can never be used again.
     */
    private function consumeRecoveryCode(two_factor_secret_tbl $record, string $code): bool
    {
        $hashes = $record->recovery_codes;

        if (! is_array($hashes) || $hashes === []) {
            return false;
        }

        foreach ($hashes as $index => $hash) {
            if (is_string($hash) && Hash::check($code, $hash)) {
                unset($hashes[$index]);
                $record->recovery_codes = array_values($hashes);
                $record->save();

                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function generateRecoveryCodes(int $count = self::RECOVERY_CODE_COUNT): array
    {
        $codes = [];

        while (count($codes) < $count) {
            $code = strtoupper(bin2hex(random_bytes(10)));
            $codes[] = implode('-', str_split($code, 4));
        }

        return $codes;
    }

    private function otpauthUri(string $secret, Users_tbl $user): string
    {
        return $this->totpFromSecret($secret)
            ->withLabel((string) $user->email)
            ->withIssuer($this->issuer())
            ->getProvisioningUri();
    }

    private function qrSvgFor(string $uri): string
    {
        $renderer = new ImageRenderer(new RendererStyle(220), new SvgImageBackEnd());

        return (new Writer($renderer))->writeString($uri);
    }

    // ────────────────────────────────────────────────────────────────────────
    // Management (authenticated, eligible user only)
    // ────────────────────────────────────────────────────────────────────────

    public function manage()
    {
        $user = $this->eligibleUserOrAbort();
        $record = $this->secretRecordFor($user);

        $secret = null;
        $qrSvg = null;

        // Only during the (unconfirmed) enrollment session is the secret and
        // its QR rendered. Once confirmed, the secret is never rendered again.
        if ($record !== null && ! $record->enabled && ! empty($record->secret)) {
            $secret = $record->secret;
            $qrSvg = $this->qrSvgFor($this->otpauthUri($secret, $user));
        }

        // Plaintext recovery codes exist in the session only for the single
        // redirect that displays them immediately after generation.
        $recoveryCodes = session()->pull('2fa.new-recovery-codes', []);

        return view('auth.two-factor', compact('user', 'record', 'secret', 'qrSvg', 'recoveryCodes'));
    }

    public function enroll(Request $request)
    {
        $user = $this->eligibleUserOrAbort();

        if ($user->twoFactorEnabled()) {
            return redirect()->route('2fa.manage')
                ->with('error', 'Two-factor authentication is already enabled on this account.');
        }

        $totp = TOTP::generate();

        two_factor_secret_tbl::updateOrCreate(
            ['user_id' => $user->id],
            [
                'secret' => $totp->getSecret(),
                'recovery_codes' => null,
                'confirmed_at' => null,
                'enabled' => false,
            ]
        );

        AuditLog::log(
            '2FA Enrollment Initiated',
            "Two-factor authentication enrollment started for user #{$user->id}.",
            'user',
            $user->id
        );

        return redirect()->route('2fa.manage')
            ->with('status', 'Scan the QR code and enter a code from your authenticator app to finish setup.');
    }

    public function confirm(Request $request)
    {
        $user = $this->eligibleUserOrAbort();
        $record = $this->secretRecordFor($user);

        if ($record === null || $record->enabled) {
            return redirect()->route('2fa.manage')
                ->with('error', 'There is no pending two-factor enrollment to confirm.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:16',
        ]);

        if (! $this->isValidTotp($record->secret, $data['code'])) {
            AuditLog::log(
                '2FA Enrollment Failed',
                "Two-factor enrollment confirmation rejected an invalid code for user #{$user->id}.",
                'user',
                $user->id
            );

            return redirect()->back()
                ->withErrors(['code' => 'The code you entered is invalid or has expired. Please try again.']);
        }

        $codes = $this->generateRecoveryCodes();

        $record->recovery_codes = array_map(static fn (string $code): string => Hash::make($code), $codes);
        $record->confirmed_at = now();
        $record->enabled = true;
        $record->save();

        // The account is now live-2FA. Mark this session verified so the
        // administrator is not signed out mid-task by the challenge gate.
        $request->session()->put('2fa.verified', true);
        $request->session()->flash('2fa.new-recovery-codes', $codes);

        AuditLog::log(
            '2FA Enabled',
            "Two-factor authentication enabled for user #{$user->id}.",
            'user',
            $user->id
        );

        return redirect()->route('2fa.manage')
            ->with('status', 'Two-factor authentication is now enabled. Store your recovery codes somewhere safe.');
    }

    public function disable(Request $request)
    {
        $user = $this->eligibleUserOrAbort();
        $record = $this->secretRecordFor($user);

        if ($record === null || ! $record->enabled) {
            return redirect()->route('2fa.manage')
                ->with('error', 'Two-factor authentication is not enabled on this account.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:64',
        ]);

        $code = preg_replace('/\s+/', '', $data['code']);

        $verifiedTotp = $this->isValidTotp($record->secret, (string) $code);
        $verifiedRecovery = $verifiedTotp ? false : $this->consumeRecoveryCode($record, (string) $code);

        if (! $verifiedTotp && ! $verifiedRecovery) {
            AuditLog::log(
                '2FA Disable Failed',
                "Two-factor disable request rejected an invalid code for user #{$user->id}.",
                'user',
                $user->id
            );

            return redirect()->back()
                ->withErrors(['code' => 'The code you entered is invalid. Two-factor authentication was not disabled.']);
        }

        // Fully invalidate the configuration: encrypted secret, recovery
        // codes, confirmation state. Re-enrollment starts from scratch.
        $record->delete();

        $request->session()->forget('2fa.verified');

        AuditLog::log(
            '2FA Disabled',
            "Two-factor authentication disabled for user #{$user->id}.",
            'user',
            $user->id
        );

        return redirect()->route('2fa.manage')
            ->with('status', 'Two-factor authentication has been disabled.');
    }

    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $this->eligibleUserOrAbort();
        $record = $this->secretRecordFor($user);

        if ($record === null || ! $record->enabled) {
            return redirect()->route('2fa.manage')
                ->with('error', 'Enable two-factor authentication before regenerating recovery codes.');
        }

        $data = $request->validate([
            'code' => 'required|string|max:64',
        ]);

        $code = preg_replace('/\s+/', '', $data['code']);

        $verifiedTotp = $this->isValidTotp($record->secret, (string) $code);
        $verifiedRecovery = $verifiedTotp ? false : $this->consumeRecoveryCode($record, (string) $code);

        if (! $verifiedTotp && ! $verifiedRecovery) {
            AuditLog::log(
                '2FA Recovery Regeneration Failed',
                "Recovery-code regeneration rejected an invalid code for user #{$user->id}.",
                'user',
                $user->id
            );

            return redirect()->back()
                ->withErrors(['code' => 'The code you entered is invalid. The existing recovery codes were not changed.']);
        }

        $codes = $this->generateRecoveryCodes();

        $record->recovery_codes = array_map(static fn (string $code): string => Hash::make($code), $codes);
        $record->save();

        $request->session()->flash('2fa.new-recovery-codes', $codes);

        AuditLog::log(
            '2FA Recovery Codes Regenerated',
            "Recovery codes regenerated for user #{$user->id}.",
            'user',
            $user->id
        );

        return redirect()->route('2fa.manage')
            ->with('status', 'New recovery codes generated. The previous recovery codes no longer work.');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Sign-in challenge (pre-verification; throttled on the POST route)
    // ────────────────────────────────────────────────────────────────────────

    public function challenge()
    {
        if (session()->get('2fa.verified')) {
            return redirect()->route('UserHandle');
        }

        $pendingId = session()->get('2fa.pending_user_id');

        if ($pendingId === null) {
            return redirect()->route('login');
        }

        $pendingUser = Users_tbl::find($pendingId);

        if ($pendingUser === null || ! $pendingUser->twoFactorEnabled()) {
            session()->forget('2fa.pending_user_id');

            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge', compact('pendingUser'));
    }

    public function verifyChallenge(Request $request)
    {
        $pendingId = session()->get('2fa.pending_user_id');

        if ($pendingId === null) {
            return redirect()->route('login');
        }

        $user = Users_tbl::find($pendingId);
        $record = $user?->twoFactorSecret()->first();

        if ($user === null || $record === null || ! $record->enabled || ! $user->twoFactorEnabled()) {
            session()->forget('2fa.pending_user_id');

            return redirect()->route('login');
        }

        $data = $request->validate([
            'code' => 'required|string|max:64',
        ]);

        $code = (string) preg_replace('/\s+/', '', $data['code']);
        $verifiedTotp = $this->isValidTotp($record->secret, $code);
        $verifiedRecovery = $verifiedTotp ? false : $this->consumeRecoveryCode($record, $code);

        if (! $verifiedTotp && ! $verifiedRecovery) {
            AuditLog::log(
                '2FA Challenge Failed',
                "Two-factor challenge rejected an invalid code for user #{$user->id}.",
                'user',
                $user->id
            );

            return redirect()->back()
                ->withErrors(['code' => 'The code you entered is invalid. Please try again.']);
        }

        // Regenerate the session id a second time so the verified privileged
        // session is completely separated from the pre-verification state.
        $request->session()->regenerate();
        $request->session()->put('2fa.verified', true);
        $request->session()->put('2fa.verified_at', now()->timestamp);
        $request->session()->forget('2fa.pending_user_id');
        $request->session()->flash('just_logged_in', true);

        AuditLog::log(
            $verifiedRecovery ? '2FA Recovery Code Used' : '2FA TOTP Challenge Succeeded',
            "Two-factor authentication verified for user #{$user->id}.",
            'user',
            $user->id
        );

        return redirect()->intended(route('UserHandle'));
    }
}