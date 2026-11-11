@php $r = $registrant ?? null; @endphp
<div class="row g-3">
  <div class="col-md-4"><label class="form-label">Control No.</label>
    <input class="form-control bg-light" value="{{ $control }}" readonly>
    <div class="form-text">Auto-generated</div></div>
  <div class="col-md-8"><label class="form-label">Full Name</label>
    <input name="name" class="form-control" value="{{ old('name', $r->name ?? '') }}" required></div>
  <div class="col-md-4"><label class="form-label">Status</label>
    <select name="status" class="form-select" required>
      <option value="">Select…</option>
      @foreach(['Single','Married','Widowed','Separated','Divorced'] as $s)
        <option @selected(old('status', $r->status ?? '') === $s)>{{ $s }}</option>
      @endforeach
    </select></div>
  <div class="col-md-4"><label class="form-label">Citizenship</label>
    <input name="citizenship" class="form-control" value="{{ old('citizenship', $r->citizenship ?? 'Filipino') }}" required></div>
  <div class="col-md-2"><label class="form-label">Birthdate</label>
    <input type="date" id="birthdate" name="birthdate" class="form-control" max="{{ now()->toDateString() }}"
      value="{{ old('birthdate', optional($r->birthdate ?? null)->format('Y-m-d')) }}" required></div>
  <div class="col-md-2"><label class="form-label">Age</label>
    <input id="age" class="form-control bg-light" value="{{ $r?->age }}" readonly></div>
  <div class="col-md-8"><label class="form-label">Address</label>
    <input name="address" class="form-control" value="{{ old('address', $r->address ?? '') }}" required></div>
  <div class="col-md-4"><label class="form-label">Contact Number</label>
    <input name="contact_number" class="form-control" placeholder="09XX XXX XXXX" value="{{ old('contact_number', $r->contact_number ?? '') }}" required></div>
</div>
<script>
(function(){
  const b=document.getElementById('birthdate'), a=document.getElementById('age');
  function calc(){ const d=new Date(b.value); if(isNaN(d)){a.value='';return;}
    const t=new Date(); let n=t.getFullYear()-d.getFullYear(); const m=t.getMonth()-d.getMonth();
    if(m<0||(m===0&&t.getDate()<d.getDate())) n--; a.value=n; }
  b.addEventListener('input',calc); calc();
})();
</script>
