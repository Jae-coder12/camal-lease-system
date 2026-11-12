<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Receipt {{ $payment->reference_number }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important
            }

            .receipt {
                border: 0 !important;
                box-shadow: none !important
            }
        }
    </style>
</head>

<body class="bg-light">
    <main class="container py-5" style="max-width:760px">
        <div class="receipt card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex justify-content-between border-bottom pb-3 mb-4">
                    <div>
                        <h1 class="h4 fw-bold">Municipality of Camalaniugan</h1>
                        <div class="text-muted">Public Market · Official Payment Receipt</div>
                    </div>
                    <div class="text-end">
                        <div class="small text-muted">Receipt reference</div><strong>{{ $payment->reference_number }}</strong>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-sm-6">
                        <div class="small text-muted">Received from</div><strong>{{ $payment->lease->registrant->name }}</strong>
                        <div>{{ $payment->lease->registrant->control_number }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Market stall</div><strong>{{ $payment->lease->stall->building?->name }} · Stall {{ $payment->lease->stall->stall_number }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Amount received</div><strong class="fs-4">₱{{ number_format($payment->amount,2) }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Payment date</div><strong>{{ $payment->payment_date->format('F d, Y') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Period covered</div><strong>{{ $payment->period_covered ?? 'Not specified' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <div class="small text-muted">Payment method</div><strong>{{ $payment->method ? str($payment->method)->replace('_',' ')->title() : 'Not specified' }}</strong>
                    </div>
                </div>
                @if($payment->remarks)<div class="mt-4">
                    <div class="small text-muted">Remarks</div>{{ $payment->remarks }}
                </div>@endif
                <div class="mt-5 d-flex justify-content-between">
                    <div class="text-muted small">Recorded by {{ $payment->recorder->name }}</div>
                    <div class="text-center" style="min-width:220px;border-top:1px solid #8794a0;padding-top:.4rem">Authorized signature</div>
                </div>
                <div class="d-flex gap-2 mt-4 no-print"><button class="btn btn-primary" onclick="window.print()">Print receipt</button><a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">Back to payments</a></div>
            </div>
        </div>
    </main>
</body>

</html>