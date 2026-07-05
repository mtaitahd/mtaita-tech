<?php

function buildEmailHtml($title, $bodyHtml, $extraFooter = '') {
    $logoUrl = 'https://mtaitatech.online/assets/img/jj.png';
    $siteUrl = 'https://mtaitatech.online';
    $siteName = 'Mtaita Tech';

    return <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1.0"></head>
<body style="margin:0;padding:0;background-color:#f4f4f5;font-family:'Segoe UI',Arial,sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f5;padding:20px 0;">
<tr><td align="center">
<table width="560" cellpadding="0" cellspacing="0" style="max-width:560px;width:100%;background-color:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,0.06);">

<!-- HEADER with logo -->
<tr><td style="padding:32px 32px 0;text-align:center;background:#ffffff;">
    <a href="$siteUrl" target="_blank">
        <img src="$logoUrl" alt="$siteName" width="64" height="64" style="display:block;margin:0 auto;border:0;border-radius:12px;">
    </a>
    <h1 style="margin:12px 0 0;font-size:20px;font-weight:700;color:#12344D;">$siteName</h1>
</td></tr>

<!-- BODY -->
<tr><td style="padding:24px 32px 16px;">
    <h2 style="margin:0 0 8px;font-size:18px;font-weight:600;color:#1e293b;">$title</h2>
    $bodyHtml
</td></tr>

<!-- FOOTER -->
<tr><td style="padding:16px 32px 32px;border-top:1px solid #e2e8f0;">
    <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.6;">
        $extraFooter
    </p>
    <p style="margin:8px 0 0;font-size:12px;color:#94a3b8;">
        &copy; 2026 <a href="$siteUrl" style="color:#DC2626;text-decoration:none;">$siteName</a>. All rights reserved.
    </p>
</td></tr>

</table>
</td></tr></table>
</body>
</html>
HTML;
}
