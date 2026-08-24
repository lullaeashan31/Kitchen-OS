<!doctype html>
<html>
<head><meta charset="utf-8">
<style>
  @page { margin: 22mm 18mm 20mm 18mm; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 10.5pt; color:#141924; line-height:1.5; }
  .doc-body { }
  .footer-note { position: fixed; bottom: -14mm; left:0; right:0; font-size:6.5pt; color:#464646;
                 border-top:0.4pt solid #969696; padding-top:2mm; }
  h1.cert { font-size: 15pt; margin:0 0 2mm; }
  .lede { font-size:8.5pt; color:#5a5f69; margin:0 0 6mm; }
  table.cert { width:100%; border-collapse:collapse; }
  table.cert td { padding:2.2mm 0; border-bottom:0.4pt solid #e4e8ee; vertical-align:top; }
  td.k { width:38%; font-weight:bold; font-size:8.5pt; color:#5a5f69; }
  td.v { font-size:9pt; }
  .integrity { margin-top:6mm; font-size:8.5pt; color:#5a5f69; font-weight:bold; }
  .integrity p { font-family: DejaVu Sans Mono, monospace; font-size:7.5pt; color:#141924; font-weight:normal; }
  .tail { margin-top:6mm; font-size:7pt; color:#787d87; }
</style>
</head>
<body>

<div class="footer-note">
  Electronically signed by {{ $doc->signer_typed_name }}
  | {{ $doc->signed_at->timezone('Asia/Kolkata')->format('d M Y, H:i') }} IST
  | Ref {{ $reference }}
  | See Signature Certificate (final page)
</div>

<div class="doc-body">{!! \App\Services\HtmlSanitizer::clean($doc->version->body_html) !!}</div>

<div style="page-break-before: always;"></div>

<h1 class="cert">Electronic Signature Certificate</h1>
<p class="lede">This certificate forms part of, and is issued with, the document it is attached to.
It records an electronic signature made under the Information Technology Act, 2000.</p>

@php
  $emp = $doc->employee;
  $ist = fn($d) => $d ? $d->timezone('Asia/Kolkata')->format('d M Y, H:i:s').' IST' : '—';
  $rows = [
    'Document'          => $doc->documentTemplate->name,
    'Document version'  => 'v'.$doc->version->version.'  ('.strtoupper($doc->language).')',
    'Signed by'         => $doc->signer_typed_name,
    'Employee'          => $emp->name.'  ('.$emp->employee_code.')',
    'Designation'       => $emp->designation ?: ($emp->jobRole->name ?? '—'),
    'Date & time'       => $ist($doc->signed_at),
    'Place of signing'  => $doc->signing_place ?: ($doc->signingOutlet->name ?? $emp->outlet->name ?? '—'),
    'Method'            => 'Typed-name electronic signature, in person',
    'Witnessed by'      => $doc->recordedBy->name ?? '—',
    'IP address'        => $doc->signed_ip ?: '—',
    'Device'            => \Illuminate\Support\Str::limit($doc->signed_user_agent ?: '—', 78),
    'Reference'         => $reference,
  ] + $fields;
@endphp

<table class="cert">
  @foreach ($rows as $k => $v)
    <tr><td class="k">{{ $k }}</td><td class="v">{{ $v }}</td></tr>
  @endforeach
</table>

<div class="integrity">
  Integrity check (SHA-256)
  <p>Recorded in the Traverse HR audit log at the time of signing and shown on the document
     verification screen. Any change to this file will not match that value.</p>
</div>

<p class="tail">Traverse Inc. | Confidential | Retained in the Traverse HR system.
The signatory retains permanent access to this document through their personal document link.</p>

</body>
</html>
