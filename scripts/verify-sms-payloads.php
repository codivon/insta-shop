<?php

/**
 * v0.18.1 payload assertions — verifies every SMS driver builds the exact
 * request shape of the customer's proven implementations (Http::fake, no
 * real network). Run: php scripts/verify-sms-payloads.php
 */

use App\Models\Setting;
use App\Models\SmsTemplate;
use App\Support\Sms\SmsManager;
use App\Support\Sms\SmsMessage;
use App\Support\Sms\SmsTemplates;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$failures = [];
$CAP = null; // captured request

function capture(string $pattern, $response): void
{
    global $CAP;
    Http::fake([$pattern => function ($request) use ($response, &$CAP) {
        $CAP = $request;

        return $response;
    }]);
}

function req(): Request
{
    global $CAP;

    return $CAP;
}

function check(string $label, bool $cond, string $detail = ''): void
{
    global $failures;
    echo ($cond ? 'PASS' : 'FAIL').'  '.$label.($cond ? '' : "  → $detail").PHP_EOL;
    if (! $cond) {
        $failures[] = $label;
    }
}

// ── Setup: settings + otp_login template with pattern ──────────────────────
Setting::setMany([
    'sms.kavenegar.api_key' => 'KN-KEY-1',
    'sms.ippanel.username' => 'ipanel-user',
    'sms.ippanel.password' => 'ipanel-pass',
    'sms.ippanel.from' => '+983000505',
    'sms.farazsms.api_key' => 'FZ-API-KEY',
    'sms.farazsms.from' => '+981000505',
    'sms.mellipayamak.username' => 'melli-user',
    'sms.mellipayamak.password' => 'melli-pass',
    'sms.mellipayamak.sender' => '30001234',
    'sms.restfulsms.api_key' => 'SMSIR-USER-KEY',
    'sms.restfulsms.secret_key' => 'SMSIR-SECRET',
]);

$tpl = SmsTemplate::query()->where('key', 'otp_login')->firstOrFail();
$tpl->update(['pattern_code' => 'PATTERN-123', 'enabled' => true]);
SmsTemplates::flush('otp_login');

$manager = app(SmsManager::class);

// ── 0. Root-cause regression: make() must keep variables alongside pattern ─
$msg = SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer');
check('SmsTemplates::make keeps pattern', $msg->template === 'PATTERN-123', "got {$msg->template}");
check('SmsTemplates::make keeps data (code)', ($msg->data['code'] ?? '') === '654321', 'data lost: '.json_encode($msg->data, JSON_UNESCAPED_UNICODE));
check('SmsTemplates::make keeps data (minutes)', ((string) ($msg->data['minutes'] ?? '')) === '5', 'data lost');

// ── 1. Kavenegar: verify/lookup with token=code ────────────────────────────
capture('api.kavenegar.com/*', Http::response(['return' => ['status' => 200, 'message' => 'تایید شد'], 'entries' => [['messageid' => 777]]]));
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer'), 'kavenegar');
$req = req();
check('kavenegar endpoint', str_contains((string) $req->url(), '/v1/KN-KEY-1/verify/lookup.json'), (string) $req->url());
check('kavenegar token=code', ($req->data()['token'] ?? '') === '654321', json_encode($req->data(), JSON_UNESCAPED_UNICODE));
check('kavenegar template', ($req->data()['template'] ?? '') === 'PATTERN-123', 'missing');
check('kavenegar ok', $res->ok, (string) $res->error);

// ── 2. IPPanel: legacy patterns endpoint with username/password ────────────
capture('ippanel.com/*', Http::response(['code' => 'ok', 'data' => ['package_id' => 555]]));
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer'), 'ippanel');
$req = req();
$url = (string) $req->url();
check('ippanel endpoint', str_contains($url, 'https://ippanel.com/patterns/pattern?'), $url);
check('ippanel username in query', str_contains($url, 'username=ipanel-user'), $url);
check('ippanel pattern_code in query', str_contains($url, 'pattern_code=PATTERN-123'), $url);
check('ippanel to json in query', str_contains($url, 'to=%5B%2209121112233%22%5D') || str_contains(urldecode($url), 'to=["09121112233"]'), urldecode($url));
check('ippanel input_data has code', str_contains(urldecode($url), '"code":"654321"'), urldecode($url));
check('ippanel from in query', str_contains(urldecode($url), 'from=+983000505'), urldecode($url));
check('ippanel ok', $res->ok, (string) $res->error);
check('ippanel package id', $res->providerMessageId === '555', $res->providerMessageId ?? '—');

// ── 3. FarazSms (iranpayamak): pattern with Api-Key header ─────────────────
capture('api.iranpayamak.com/*', Http::response(['code' => 200, 'data' => ['message_id' => 888]]));
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer'), 'farazsms');
$req = req();
$payload = json_decode((string) $req->body(), true);
check('farazsms endpoint', str_contains((string) $req->url(), 'api.iranpayamak.com/ws/v1/sms/pattern'), (string) $req->url());
check('farazsms Api-Key header', ($req->header('Api-Key')[0] ?? '') === 'FZ-API-KEY', json_encode($req->header('Api-Key')));
check('farazsms code=PATTERN-123', ($payload['code'] ?? '') === 'PATTERN-123', (string) $req->body());
check('farazsms attributes code', ($payload['attributes']['code'] ?? '') === '654321', (string) $req->body());
check('farazsms recipient', ($payload['recipient'] ?? '') === '09121112233', (string) $req->body());
check('farazsms line_number', ($payload['line_number'] ?? '') === '+981000505', (string) $req->body());
check('farazsms number_format=english', ($payload['number_format'] ?? '') === 'english', (string) $req->body());
check('farazsms ok', $res->ok, (string) $res->error);
check('farazsms message id', $res->providerMessageId === '888', $res->providerMessageId ?? '—');

// ── 4. MelliPayamak pattern mode: BaseServiceNumber with «;» segments ──────
capture('rest.payamak-panel.com/*', Http::response(['RetStatus' => 1, 'StrRetStatus' => 'OK', 'Value' => '9001']));
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer'), 'mellipayamak');
$req = req();
check('mellipayamak pattern endpoint', str_contains((string) $req->url(), 'BaseServiceNumber'), (string) $req->url());
check('mellipayamak bodyId', ($req->data()['bodyId'] ?? '') === 'PATTERN-123', json_encode($req->data(), JSON_UNESCAPED_UNICODE));
check('mellipayamak segments', ($req->data()['text'] ?? '') === '654321;5', $req->data()['text'] ?? '—');
check('mellipayamak to', ($req->data()['to'] ?? '') === '09121112233', '—');
check('mellipayamak ok', $res->ok, (string) $res->error);

// ── 5. MelliPayamak free-text fallback (no pattern) ────────────────────────
$free = SmsMessage::make('989121112233')->text('پیام تست')->panel('admin');
$res = $manager->send($free, 'mellipayamak');
$req = req();
check('mellipayamak free endpoint', str_contains((string) $req->url(), 'SendSMS/SendSMS'), (string) $req->url());
check('mellipayamak free from', ($req->data()['from'] ?? '') === '30001234', json_encode($req->data(), JSON_UNESCAPED_UNICODE));
check('mellipayamak free ok', $res->ok, (string) $res->error);

// ── 6. sms.ir (RestfulSms): UltraFastSend/direct with UserApiKey+SecretKey ─
capture('restfulsms.com/*', Http::response(['IsSuccessful' => true, 'Message' => 'عملیات با موفقیت انجام شد', 'Data' => ['PackId' => 4032]]));
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '654321', 'minutes' => 5], 'customer'), 'restfulsms');
$req = req();
$payload = json_decode((string) $req->body(), true);
check('smsir endpoint', str_contains(strtolower((string) $req->url()), 'restfulsms.com/api/ultrafastsend/direct'), (string) $req->url());
check('smsir UserApiKey', ($payload['UserApiKey'] ?? '') === 'SMSIR-USER-KEY', (string) $req->body());
check('smsir SecretKey', ($payload['SecretKey'] ?? '') === 'SMSIR-SECRET', '—');
check('smsir mobile local', ($payload['mobile'] ?? '') === '09121112233', (string) $req->body());
check('smsir TemplateId', ($payload['TemplateId'] ?? '') === 'PATTERN-123', '—');
check('smsir ParameterArray code', ($payload['ParameterArray'][0]['Parameter'] ?? '') === 'code' && ($payload['ParameterArray'][0]['ParameterValue'] ?? '') === '654321', (string) $req->body());
check('smsir ok', $res->ok, (string) $res->error);
check('smsir pack id', $res->providerMessageId === '4032', $res->providerMessageId ?? '—');

// ── 7. Failure UX: missing pattern on a pattern-based driver ───────────────
$tpl->update(['pattern_code' => null]);
SmsTemplates::flush('otp_login');
$res = $manager->send(SmsTemplates::make('otp_login', '989121112233', ['code' => '111111', 'minutes' => 5], 'customer'), 'restfulsms');
check('smsir no-template error is actionable', ! $res->ok && str_contains((string) $res->error, 'قالب‌های پیامک'), (string) $res->error);
$tpl->update(['pattern_code' => 'PATTERN-123']);
SmsTemplates::flush('otp_login');

// ── 8. patternBased flags ──────────────────────────────────────────────────
check('log not patternBased', ! $manager->driver('log')->patternBased());
check('mellipayamak not patternBased', ! $manager->driver('mellipayamak')->patternBased());
foreach (['kavenegar', 'ippanel', 'farazsms', 'restfulsms'] as $d) {
    check("$d patternBased", $manager->driver($d)->patternBased());
}

// ── 9. Labels ──────────────────────────────────────────────────────────────
check('restfulsms label = sms.ir', $manager->driver('restfulsms')->label() === 'sms.ir', $manager->driver('restfulsms')->label());
check('no OTP field on kavenegar', ! array_key_exists('template_otp', $manager->driver('kavenegar')->settingsFields()));
check('no OTP field on ippanel', ! array_key_exists('pattern_otp', $manager->driver('ippanel')->settingsFields()));
check('farazsms registered', $manager->has('farazsms'));

echo PHP_EOL.(count($failures) === 0 ? 'ALL CHECKS PASSED' : count($failures).' FAILURES: '.implode(', ', $failures)).PHP_EOL;
exit(count($failures) === 0 ? 0 : 1);
