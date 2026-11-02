<div dir="rtl" style="font-family: Tahoma, Vazirmatn, sans-serif; direction: rtl; text-align: right; padding: 24px; color: #1e293b;">
    <h2 style="margin: 0 0 12px; font-size: 16px;">فایل پشتیبان اینستاشاپ</h2>

    <p style="margin: 0 0 8px; font-size: 13px; line-height: 2;">
        فایل پشتیبان فروشگاه شما پیوست این ایمیل است.
    </p>

    <ul style="margin: 0 0 16px; padding-right: 18px; font-size: 12.5px; line-height: 2; color: #475569;">
        <li>نام فایل: <strong>{{ $fileName }}</strong></li>
        <li>تاریخ پشتیبان‌گیری: <strong>{{ \App\Support\JDate::dateTime($createdAt) }}</strong></li>
        <li>حجم کل پشتیبان: <strong>{{ \App\Support\Fmt::bytes($backup->size) }}</strong></li>
        @if ($total > 1)
            <li>این پیام: <strong>بخش {{ \App\Support\Fmt::fa($part) }} از {{ \App\Support\Fmt::fa($total) }}</strong></li>
        @endif
    </ul>

    @if ($total > 1)
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; font-size: 12.5px; line-height: 2; color: #475569;">
            <strong style="color: #0f172a;">بازگرداندن فایل اصلی از بخش‌ها:</strong><br>
            همهٔ بخش‌ها را در یک پوشه کنار هم قرار دهید؛ سپس:<br>
            — در ویندوز (CMD):
            <code dir="ltr" style="display: inline-block; background: #eef2f7; padding: 2px 8px; border-radius: 6px;">copy /b {{ pathinfo($fileName, PATHINFO_FILENAME) }}.zip.001+{{ pathinfo($fileName, PATHINFO_FILENAME) }}.zip.002+... {{ pathinfo($fileName, PATHINFO_FILENAME) }}.zip</code><br>
            — در لینوکس / مک:
            <code dir="ltr" style="display: inline-block; background: #eef2f7; padding: 2px 8px; border-radius: 6px;">cat {{ pathinfo($fileName, PATHINFO_FILENAME) }}.zip.* &gt; {{ pathinfo($fileName, PATHINFO_FILENAME) }}.zip</code>
        </div>
    @endif

    <p style="margin: 16px 0 0; font-size: 11.5px; color: #94a3b8;">
        این ایمیل به‌صورت خودکار توسط پنل مدیریت اینستاشاپ ارسال شده است.
    </p>
</div>
