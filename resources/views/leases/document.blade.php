<!doctype html>
<html><head><meta charset="utf-8"><title>Lease Contract {{ $lease->contract_number }}</title>
<style>
  body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:12px;line-height:1.6;color:#111;margin:40px 55px}
  h2{text-align:center;margin:0}.sub{text-align:center;color:#555;margin-bottom:25px}
  .terms li{margin-bottom:6px}
  .sig{width:100%;margin-top:60px}.sig td{width:50%;text-align:center;vertical-align:top}
  .line{border-top:1px solid #000;margin:0 30px;padding-top:4px}
  .bar{margin-bottom:20px}.bar a,.bar button{padding:6px 14px;margin-right:6px;font-size:13px}
  @media print{.no-print{display:none}}
</style></head><body>
@if(!$pdf)
<div class="no-print bar"><button onclick="window.print()">🖨 Print</button><a href="{{ route('leases.download',$lease) }}">⬇ Download PDF</a></div>
@endif

<h2>LEASE CONTRACT</h2>
<div class="sub">Stall Rental Agreement &bull; Contract No. {{ $lease->contract_number }}</div>

<p>{{ now()->format('F d, Y') }}</p>
<p><strong>{{ strtoupper($lease->registrant->name) }}</strong><br>
{{ $lease->registrant->address }}<br>Control No.: {{ $lease->registrant->control_number }}</p>

<p><strong>Re: Lease of Stall {{ $lease->stall->stall_number }} — {{ $lease->stall->location }}</strong></p>
<p>Dear {{ $lease->registrant->name }},</p>
<p>We are pleased to confirm the lease of the above-described stall ({{ $lease->stall->size_sqm }} sq.m.) to you
({{ $lease->registrant->status }}, {{ $lease->registrant->citizenship }}, {{ $lease->registrant->age }} years old, contact no. {{ $lease->registrant->contact_number }}),
subject to the following terms and conditions:</p>

<ol class="terms">
  <li><strong>Term.</strong> The lease starts on {{ $lease->start_date->format('F d, Y') }} and runs until
      {{ $lease->end_date ? $lease->end_date->format('F d, Y') : 'terminated by either party' }}, unless sooner terminated.</li>
  <li><strong>Rent.</strong> The monthly rental is <strong>₱{{ number_format($lease->monthly_rate,2) }}</strong>, payable on or before the fifth (5th) day of each month.</li>
  <li><strong>Use.</strong> The stall shall be used solely for lawful business purposes and shall be kept clean and in good condition.</li>
  <li><strong>No sub-lease.</strong> The Lessee shall not sub-lease or transfer the stall without the written consent of the Lessor.</li>
  <li><strong>Termination.</strong> Failure to pay rent or violation of any term of this contract is a ground for termination of the lease.</li>
</ol>
<p>Please sign below to signify your conformity to this agreement.</p>

<p>Very truly yours,</p>
<table class="sig"><tr>
  <td><div class="line">Lessor / Authorized Representative</div></td>
  <td><div class="line">{{ $lease->registrant->name }}<br>Lessee (Conforme)</div></td>
</tr></table>
</body></html>
