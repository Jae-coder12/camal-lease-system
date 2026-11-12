<!doctype html>
<html><head><meta charset="utf-8"><title>Lease Contract {{ $lease->contract_number }}</title>
<style>
  body{font-family:'DejaVu Sans',Arial,sans-serif;font-size:11px;line-height:1.5;color:#111;margin:38px 52px}
  h2{text-align:center;margin:0;font-size:17px}.sub{text-align:center;color:#555;margin-bottom:22px}
  h3{font-size:12px;text-align:center;margin:20px 0 10px}
  p{margin:0 0 10px}.contract-meta{text-align:right;margin-bottom:18px}
  .terms li{margin-bottom:7px;padding-left:3px}
  .sig{width:100%;margin-top:45px}.sig td{width:50%;text-align:center;vertical-align:top;padding:0 12px}
  .line{border-top:1px solid #000;margin:0 15px;padding-top:5px}
  .notary{margin-top:42px}.notary-grid{width:100%;margin-top:16px}
  .notary-grid td{width:50%;padding:3px 0}
  .bar{margin-bottom:20px}.bar a,.bar button{padding:6px 14px;margin-right:6px;font-size:13px}
  @media print{.no-print{display:none}}
</style></head><body>
@php
  $tenant = $lease->tenant_details ?? [];
  $tenantName = data_get($tenant, 'name', $lease->registrant->name);
  $tenantControlNumber = data_get($tenant, 'control_number', $lease->registrant->control_number);
  $terms = preg_split('/\r\n|\r|\n/', $lease->contract_terms ?: \App\Models\Lease::defaultContractTerms());
@endphp
@if(!$pdf)
<div class="no-print bar"><button onclick="window.print()">🖨 Print</button><a href="{{ route('leases.download',$lease) }}">⬇ Download PDF</a></div>
@endif

<div class="contract-meta">Control No.: {{ $tenantControlNumber }}</div>
<h2>CONTRACT OF LEASE OF MARKET STALL</h2>
<div class="sub">Stall Rental Agreement &bull; Contract No. {{ $lease->contract_number }}</div>

<p><strong>KNOW ALL MEN BY THESE PRESENTS:</strong></p>
<p>The MUNICIPAL GOVERNMENT OF CAMALANIUGAN, CAGAYAN, represented by its duly authorized representative, hereinafter referred to as the <strong>LESSOR</strong>;</p>
<p style="text-align:center">and</p>
<p><strong>{{ strtoupper($tenantName) }}</strong>, {{ data_get($tenant, 'civil_status', $lease->registrant->status) }}, {{ data_get($tenant, 'citizenship', $lease->registrant->citizenship) }} citizen, of legal age, and with residence at {{ data_get($tenant, 'address', $lease->registrant->address) }}, hereinafter referred to as the <strong>LESSEE</strong>.</p>
<p><strong>LESSEE DETAILS</strong><br>
Control No.: {{ $tenantControlNumber }}<br>
Birth date: {{ data_get($tenant, 'birthdate', $lease->registrant->birthdate?->format('Y-m-d')) }}<br>
Contact no.: {{ data_get($tenant, 'contact_number', $lease->registrant->contact_number) }} &bull; Email: {{ data_get($tenant, 'email', $lease->registrant->user?->email) }}</p>

<h3>HAVE AGREED AS FOLLOWS:</h3>
<ol class="terms">
  <li><strong>Leased premises:</strong> The LESSOR leases to the LESSEE the market stall at {{ $lease->stall->building?->name }} — Stall {{ $lease->stall->stall_number }}, {{ $lease->stall->location }}, measuring {{ $lease->stall->size_sqm }} sq.m.</li>
  <li><strong>Rental:</strong> ₱{{ number_format((float)($lease->rental_amount ?? $lease->monthly_rate),2) }} per {{ $lease->payment_frequency==='daily'?'day':'month' }}, payable to the municipal market office at the stated frequency. Payment is recognized upon issuance of an official municipal receipt.</li>
  <li><strong>Contract duration and renewal:</strong> This lease runs from {{ $lease->start_date->format('F d, Y') }} through {{ $lease->end_date?->format('F d, Y') ?? 'until ended under this contract' }}. Upon expiration, the LESSEE has the first option to renew, provided the LESSEE has complied with this contract and remains qualified under the Market Code and applicable municipal ordinances. Renewal is subject to those requirements and is not automatic. Any successor preference is governed by the same authorities.</li>
  @foreach($terms as $term)<li>{{ $term }}</li>@endforeach
  <li><strong>Termination:</strong> The parties may end this lease by mutual agreement. The LESSEE may discontinue the business and vacate the stall after prior notice to the LESSOR. The LESSOR may terminate this lease if the LESSEE violates its terms, the Market Code, or applicable municipal ordinances, rules, or regulations, subject to applicable law.</li>
  <li><strong>Effectivity:</strong> This contract takes effect upon ratification before a Notary Public.</li>
</ol>

@if($lease->renewed_from_lease_id)<p>Renewal of contract {{ $lease->renewedFrom?->contract_number }}.</p>@endif
<p>IN WITNESS WHEREOF, the parties have signed this contract on the date and at the place stated below.</p>

<table class="sig"><tr>
  <td><div class="line">Authorized Municipal Representative<br>Municipality of Camalaniugan<br>LESSOR &bull; Signature / Date</div></td>
  <td><div class="line">{{ $tenantName }}<br>LESSEE &bull; Signature / Date</div></td>
</tr></table>

<div class="notary">
  <p>Signed in the Municipality of Camalaniugan, Province of Cagayan, Philippines, this ____ day of ______________, 20____.</p>
  <p><strong>ACKNOWLEDGMENT</strong></p>
  <p>REPUBLIC OF THE PHILIPPINES &nbsp; )<br>PROVINCE OF CAGAYAN &nbsp;&nbsp;&nbsp; ) S.S.<br>MUNICIPALITY OF CAMALANIUGAN )</p>
  <p>BEFORE ME, a Notary Public, personally appeared the LESSOR’s authorized representative and the LESSEE, who presented competent proof of identity, and acknowledged that they executed this Contract of Lease of Market Stall as their free and voluntary act.</p>
  <table class="notary-grid">
    <tr><td>Doc. No. __________</td><td>Notary Public</td></tr>
    <tr><td>Page No. __________</td><td>____________________________</td></tr>
    <tr><td>Book No. __________</td><td></td></tr>
    <tr><td>Series of __________</td><td></td></tr>
  </table>
</div>
</body></html>
